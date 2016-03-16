<?php

namespace Deimos;

use jlawrence\eos\Parser;
use jlawrence\eos\Stack;
use PHPSQLParser\utils\ExpressionType;

class Query
{

    /**
     * @var QueryParser
     */
    protected $parser;

    /**
     * @var XMLBuilder
     */
    protected $xmlBuilder;

    /**
     * @var array
     */
    protected $storage = array();

    /**
     * @var array
     */
    protected $execute = null;

    /**
     * Query constructor.
     * @param $sql
     */
    public function __construct($sql)
    {
        $this->parser = new QueryParser($sql, true);
        $this->xmlBuilder = new XMLBuilder();
    }

    public function execute()
    {

        if ($this->execute !== null)
            return $this->execute;

        $this->where();
        $data = $this->storage['WHERE'];

        $this->execute = $data;
        return $this->execute();

    }

    /**
     * todo
     */

    private function noQuotes($array, $default = 'base_expr')
    {
        $str = $array[$default];
        if (isset($array['no_quotes'])) {
            if ($array['no_quotes']['delim']) {
                $str = implode('.', $array['no_quotes']['parts']);
            }
            else {
                $str = $array['no_quotes']['parts'][0];
            }
        }
        return $str;
    }

    private function isOperator($array)
    {

        if ($array['expr_type'] === ExpressionType::OPERATOR) {
            return true;
        }

        if ($array['expr_type'] === ExpressionType::COLREF && !isset($array['no_quotes'])) {
            switch ($array['base_expr']) { // SQL Arithmetic Operators
                case '+':
                case '-':
                case '^':
                case '/':
                case '*':
                case '%':
                    return true;
            }
        }

        return false;

    }

    private function getInArray($column, $data)
    {

        $columnObject = new ArrayObject($column);
        $res = $columnObject->get($this->noQuotes($data));

        foreach ($res as &$r) {
            if (isset($r['#value'])) {
                $r = array($r['#value']['value']);
            }
            else if (isset($r['nodevalue'])) {
                $r = array($r['nodevalue']);
            }
        }

        if (count($res) == 1) {
            reset($res);
            return current($res);
        }

        return $res;

    }

    /**
     * @param array $options
     * @param array $column
     * @param Stack|null $stack
     * @param bool $first
     * @return array|mixed
     * @throws \Exception
     */
    private function parsingOfData($options, $column, Stack &$stack = null, $first = true)
    {

        if (!$stack) {
            $stack = new Stack();
        }

        foreach ($options as $option) {

            if (!isset($option['expr_type'])) {
                return array($option['expr_type']);
            }

            if ($option['expr_type'] !== ExpressionType::BRACKET_EXPRESSION) {
                $stack->push($option);
            }
            else {
                $result = null;
                try {
                    $result = Parser::solve($option['base_expr']);
                }
                catch (\Exception $e) {

                    $colref = array();

                    $tree = new ArrayObject(array('s' => $option));
                    $subTree = array();

                    while ($subs = $tree->get('s.sub_tree')) {

                        if (!is_array($subs)) {
                            continue;
                        }

                        $temp = array();
                        foreach ($subs as $key => $sub) {
                            if ($sub == false) {
                                unset($subs[$key]);
                                continue;
                            }
                            if (is_array($sub)) {
                                $temp = array_merge($temp, $sub);
                                $subTree = array_merge($subTree, $sub);
                            }
                        }

                        $tree = new ArrayObject(array('s' => $temp));

                    }

                    foreach ($subTree as $sub) {

                        if ($sub['expr_type'] == ExpressionType::COLREF && !$this->isOperator($sub)) {
                            $key = $this->noQuotes($sub);
                            $key = str_replace('.', 'AT', $key);
                            $key = str_replace('@', 'DOG', $key);
                            $colref[$key] = $this->getInArray($column, $sub);
                        }

                    }

                    $key = str_replace('.', 'AT', $option['base_expr']);
                    $key = str_replace('@', 'DOG', $key);
                    $key = str_replace('`', '', $key);

                    $result = Parser::solve($key, $colref);

                }

                return array($result);

            }

            if ($option['sub_tree']) {

                if ($option['expr_type'] == ExpressionType::EXPRESSION) {
                    continue;
                }

                $this->parsingOfData($option['sub_tree'], $column, $stack, false);

            }

        }

        if ($first) {

            $parameters = array();
            $isOperator = false;
            $clear = false;

            while ($stack->peek()) {

                $data = $stack->pop();

                switch ($data['expr_type']) {

                    case ExpressionType::CONSTANT:
                        $parameters[] = $this->noQuotes($data);
                        break;

                    case ExpressionType::COLREF:
                        if (!$this->isOperator($data)) {
                            $parameters[] = $this->getInArray($column, $data);
                        }
                        else {
                            $parameters[] = $this->noQuotes($data);
                        }
                        break;

                    case ExpressionType::EXPRESSION:
                        try {
                            $result = Parser::solve($data['base_expr']);
                            $parameters[] = $result;
                        }
                        catch (\Exception $e) {
//                            $parameters[] = $this->parsingOfData($data, $semantic); // todo
                        }
                        break;

                    case ExpressionType::OPERATOR:
                        $parameters[] = $this->noQuotes($data);
                        break;

                    case ExpressionType::AGGREGATE_FUNCTION:
                    case ExpressionType::SIMPLE_FUNCTION:

                        $func = $data['base_expr'];

                        if (function_exists($func)) {
                            $parameters = array(call_user_func_array($func, $parameters));
                        }
                        else {
                            throw new \Exception($func);
                        }

                        // execute
//                        $clear = true;
                        break;

                }

                if ($isOperator) {

                    $toParser = true;
                    foreach ($parameters as $parameter) {
                        if (is_array($parameter) || is_object($parameter)) {
                            $toParser = false;
                            break;
                        }
                    }

                    if ($toParser) {
                        $parameters = array_reverse($parameters);
                        $parameters = array(Parser::solve(implode($parameters)));
                    }
                    else {
                        //
                    }

                }

                if ($clear) {
                    $parameters = array();
                    $clear = false;
                }

                $isOperator = $this->isOperator($data);

            }

            return $parameters;

        }

        return array();

    }

    private function where()
    {

        if (isset($this->storage['WHERE'])) {
            return $this->storage['WHERE'];
        }

        $from = current($this->from()); // fixme
        $semantic = new SemanticParser($from['data']);

        $where = $this->parser->where();

        if (count($where)) {

            $this->storage['WHERE'] = array();

            $maxCount = count($where) - 1;

            $indexes = array(); // result

            foreach ($semantic->getStorage() as $key => $columns) {

                foreach ($columns as $columnIndex => $column) {

                    if (!isset($indexes[$columnIndex])) {
                        $indexes[$columnIndex] = array();
                    }

                    // elements -> element
                    for ($ind = 0; $ind <= $maxCount; ++$ind) {

                        $opts = array();
                        $operators = array();

                        $options = $where[$ind];
                        $operator = $where[++$ind];

                        $isOperator = $this->isOperator($operator);

                        if ($isOperator) {

                            $operators = array($operator['base_expr']);
                            $opts[] = $where[++$ind];

                            switch ($operators[0]) {

                                case 'BETWEEN':
                                    ++$ind;
                                    $opts[] = $where[++$ind];
                                    break;

                                case 'IN':
                                    $operators = array('=');
                                    break;

                                case '<=':
                                    $operators = array('<', '=');
                                    break;

                                case '>=':
                                    $operators = array('>', '=');
                                    break;

                                case 'IS':
                                    $operators[0] = '=';
                                    if (end($opts) == 'NOT') {
                                        $operators = array('!=');
                                        $opts[key($opts)] = $where[++$ind];
                                    }
                                    break;

                            }

                        }

                        $options = $this->parsingOfData(array($options), array(
                            $key => $column
                        ));

                        if (count($options) == 1) {
                            $options = current($options);
                        }

                        if ($isOperator) {

                            foreach ($opts as &$opt) {

                                $opt = $this->parsingOfData(array($opt), array(
                                    $key => $column
                                ));


                                if (count($opt) == 1) {
                                    $opt = current($opt);
                                }

                            }

                            foreach ($operators as $operator) {

                                if (!is_array($options)) {
                                    $options = array($options);
                                }

                                if (count($options)) {

                                    foreach ($options as $option) {

                                        $bool = null;

                                        switch ($operator) {

                                            case 'BETWEEN': // (>= and <=)
                                                $bool = $option >= min($opts) && $option <= max($opts);
                                                break;

                                            case '=':
                                                $bool = in_array($option, $opts);
                                                break;

                                            case '!=':
                                                $bool = !in_array($option, $opts);
                                                break;

                                            case '<':
                                                $bool = $option < min($opts);
                                                break;

                                            case '>':
                                                $bool = $option > max($opts);
                                                break;

                                        }

                                        $indexes[$columnIndex][] = (int)$bool;

                                    }

                                }
                                else {
                                    $indexes[$columnIndex][] = (int)false;
                                }



                            }

                        }
                        else {
                            $indexes[$columnIndex][] = (bool)$options;
                        }

                        if ($oper = $where[++$ind]) {
                            $oper = mb_strtoupper($this->noQuotes($oper));
                            if (in_array($oper, array('AND', 'OR'))) {
                                $indexes[$columnIndex][] = $oper;
                            }
                        }

                    }

                }

                foreach ($indexes as $index => $boolean) {
                    for ($i = 0; $i < count($boolean); $i += 2) {
                        if ($boolean[$i + 2] !== null) {
                            if ($boolean[$i + 1] == 'OR') {
                                $indexes[$index] = $boolean[$i] || $boolean[$i + 2];
                            }
                            else if ($boolean[$i + 1] == 'AND') {
                                $indexes[$index] = $boolean[$i] && $boolean[$i + 2];
                            }
                        }
                        else {
                            $indexes[$index] = (bool)$boolean[$i];
                        }
                    }
                }

                foreach ($indexes as $index => $boolean) {
                    if ($boolean) {
                        $this->storage['WHERE'][$key][$index] = $columns[$index];
                    }
                }

            }

            return $this->storage['WHERE'];

        }

        throw new \Exception(__FUNCTION__);

    }

    private function from()
    {

        if (isset($this->storage['FROM'])) {
            return $this->storage['FROM'];
        }

        $from = $this->parser->from();

        if (count($from)) {

            $this->storage['FROM'] = array();
            foreach ($from as $options) {

                $alias = $this->noQuotes($options, 'table');
                if ($options['alias']) {
                    $alias = $this->noQuotes($options['alias']);
                }

                $pathInfo = pathinfo($alias);
                if (isset($pathInfo['filename'])) {
                    $alias = $pathInfo['filename'];
                }

                if ($alias == 'dual') {
                    $this->storage['FROM'][$alias]['string'] = $alias;
                }
                else {
                    $this->storage['FROM'][$alias]['string'] = $this->noQuotes($options, 'table');
                    $this->xmlBuilder->loadXml($this->storage['FROM'][$alias]['string']);
                    $this->storage['FROM'][$alias]['data'] = current($this->xmlBuilder->asArray());
                }

                if (!$this->storage['FROM'][$alias]['data']) {
                    $this->storage['FROM'][$alias]['data'] = array();
                }

            }

            return $this->storage['FROM'];

        }

        throw new \Exception(__FUNCTION__);

    }

}