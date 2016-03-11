<?php

include_once "vendor/autoload.php";
include_once "smtc.php";

$dom = new \Deimos\Parser(file_get_contents('person.xml'));

$index = 1;
$employees = new stdClass();

foreach ($dom as $employee) {

    if (!isset($employees->{$employee->getName()})) {
        $employees->{$employee->getName()} = array();
    }

    $employees->{$employee->getName()}[] = new stdClass();

    end($employees->{$employee->getName()});

    $key = key($employees->{$employee->getName()});

    $_employee = &$employees->{$employee->getName()}[$key];
    $_employee->{'@attributes'} = $employee->attr();

    /**
     * @var \Deimos\Parser $element
     */
    foreach ($employee as $tagName => $element) {

        if (!isset($_employee->{$tagName})) {
            $_employee->{$tagName} = new stdClass();
            $_employee->{$tagName}->{'@attributes'} = $element->attr();
        }

        if (!$semantic->isSemantic($tagName)) {
            $_employee->{$tagName}->{'@value'} = (string)$element;
            continue;
        }

        $result = $semantic->{$tagName}((string)$element, $element->attr('type'));

        if ($result instanceof \PhpUnitsOfMeasure\AbstractPhysicalQuantity) {
            $_employee->{$tagName}->{'@value'} = array(
                'value' => $result->toUnit($semantic->{$tagName}),
                'type' => $semantic->{$tagName}
            );
        }

    }

}

var_dump($employees);

//$xmlBuilder = new \Thapp\XmlBuilder\XMLBuilder($dom->getName());
//$xmlBuilder->load($employees);
//
//$newDom = new \Deimos\Parser($xmlBuilder->createXML(true));
//
//foreach ($newDom->xpath('//item/weight') as $array) {
//    var_dump([round((double)$array->value, 2), (string)$array->type]);
//}

//header('Content-type: text/xml');
//
//echo $xmlBuilder->createXML(true);