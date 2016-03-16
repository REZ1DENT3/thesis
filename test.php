<?php

include_once "vendor/autoload.php";

$xmlBuilder = new Thapp\XmlBuilder\XMLBuilder();

$file = 'employees.xml';
if (isset($_GET['file'])) {
    $file = $_GET['file'];
}

$xml = $xmlBuilder->loadXML(file_get_contents("demo/$file"), true);
$arrayObject = new \Deimos\ArrayObject($xmlBuilder->toArray($xml));

$idList = $arrayObject
    ->get('employees.employee.@id');

function avg($param)
{
    if (is_array($param)) {
        return array_sum($param) / count($param);
    }
    return false;
}

var_dump(avg($idList));