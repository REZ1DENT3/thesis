<?php

namespace Deimos;

class ArrayObject extends \ArrayObject
{

    const ASC = 'ASC';
    const DESC = 'DESC';

    /**
     * @var int
     */
    private $def = 1; // self::ASC

    /**
     * @param mixed $a
     * @param mixed $b
     * @return int
     */
    public function cmp($a, $b)
    {

        if ($a === null && $b === null) return 0;

        if ($a === null) return 1;
        if ($b === null) return -1;

        if (is_numeric($a) && is_numeric($b)) {
            return $this->def * ($a - $b);
        }
        else if (is_object($a) && is_object($b)) {
            $a = spl_object_hash($a);
            $b = spl_object_hash($b);
        }
        else if (is_array($a) && is_array($b)) {
            $a = count($a);
            $b = count($b);
        }

        return $this->def * strcmp($a, $b);

    }

    /**
     * @param string $def
     * @return int
     */
    private function calcDef($def)
    {
       return 2 - (3 >> (mb_strtoupper($def) === self::ASC));
    }

    /**
     * @param string $def
     */
    public function sort($def = self::ASC)
    {
        $this->def = $this->calcDef($def);
        $this->uasort(array($this, 'cmp'));
    }

    /**
     * @param string $def
     */
    public function orderBy($path, $def = self::ASC)
    {
        $this->def = $this->calcDef($def);
        $this->uasort(function ($a, $b) use ($path) {

            $c = (new self($a))->get($path);
            $d = (new self($b))->get($path);

            if (!count($c) || !count($d)) return 0;

            $e = (new self($c))->get('#value.value');
            $f = (new self($d))->get('#value.value');

            if ($e[0] !== null && $f[0] !== null)
                return $this->cmp($e[0], $f[0]);

            return $this->cmp($c[0], $d[0]);

        });
    }

    /**
     * @param array $array
     * @return bool
     */
    public final function checkKeysIsNumber(array $array)
    {
        return ctype_digit(implode('', array_keys($array)));
    }

    /**
     * @param string|array $path
     * @param int $index
     * @param null $self
     * @param array $data
     * @return array
     */
    public function get($path, $index = 0, $self = null, &$data = array())
    {

        if (is_string($path)) {
            $path = preg_replace('~@(attributes.){0,1}~', '@attributes.', $path);
            $path = explode('.', $path);
        }

        $count = count($path) - 1;

        $asterisk = end($path) == '*';
        if ($asterisk) {
            unset($path[$count]);
            $count--;
        }

        if (!$self) {
            $self = (array)$this;
        }

        if (isset($self[$path[$index]])) {
            if ($index == $count) {
                $data[] = $self[$path[$index]];
            }
            else {
                $this->get($path, $index + 1, $self[$path[$index]], $data);
            }
        }
        else if (is_array($self) && $this->checkKeysIsNumber($self)) {
            foreach ($self as $value) {
                $this->get($path, $index, $value, $data);
            }
        }

        if (($index == $count) && $asterisk) {
            if (count($data) == 1) {
                $data = current($data);
            }
        }

        return $data;

    }

}