<?php

namespace Deimos;

class SemanticUltra extends Semantic
{

    public function __construct()
    {

        $this->acceleration = new \Deimos\Rule('m/s^2', function ($value, $type) {
            return new \PhpUnitsOfMeasure\PhysicalQuantity\Acceleration($value, $type);
        });

        $this->angle = new \Deimos\Rule('rad', function ($value, $type) {
            return new \PhpUnitsOfMeasure\PhysicalQuantity\Angle($value, $type);
        });

        $this->electricCurrent = new \Deimos\Rule('amp', function ($value, $type) {
            return new \PhpUnitsOfMeasure\PhysicalQuantity\ElectricCurrent($value, $type);
        });

        $this->energy = new \Deimos\Rule('joule', function ($value, $type) {
            return new \PhpUnitsOfMeasure\PhysicalQuantity\Energy($value, $type);
        });

        $this->luminousIntensity = new \Deimos\Rule('cd', function ($value, $type) {
            return new \PhpUnitsOfMeasure\PhysicalQuantity\LuminousIntensity($value, $type);
        });

        $this->pressure = new \Deimos\Rule('pascal', function ($value, $type) {
            return new \PhpUnitsOfMeasure\PhysicalQuantity\Pressure($value, $type);
        });

        $this->temperature = new \Deimos\Rule('celsius', function ($value, $type) {
            return new \PhpUnitsOfMeasure\PhysicalQuantity\Temperature($value, $type);
        });

        $this->time = new \Deimos\Rule('sec', function ($value, $type) {
            return new \PhpUnitsOfMeasure\PhysicalQuantity\Time($value, $type);
        });

        $this->velocity = new \Deimos\Rule('meters/sec', function ($value, $type) {
            return new \PhpUnitsOfMeasure\PhysicalQuantity\Velocity($value, $type);
        });

        $this->volume = new \Deimos\Rule('m^3', function ($value, $type) {
            return new \PhpUnitsOfMeasure\PhysicalQuantity\Volume($value, $type);
        });

        $this->weight = new \Deimos\Rule('kg', function ($value, $type) {
            return new \PhpUnitsOfMeasure\PhysicalQuantity\Mass($value, $type);
        });

        $this->length = new \Deimos\Rule('cm', function ($value, $type) {
            return new \PhpUnitsOfMeasure\PhysicalQuantity\Length($value, $type);
        });

        $this->age = new \Deimos\Rule('year', function ($value, $type) {
            return new \PhpUnitsOfMeasure\PhysicalQuantity\Time($value, $type);
        });

        // Length
        $this->addAlias('length', 'growth');
        $this->addAlias('growth', 'body height');
        $this->addAlias('body height', 'bodyHeight');
        $this->addAlias('bodyHeight', 'body-height');

        // Weight
        $this->addAlias('weight', 'body weight');
        $this->addAlias('body weight', 'bodyWeight');
        $this->addAlias('bodyWeight', 'body-weight');

    }

}