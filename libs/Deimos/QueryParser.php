<?php

namespace Deimos;

class QueryParser extends \PHPSQLParser\PHPSQLParser
{

    /**
     * @var array
     */
    private $_row = array();

    /**
     * @param bool|string $sql
     * @param bool $calcPositions
     */
    public function __construct($sql = false, $calcPositions = false)
    {
        if ($sql) {
            $this->parse($sql, $calcPositions);
            $this->parsed = $this->convertToObject($this->parsed);
        }
    }

    /**
     * @param string $name
     * @return mixed
     */
    private function cache($name)
    {

        $key = preg_replace('~By$~', '', $name);

        if (isset($this->_row[$key])) {
            return $this->_row[$key];
        }

        if (isset($this->parsed->{mb_strtoupper($key)})) {
            $this->_row[$key] = call_user_func_array(array(
                $this, '_' . $name
            ), array());
            return $this->cache($key);
        }

        return array();

    }

    /**
     * @param array|\stdClass $array
     * @return \stdClass
     */
    public static function convertToObject($array)
    {
        $object = new \stdClass();
        foreach ($array as $key => $value) {
            if ($value instanceof \Deimos\Parser) {
                $value = (array)$value;
            }
            if (is_array($value)) {
                $value = self::convertToObject($value);
            }
            $object->$key = $value;
        }
        return $object;
    }

    /**
     * @return mixed
     */
    final protected function _select()
    {
        return $this->parsed->SELECT;
    }

    /**
     * @return mixed
     */
    public function select()
    {
        return $this->cache(__FUNCTION__);
    }

    /**
     * @return mixed
     */
    final protected function _from()
    {
        return $this->parsed->FROM;
    }

    /**
     * @return mixed
     */
    public function from()
    {
        return $this->cache(__FUNCTION__);
    }

    /**
     * @return mixed
     */
    final protected function _where()
    {
        return $this->parsed->WHERE;
    }

    /**
     * @return mixed
     */
    public function where()
    {
        return $this->cache(__FUNCTION__);
    }

    /**
     * @return mixed
     */
    final protected function _orderBy()
    {
        return $this->parsed->ORDER;
    }

    /**
     * @return mixed
     */
    public function orderBy()
    {
        return $this->cache(__FUNCTION__);
    }

}