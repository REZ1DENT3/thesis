<?php

namespace old;

class SemanticParser
{

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var \stdClass
     */
    private $data;

    /**
     * @var SemanticUltra
     */
    private $semantic;

    /**
     * @return SemanticUltra
     */
    public function getSemantic()
    {
        return $this->semantic;
    }

    /**
     * SemanticParser constructor.
     * @param Parser $parser
     */
    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
        $this->semantic = new SemanticUltra();
    }

    /**
     * @param null $items
     * @param null $data
     * @return \stdClass
     */
    public function execute($items = null, &$data = null)
    {

        if ($items === null) {
            $items = $this->parser;
        }

        if ($data === null) {
            $data = &$this->data;
        }

        /**
         * @var $item Parser
         */
        foreach ($items as $item) {

            $tagName = $item->getName();

            if ($this->semantic->isSemantic($tagName)) {
                $tagName = $this->semantic->getOriginal($tagName);
            }

            if (empty($data)) {
                $data = new \stdClass();
            }

            if (!isset($data->{$tagName})) {
                $data->{$tagName} = array();
            }

            $data->{$tagName}[] = new \stdClass();

            end($data->{$tagName});
            $key = key($data->{$tagName});

            /**
             * @var $_item Parser
             */
            $_item = &$data->{$tagName}[$key];
            $_item->{'@attributes'} = $item->attr();

            $this->execute($item, $_item);

            /**
             * @var $element Parser
             */
            foreach ($item as $tgName => $element) {

                $attr = $element->attr();

                if ($this->semantic->isSemantic($tgName)) {
                    $tgName = $this->semantic->getOriginal($tgName);
                }

                if (!isset($_item->{$tgName})) {
                    $_item->{$tgName} = new \stdClass();
                }
                else if (is_array($_item->{$tgName})) {
                    $_item->{$tgName} = new \stdClass($_item->{$tgName});
                }

                if (!empty($attr)) {
                    $_item->{$tgName}->{'@attributes'} = $attr;
                }

                if (!$this->semantic->isSemantic($tgName)) {
                    $_item->{$tgName}->{'#value'} = (object)$element;
                    if (count((array)$_item->{$tgName}->{'#value'}) == 1) {
                        if (is_array($_item->{$tgName}->{'#value'}->{0})) {
                            $_item->{$tgName} = (array)$_item->{$tgName}->{'#value'}->{0};
                            $_item->{$tgName} = QueryParser::convertToObject($_item->{$tgName});
                        }
                        else {
                            $_item->{$tgName} = (array)$_item->{$tgName}->{'#value'};
                            if (count($_item->{$tgName}) == 1) {
                                $_item->{$tgName} = $_item->{$tgName}[0];
                            }
                        }
                    }
                    continue;
                }

                $type = $element->attr('type');
                if ($type) {
                    $value = (string)$element;
                    if (empty($value)) {
                        $value = $element->attr('value');
                    }
                    if ($value) {
                        $result = $this->semantic->{mb_strtoupper($tgName)}($value, $type);
                        if ($result instanceof \PhpUnitsOfMeasure\AbstractPhysicalQuantity) {
                            $_item->{$tgName}->{'#value'} = array(
                                'value' => $result->toUnit($this->semantic->{$tgName}),
                                'type' => $this->semantic->{$tgName}
                            );
                        }
                    }
                }
                else {
                    $value = (string)$element;
                    if (empty($value)) {
                        $value = $element->attr('value');
                    }
                    $_item->{$tgName}->{'#value'} = array(
                        'value' => $this->semantic->{$tgName}($value),
                        'type' => $this->semantic->{$tgName}
                    );
                }

            }
        }

        return $this->data;

    }

    public function getSemanticData()
    {
        if ($this->data) {
            return $this->data;
        }
        return $this->execute();
    }

}