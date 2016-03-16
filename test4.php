<?php

include "vendor/autoload.php";

//$sql = new \Deimos\Query("
//  SELECT `*`
//  FROM `demo/employees.xml`
//  WHERE (sin(2 ^ 8) * cos(`employee.@id`)) > 0.2
//");

//$sql = new \Deimos\Query("
//  SELECT `*`
//  FROM `demo/employees.xml`
//  WHERE ((2 ^ 8) - cos(`employee.@id`)) > 1
//");

//$sql = new \Deimos\Query("
//  SELECT `*`
//  FROM `demo/employees.xml`
//  WHERE `employee.age` < (444  + (44 - cos(`employee.@id`)) - 444)
//    AND `employee.@id` = 3
//");

//$sql = new \Deimos\Query("
//  SELECT `*`
//  FROM `demo/employees.xml`
//  WHERE `employee.weight` BETWEEN 58 AND 76.2
//");

$sql = new \Deimos\Query("
  SELECT `*`
  FROM `demo/employees.xml`
  WHERE (`employee.@id` % 2) = 0
");

//$sql = new \Deimos\Query("
//    SELECT *
//    FROM `demo/employees.xml`
//    WHERE `employee.firstname` = `Ivan`
//");

//$sql = new \Deimos\Query("
//    SELECT *
//    FROM `demo/employees.xml`
//    WHERE `employee.lastname` LIKE upper(`ivanov`)
//");

//$sql = new \Deimos\Query("
//  SELECT `*`
//  FROM `demo/employees.xml`
//  WHERE (sin(2 ^ 8) * `employee.@id`) < 0.07
//");

var_dump($sql->execute());