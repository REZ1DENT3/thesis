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

        if (isset($this->parsed[mb_strtoupper($key)])) {
            $this->_row[$key] = call_user_func_array(array(
                $this, '_' . $name
            ), array());
            return $this->cache($key);
        }

        return array();

    }

    /**
     * @return mixed
     */
    private function _select()
    {
        return $this->parsed['SELECT'];
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
    private function _from()
    {
        return $this->parsed['FROM'];
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
    private function _where()
    {
        return $this->parsed['WHERE'];
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
    private function _orderBy()
    {
        return $this->parsed['ORDER'];
    }

    /**
     * @return mixed
     */
    public function orderBy()
    {
        return $this->cache(__FUNCTION__);
    }

    /**
     * @return mixed
     */
    private function _groupBy()
    {
        return $this->parsed['ORDER'];
    }

    /**
     * @return mixed
     */
    public function groupBy()
    {
        return $this->cache(__FUNCTION__);
    }

}