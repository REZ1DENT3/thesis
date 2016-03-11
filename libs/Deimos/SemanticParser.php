<?php

namespace Deimos;

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
                    $_item->{$tgName}->{'@value'} = (string)$element;
                }

                $type = $element->attr('type');

                if ($type) {
                    $value = (string)$element;
                    if (empty($value)) {
                        $value = $element->attr('value');
                    }
                    if ($value) {
                        $result = $this->semantic->{$tgName}($value, $type);
                        if ($result instanceof \PhpUnitsOfMeasure\AbstractPhysicalQuantity) {
                            $_item->{$tgName}->{'@attributes'}['originalSemantic'] = array(
                                '@value' => $value,
                                '@type' => $type
                            );
                            $_item->{$tgName}->{'@value'} = array(
                                'value' => $result->toUnit($this->semantic->{$tgName}),
                                'type' => $this->semantic->{$tgName}
                            );
                        }
                    }
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