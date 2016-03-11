<?php

/**
 * Interface AInt
 */
interface AInt {
    /**
     * @return mixed
     */
    public function say();
}

/**
 * Class L
 */
abstract class L implements AInt {}

/**
 * Class A
 */
class A extends L
{
    /**
     * @return string
     */
    public function say()
    {
        return 'Hello!';
    }
}

/**
 * Class B
 */
class B extends A {}

/**
 * Class C
 */
abstract class C extends B {}

$a = new A();
var_dump([$a instanceof AInt, $a->say()]);

$b = new B();
var_dump([$b instanceof A, $b->say()]);

$c = new C();
var_dump([$c instanceof B, $c->say()]);