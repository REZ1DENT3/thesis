<?php

include "vendor/autoload.php";

//$sql = new \Deimos\Query("
//  SELECT `*`
//  FROM `demo/employees.xml`
//  WHERE (sin(2 ^ 8) * cos(`employee.@id`)) > 1
//        AND hello(15, 99)
//        AND min(1,2,3,4)
//");

//$sql = new \Deimos\Query("
//  SELECT `*`
//  FROM `demo/employees.xml`
//  WHERE (sin(2 ^ 8) * cos(`employee.@id`)) > 1
//");

//$sql = new \Deimos\Query("
//  SELECT `*`
//  FROM `demo/employees.xml`
//  WHERE `employee.age` < (444  + (44 - cos(`employee.@id`)) - 444)
//    AND `employee.@id` = 3
//");

$sql = new \Deimos\Query("
  SELECT `*`
  FROM `demo/employees.xml`
  WHERE `employee.@id` = 3
");


//$sql = new \Deimos\Query("
//  SELECT `*`
//  FROM `demo/employees.xml`
//  WHERE (sin(2 ^ 8) * `employee.@id`) < 0.07
//");

var_dump($sql->execute());