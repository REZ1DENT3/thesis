<?php

namespace old;

use PHPSQLParser\builders\SubQueryBuilder;
use PHPSQLParser\utils\ExpressionType;

class Query
{

    /**
     * @var QueryParser
     */
    private $parser;

    /**
     * @var array SemanticParser
     */
    private $semanticParser;

    /**
     * Query constructor.
     * @param $sql
     */
    public function __construct($sql)
    {
        $this->parser = new QueryParser($sql);
    }

    private function getValue($stdObj)
    {
        $res = array();

        if ($stdObj->expr_type == ExpressionType::EXPRESSION) {

        }

        if ($stdObj->expr_type == ExpressionType::CONSTANT) {
            return array($stdObj->base_expr);
        }

        if ($stdObj->expr_type == ExpressionType::SUBQUERY) {
            $value = (new self(trim(trim($stdObj->base_expr, '()'))))
                ->execute();

            if (is_array($value)) {
                return $value;
            }

            return array($value);
        }

        if ($stdObj->sub_tree) {
            foreach ($stdObj->sub_tree as $sub) {
                $res[] = $this->getValue($sub)[0];
            }
        }
        else if (isset($stdObj->no_quotes) &&
            isset($stdObj->no_quotes->parts) &&
            isset($stdObj->no_quotes->parts->{0})
        ) {
            $res[] = $stdObj->no_quotes->parts->{0};
        }
        else {
            $res[] = $stdObj->base_expr;
        }

        foreach ($res as &$r) {
            $r = preg_replace('~@(attributes.){0,1}(\w+)$~', '@attributes.$2', $r);
        }

        return $res;
    }

    public function getWithParts($parts, $alias, $value, $l = false)
    {

        if ($l && is_numeric(key($value))) {
            foreach ($value as &$v) {
                $v = $this->getWithParts($parts, $alias, $v, $l);
            }
            return $value;
        }

        $checkSemantic = !in_array('#value', $parts);
        for ($ind = 1; $ind < count($parts); $ind++) {

            if (!$ind) continue;

            $value = (array)$value;
            if (!isset($value[$parts[$ind]])) {
                if ($l) {
                    return $value;
                }
                return false;
            }

            $value = $value[$parts[$ind]];
            if ($checkSemantic && (count($parts) - 1) == $ind) {
                if ($this->semanticParser[$alias]->getSemantic()->isSemantic($parts[$ind])) {
                    $parts[] = '#value';
                    $parts[] = 'value';
                    $checkSemantic = false;
                }
            }

        }

        return $value;

    }

    public function usort(&$semanticArray, $parts, $alias, $self, $o)
    {
        $cmp = function ($a, $b) use ($parts, $alias, $self, $o) {
            $a = $self->getWithParts($parts, $alias, $a);
            $b = $self->getWithParts($parts, $alias, $b);
            $mul = 1;
            if ($o->direction == 'DESC') {
                $mul = -1;
            }
            if (!(is_numeric($a) && is_numeric($b)) && (!is_string($a) || !is_string($b))) {
                $a = spl_object_hash((object)$a);
                $b = spl_object_hash((object)$b);
            }
            return $mul * strcmp($a, $b);
        };

        uasort($semanticArray, $cmp);
    }

    public function execute()
    {

        $params = array('select', 'from');

        /**
         * @var $from \stdClass
         * @var $where \stdClass
         * @var $select \stdClass
         */
        foreach ($params as $param) {
            $$param = $this->parser->{$param}();
            if (!$$param) {
                throw new \Exception($param);
            }
        }

        $semanticArray = array();
        $self = &$this;

        foreach ($from as $table) {

            $filePath = $table->table;

            if (isset($table->no_quotes) && isset($table->no_quotes->parts) && isset($table->no_quotes->parts->{'0'})) {
                $filePath = $table->no_quotes->parts->{'0'};
            }

            $alias = $filePath;
            $pathInfo = pathinfo($filePath);

            if (isset($pathInfo['filename'])) {
                $alias = $pathInfo['filename'];
            }

            if ($table->alias && $table->alias->name) {
                $alias = $table->alias->name;
            }

            if ($filePath == 'dual') {
                $parser = new Parser("<dual></dual>");
            }
            else {
                $parser = new Parser(file_get_contents($filePath));
            }

            $this->semanticParser[$alias] = new SemanticParser($parser);
            $this->semanticParser[$alias]->execute();

            $opr = 'OR';

            $where = $this->parser->where();
            for ($i = 0; $i < count((array)$where);) {

                $parts = explode('.', $this->getValue($where->{$i++})[0]);

                $operator = $this->getValue($where->{$i++});
                $const = $this->getValue($where->{$i++});

                switch ($operator[0]) {

                    case 'BETWEEN':
                        $operator[0] = '=';
                        $const = array($const[0], $this->getValue($where->{++$i})[0]);
                        if (date($const[0])) {
                            try {
                                $begin = new \DateTime($const[0]);
                                $end = new \DateTime($const[1]);
                                $interval = new \DateInterval('P1D');
                                $dateRange = new \DatePeriod($begin, $interval, $end);
                                foreach ($dateRange as $key => $date) {
                                    $const[$key] = $date->format('d-m-Y');
                                }
                            }
                            catch (\Exception $e) {
                                $const = range($const[0], $const[1]);
                            }
                        }
                        else {
                            $const = range($const[0], $const[1]);
                        }
                        $i++;
                        break;

                    case '<=':
                        $operator = array('<', '=');
                        break;

                    case '>=':
                        $operator = array('>', '=');
                        break;

                    case 'IS':
                        if ($const[0] == 'NOT') {
                            $operator[0] = 'IS NOT';
                            $const = $this->getValue($where->{$i++});
                        }
                        break;

                }

                $newData = array_filter(
                    (array)$this->semanticParser[$alias]->getSemanticData()->{current($parts)},
                    function ($value) use ($parts, $operator, $const, $alias, $self) {

                        $value = $self->getWithParts($parts, $alias, $value);

                        $ind = 0;
                        foreach ($operator as $opr) {

                            if (is_array($value) || is_object($value)) {
                                break;
                            }

                            if ($opr == '<' && $value < min($const)) {
                                $ind++;
                            }

                            if ($opr == '>' && $value > max($const)) {
                                $ind++;
                            }

                            if (in_array($opr, array('=', 'IS')) && in_array($value, $const)) {
                                $ind++;
                            }

                            if (in_array($opr, array('!=', 'IS NOT')) && !in_array($value, $const)) {
                                $ind++;
                            }

                            if ($opr == 'IN' && in_array($value, $const)) {
                                $ind++;
                            }

                        }

                        return $ind;

                    }

                );

                if ($opr == 'AND') {
                    $semanticArray = array_uintersect($semanticArray, $newData, function ($a, $b) {
                        return strcmp(spl_object_hash($a), spl_object_hash($b));
                    });
                }
                else if ($opr == 'OR') {
                    $semanticArray = array_merge($semanticArray, $newData);
                }

                if (isset($where->{$i})) {
                    $opr = $this->getValue($where->{$i++})[0];
                }

                $semanticArray = array_unique($semanticArray, SORT_REGULAR);

            }

            if (count((array)$where) == 0) {
                $semanticArray = $this->semanticParser[$alias]->getSemanticData();
            }

            foreach ($this->parser->orderBy() as $o) {

                $parts = explode('.', $this->getValue($o)[0]);
                $semanticArray = (array)$semanticArray;
                if (is_numeric(key($semanticArray))) {
                    $this->usort($semanticArray, $parts, $alias, $self, $o);
                }
                else {
                    foreach ($semanticArray as &$sem) {
                        $this->usort($sem, $parts, $alias, $self, $o);
                    }
                }

            }

            foreach ($select as $s) {

                $parts = $this->getValue($s)[0];
                if (!is_numeric($parts)) {
                    $parts = explode('.', $parts);
                    $semanticArray = array_values(array_map(function ($a) use ($parts, $alias, $self) {
                        return $self->getWithParts($parts, $alias, $a, true);
                    }, (array)$semanticArray));
                }
                else {
                    $semanticArray = $parts;
                }

                $sArray = $semanticArray;
                if (is_array($semanticArray[0]) && count($semanticArray) == 1) {
                    $sArray = $semanticArray[0];
                }

                $functions = array();
                $m = $s;
                while (preg_match('~function~', $m->expr_type)) {
                    $functions[] = strtolower($m->base_expr);
                    $m = $m->sub_tree->{'0'};
                }

                if (is_array($sArray)) {
                    foreach ($sArray as $skArr => $svArr) {
                        if (is_array($sArray[$skArr]) && empty($sArray[$skArr])) {
                            unset($sArray[$skArr]);
                        }
                    }
                }

                for ($i = count($functions) - 1; $i >= 0; --$i) {
                    if (function_exists($functions[$i])) {
                        try {
                            $sArrayS = $functions[$i]($sArray);
                            if (!is_numeric($sArrayS) && !$sArrayS) {
                                if (is_array($sArray) || is_object($sArray)) {
                                    foreach ($sArray as &$sA) {
                                        $sA = $functions[$i]($sA);
                                    }
                                }
                            }
                            else {
                                $sArray = $sArrayS;
                            }
                        }
                        catch (\Exception $e) {
                            $sArray = $functions[$i]($sArray);
                        }
                    }
                    else if ($functions[$i] == 'avg') {
                        $sArray = array_sum($sArray) / count($sArray);
                    }
                    else if ($functions[$i] == 'sum') {
                        $sArray = array_sum($sArray);
                    }
                    else if ($functions[$i] == 'first') {
                        $sArray = current($sArray);
                    }
                    else if ($functions[$i] == 'last') {
                        $sArray = end($sArray);
                    }
                    else if ($functions[$i] == 'length') {
                        if (is_array($sArray) || is_object($sArray)) {
                            foreach ($sArray as &$sA) {
                                $sA = mb_strlen($sA);
                            }
                        }
                        else {
                            $sArray = mb_strlen($sArray);
                        }
                    }
                    else if ($functions[$i] == 'date') {
                        if (is_array($sArray) || is_object($sArray)) {
                            foreach ($sArray as &$sA) {
                                $sA = date('d-m-Y', strtotime($sA));
                            }
                        }
                        else {
                            $sArray = date('d-m-Y', strtotime($sArray));
                        }
                    }
                    else if ($functions[$i] == 'datetime') {
                        if (is_array($sArray) || is_object($sArray)) {
                            foreach ($sArray as &$sA) {
                                $sA = date('d-m-Y H:i:s', strtotime($sA));
                            }
                        }
                        else {
                            $sArray = date('d-m-Y H:i:s', strtotime($sArray));
                        }
                    }
                    else {
                        throw new \Exception($functions[$i]);
                    }
                }

                $semanticArray = $sArray;

//                UCASE() - Converts a field to upper case
//                LCASE() - Converts a field to lower case
//                MID() - Extract characters from a text field
//                LEN() - Returns the length of a text field
//                ROUND() - Rounds a numeric field to the number of decimals specified
//                NOW() - Returns the current system date and time
//                FORMAT() - Formats how a field is to be displayed

            }

            break; // first file

        }

        return $semanticArray;

    }

}