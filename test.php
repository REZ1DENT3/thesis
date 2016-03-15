<?php

include_once "vendor/autoload.php";

$xmlBuilder = new Thapp\XmlBuilder\XMLBuilder();

$file = 'employees.xml';
if (isset($_GET['file'])) {
    $file = $_GET['file'];
}

$xml = $xmlBuilder->loadXML(file_get_contents("demo/$file"), true);
$arrayObject = new \Deimos\ArrayObject($xmlBuilder->toArray($xml));

$ids = $arrayObject->get('employees.employee.@id');

var_dump($ids);