<?php

include_once 'vendor/autoload.php';

$xmlBuilder = new \Deimos\XMLBuilder();
$xmlBuilder->loadXml('demo/simple.xml');

var_dump($xmlBuilder->asArray());
