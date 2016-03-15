<?php

namespace Deimos;

class ArrayObject extends \ArrayObject
{

    /**
     * @param array $array
     * @return bool
     */
    public final function checkKeysIsNumber(array $array)
    {
        foreach (array_keys($array) as $value) {
            if (!is_numeric($value)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param string $path
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