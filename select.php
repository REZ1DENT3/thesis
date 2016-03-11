<?php

include_once "vendor/autoload.php";

//$phpSQLParser = new \PHPSQLParser\PHPSQLParser(
//    "SELECT `employee.weight` FROM `person.xml` WHERE `employee.age` BETWEEN 15 AND 30"
//);

$phpSQLParser = new \PHPSQLParser\PHPSQLParser(
    "SELECT `employee.weight` FROM `person.xml` WHERE `employee.@attributes.id` = 2"
);

var_dump($phpSQLParser);

//$phpSQLParser = new \PHPSQLParser\PHPSQLParser(
//    "SELECT max(employee.weight) FROM person.xml"
//);
//
//var_dump($phpSQLParser);