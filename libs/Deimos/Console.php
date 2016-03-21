<?php

namespace Deimos;

class Console
{

    /**
     * @var string|null $value
     */
    private $value = null;

    /**
     * @return null|string
     */
    public function getLine()
    {
        return $this->value;
    }

    /**
     * @return double|null
     */
    public function getDouble()
    {
        $number = $this->getNumber();
        if ($number === null) {
            return $number;
        }
        return (double)$number;
    }

    /**
     * @return float|null
     */
    public function getFloat()
    {
        $number = $this->getNumber();
        if ($number === null) {
            return $number;
        }
        return (float)$number;
    }

    /**
     * @return int|null
     */
    public function getInt()
    {
        $number = $this->getNumber();
        if ($number === null) {
            return $number;
        }
        return (int)$number;
    }

    /**
     * @return null|string
     */
    public function getNumber()
    {
        if (is_numeric($this->value)) {
            return $this->value;
        }
        return null;
    }

    /**
     * @param string $echo
     */
    public function read($echo = '> ')
    {
        if ($echo) {
            echo $echo;
        }
        fscanf(STDIN, "%[^\n]s", $this->value);
        $this->value = trim($this->value);
    }

    /**
     * @return bool
     */
    public function isEnd()
    {
        $array = array('quit', 'exit', 'q!');
        $val = mb_strtolower($this->value);
        return in_array($val, $array);
    }

    /**
     * @param callable $function
     */
    public function run(callable $function)
    {

        do {

            if (!empty($this->getLine())) {
                $function($this);
            }

            $this->read();

        }
        while (!$this->isEnd());

    }

}