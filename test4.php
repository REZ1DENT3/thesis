<?php

include "vendor/autoload.php";

$sql = new \Deimos\Query("
  SELECT `*`
  FROM `demo/employees.xml`
  WHERE (sin(2 ^ 8) * avg(`employee.@id`)) > 1
        AND hello(15, 99)
        AND min(1,2,3,4)
");

var_dump($sql->where());