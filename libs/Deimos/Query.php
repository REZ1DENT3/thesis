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

    /**
     * @param $options
     * @param $semantic SemanticParser
     * @param Stack|null $stack
     * @param bool $first
     * @return array
     */
    private function parsingOfData($options, $semantic, Stack &$stack = null, $first = true)
    {

        if (!$stack) {
            $stack = new Stack();
        }

        foreach ($options as $option) {

            if ($option['expr_type'] !== ExpressionType::BRACKET_EXPRESSION)
                $stack->push($option);

            if ($option['sub_tree']) {

                if ($option['expr_type'] == ExpressionType::EXPRESSION)
                    continue;

                $this->parsingOfData($option['sub_tree'], $semantic, $stack, false);

            }

        }

        if ($first) {

            $parameters = array();
            $isOperator = false;
            $clear = false;

            while ($stack->peek()) {

                $data = $stack->pop();

                var_dump($data);

                switch ($data['expr_type']) {

                    case ExpressionType::CONSTANT:
                        $parameters[] = $this->noQuotes($data);
                        break;

                    case ExpressionType::COLREF:
                        if (!$this->isOperator($data)) {
                            $parameters[] = $semantic->get($this->noQuotes($data));
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
                        // execute
                        $clear = true;
                        break;

                }

                if ($isOperator) {
                    $parameters = array(Parser::solve(implode($parameters)));
                }

                if ($clear) {
                    $parameters = array();
                    $clear = false;
                }

                $isOperator = $this->isOperator($data);

            }
        }

        return array();

    }

    public function where()
    {

        if (isset($this->storage['WHERE'])) {
            return $this->storage['WHERE'];
        }

        $from = current($this->from());
        $semantic = new SemanticParser($from['data']);

        $where = $this->parser->where();

        if (count($where)) {

            $this->storage['WHERE'] = array();

            foreach ($where as $options) {
                var_dump($this->parsingOfData(array($options), $semantic));
                die;
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