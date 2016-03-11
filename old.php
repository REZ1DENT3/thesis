<?php

include_once "vendor/autoload.php";
include_once "smtc.php";

use com\soloproyectos\common\dom\node\DomNode;
use Thapp\XmlBuilder\XmlBuilder;
use Thapp\XmlBuilder\Normalizer;

$employees = array();

$dom = DomNode::createFromStringWithFile('person.xml');

// new SimpleXMLElement(file_get_contents('person.xml')

//while (list($key, $val) = each($fruit)) {
//    echo "$key => $val\n";
//}

foreach ($dom->xpath('//employee') as $employee) {

    $employees[(int)$employee->attr('id')] = array(
        '@attributes' => array('id' => (int)$employee->attr('id'))
    );
    foreach ($employee->query('*') as $param) {
        if ($semantic->isSemantic($param->name())) {
            $employees[$employee->attr('id')][$param->name()]['value'] = $semantic->{$param->name()}(
                $param->text(),
                $param->attr('type')
            )->toUnit($semantic->{$param->name()});
            $employees[$employee->attr('id')][$param->name()]['type'] = $semantic->{$param->name()};
        }
        else {
            $employees[$employee->attr('id')][$param->name()] = $param->text();
        }
    }

}

$xmlBuilder = new XmlBuilder($dom->name());
$xmlBuilder->load($employees);

var_dump($employees);

$newDom = DomNode::createFromString($xmlBuilder->createXML(true));
foreach ($newDom->xpath('//age') as $age) {
    var_dump($age->query('value')->text() . ' ' . $age->query('type')->text());
}

//header('Content-type: text/xml');
//
//echo $xmlBuilder->createXML(true);