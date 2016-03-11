<?php

include_once "vendor/autoload.php";

$query = new \Deimos\Query("
  SELECT avg(`employee.@id`)
  FROM `demo/employees.xml`
  WHERE
        `employee.@id` IN (1, 2, 3, 4, 5) OR
        `employee.@id` BETWEEN 2 AND 54 OR
        `employee.age` > 20
  ORDER BY `employee.@id`
");

//$query = new \Deimos\Query("
//  SELECT *
//  FROM `demo/employees2.xml`
//  WHERE `employee.@id` != 0
//  ORDER BY `employee.@id` DESC
//");

//$query = new \Deimos\Query("
//  SELECT *
//  FROM `demo/employees2.xml`
//  ORDER BY `employee.@id` DESC
//");

//$query = new \Deimos\Query("
//  SELECT *
//  FROM `demo/employees2.xml`
//  WHERE `employee.hiredate` BETWEEN `15-10-2000` AND `15-10-2015`
//  ORDER BY `employee.@id` DESC
//");

//$query = new \Deimos\Query("
//  SELECT *
//  FROM `demo/employees.xml`
//  WHERE `employee.weight` < 60
//        AND `employee.weight` > 56
//");

//$query = new \Deimos\Query("
//  SELECT tan(max(`employee.length`))
//  FROM `demo/employees.xml`
//");

//$query = new \Deimos\Query("
//  SELECT *
//  FROM `demo/employees.xml`
//  WHERE `employee.length` IS 173
//");

//$query = new \Deimos\Query("
//  SELECT *
//  FROM `demo/employees.xml`
//  WHERE `employee.length` IS NOT 173
//");

//$query = new \Deimos\Query("
//  SELECT *
//  FROM `demo/employees.xml`
//  WHERE `employee.length` = (
//    SELECT max(employee.length) FROM `demo/employees.xml`
//  )
//");

//$query = new \Deimos\Query("
//  SELECT *
//  FROM `demo/employees.xml`
//  WHERE `employee.length` > (
//    SELECT sin(min(`employee.length`)) FROM `demo/employees.xml`
//  )
//");

var_dump($query->execute());