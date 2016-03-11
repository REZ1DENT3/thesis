<?php

namespace Deimos;

class Semantic
{
    /**
     * @var array
     */
    protected $_row = array();

    /**
     * @var array
     */
    protected $_rowAliases = array();

    /**
     * @var array
     */
    protected $_defaultUnit = array();

    /**
     * @param $name string
     * @return bool
     */
    public function isSemantic($name)
    {
        return isset($this->_row[$name]) ||
        (isset($this->_rowAliases[$name]) &&
            $this->isSemantic($this->_rowAliases[$name]));
    }

    /**
     * @param $name string
     * @param $arguments array
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (in_array($name, array_keys($this->_rowAliases))) {
            $name = $this->_rowAliases[$name];
        }
        return call_user_func_array($this->_row[$name], $arguments);
    }

    /**
     * @param $name
     * @param Rule $value
     * @throws \Exception
     */
    public function __set($name, Rule $value)
    {
        $this->_row[$name] = $value->getMethod();
        $this->_defaultUnit[$name] = $value->getDefaultUnit();
        foreach ($value->getAliasList() as $aliasName) {
            $this->addAlias($name, $aliasName);
        }
    }

    /**
     * @param $name string
     * @param $value string
     * @throws \Exception
     */
    public function setDefaultUnit($name, $value)
    {
        if ($this->isSemantic($name)) {
            $this->_defaultUnit[$name] = $value;
        }
        throw new \Exception();
    }

    /**
     * @param $name
     * @return mixed
     * @throws \Exception
     */
    public function __get($name)
    {
        if ($this->isSemantic($name) && isset($this->_defaultUnit[$name])) {
            return $this->_defaultUnit[$name];
        }
        else if ($this->isSemantic($this->_rowAliases[$name])) {
            return $this->{$this->_rowAliases[$name]};
        }
        throw new \Exception();
    }

    /**
     * @param $name string
     * @param $aliasName string
     * @throws \Exception
     */
    public function addAlias($name, $aliasName)
    {
        if (in_array($name, array_keys($this->_rowAliases)) && $this->isSemantic($this->_rowAliases[$name])) {
            $this->_rowAliases[$aliasName] = &$this->_rowAliases[$name];
        }
        else if ($this->isSemantic($name)) {
            $this->_rowAliases[$aliasName] = $name;
        }
        else {
            throw new \Exception();
        }
    }

    /**
     * @param $name
     * @throws \Exception
     */
    public function getOriginal($name)
    {
        if (in_array($name, array_keys($this->_rowAliases)) && $this->isSemantic($this->_rowAliases[$name])) {
            return $this->_rowAliases[$name];
        }
        else if ($this->isSemantic($name)) {
            return $name;
        }
        else {
            throw new \Exception();
        }
    }
}