<?php

namespace Deimos;

class XMLBuilder extends \Thapp\XmlBuilder\XMLBuilder
{

    /**
     * @var \Thapp\XmlBuilder\Dom\DOMDocument|null
     */
    private $loadXml;

    /**
     * @var array
     */
    private $storage = array();

    /**
     * @param string $xml
     * @param bool $sourceIsString
     * @param bool $simpleXml
     * @return mixed
     */
    public function loadXml($xml, $sourceIsString = false, $simpleXml = false)
    {
        if (is_string($xml) && file_exists($xml)) {
            $xml = file_get_contents($xml);
            $sourceIsString = true;
        }
        return $this->loadXml = parent::loadXml($xml, $sourceIsString, $simpleXml);
    }

    /**
     * @return array
     */
    public function asArray()
    {
        if ($this->loadXml && empty($this->storage)) {
            $this->storage = $this->toArray($this->loadXml);
        }
        return $this->storage;
    }

}