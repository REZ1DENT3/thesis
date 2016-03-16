<?php

namespace Deimos;

class SemanticParser
{
    /**
     * @var array
     */
    private $storage = array();

    /**
     * @var array
     */
    private $arrayObject = array();

    /**
     * @var SemanticUltra
     */
    private $semantic;

    /**
     * SemanticParser constructor.
     * @param $array array
     */
    public function __construct($array)
    {
        $this->semantic = new SemanticUltra();
        $this->storage = $array;
        $this->init($this->storage);
        $this->arrayObject = new ArrayObject($this->storage);
    }

    /**
     * @param $storage array
     */
    private function init(&$storage)
    {

        if (!is_array($storage) || isset($storage['nodevalue'])) {
            return;
        }

        foreach ($storage as $key => &$element) {

            $type = null;
            if (isset($element['@attributes']['type'])) {
                $type = $element['@attributes']['type'];
            }

            $value = null;
            if (isset($element['nodevalue'])) {
                $value = $element['nodevalue'];
            }

            if (isset($element['@attributes']['value'])) {
                $value = $element['@attributes']['value'];
            }

            $name = $key;
            if (isset($element['@attributes']['name'])) {
                $name = $element['@attributes']['name'];
            }

            $func = null;
            if ($this->semantic->isSemantic($name)) {
                $func = $this->semantic->getOriginal(mb_strtoupper($name));
            }

            if ($func) {

                $result = $this->semantic->{$func}($value, $type);
                if ($result instanceof \PhpUnitsOfMeasure\AbstractPhysicalQuantity) {
                    $element['#value'] = array(
                        'value' => $result->toUnit($this->semantic->{$func}),
                        'type' => $this->semantic->{$func}
                    );
                }
                else {
                    $element['#value'] = array(
                        'value' => $result,
                        'type' => $this->semantic->{$func}
                    );
                }

                if (mb_strtoupper($func) !== mb_strtoupper($key)) {
                    $storage[$func] = $element;
                    unset($storage[$key]);
                }
            }

            $this->init($element);

        }

    }

    /**
     * @return ArrayObject
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @param $path string
     * @return array
     */
    public final function get($path)
    {
        $rows = $this->arrayObject->get($path);
        foreach ($rows as &$row) {
            if (is_array($row)) {
                if (isset($row['#value'])) {
                    $row = $row['#value']['value'];
                }
                if (isset($row['nodevalue'])) {
                    $row = $row['nodevalue'];
                }
            }
        }
        return $rows;
    }

}