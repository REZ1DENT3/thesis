<?php

include_once "vendor/autoload.php";

$xmlBuilder = new Thapp\XmlBuilder\XMLBuilder();

$xml = $xmlBuilder->loadXML(file_get_contents("demo/namespace.xml"), true);
$arrayObject = new \Deimos\ArrayObject($xmlBuilder->toArray($xml));

var_dump($arrayObject);