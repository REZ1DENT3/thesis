<?php

namespace Deimos;

class ArrayObject extends \ArrayObject
{

    const ASC = 1;
    const DESC = -1;

    /**
     * @var int
     */
    private $def = self::ASC;

    /**
     * @param mixed $a
     * @param mixed $b
     * @return int
     */
    public function cmp($a, $b)
    {

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
     * @param int $def
     */
    public function sort($def = self::ASC)
    {
        $this->def = $def;
        $this->uasort(array($this, 'cmp'));
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

        if (!$self) {
            $self = (array)$this;
        }

        if (isset($self[$path[$index]])) {
            if ($index == (count($path) - 1)) {
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

        return $data;

    }

}