<?php

namespace Deimos;

class Rule
{
    /**
     * @var callable
     */
    private $_method;

    /**
     * @var string
     */
    private $_defaultUnit;

    /**
     * @var array
     */
    private $_aliasList = array();

    /**
     * Rule constructor.
     * @param string $defaultUnit
     * @param callable $method
     * @param array $aliasList
     */
    public function __construct($defaultUnit, callable $method, array $aliasList = array())
    {
        $this->_defaultUnit = $defaultUnit;
        $this->_aliasList = $aliasList;
        $this->_method = $method;
    }

    /**
     * @return callable
     */
    public function getMethod()
    {
        return $this->_method;
    }

    /**
     * @return string
     */
    public function getDefaultUnit()
    {
        return $this->_defaultUnit;
    }

    /**
     * @return array
     */
    public function getAliasList()
    {
        return $this->_aliasList;
    }
}