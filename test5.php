<?php

var_dump(array_is_numeric([1,2,3]));
die;

//namespace D;
//
//class Queue extends \SplQueue
//{
//}
//
//class Stack extends \SplStack
//{
//}
//
//$queue = new Queue();
//
//foreach (range(1, 100) as $num)
//    $queue->push($num);
//
//while (!$queue->isEmpty()) {
//    var_dump($queue->dequeue());
//}
//
//var_dump([]);
//
//$stack = new Stack();
//
//foreach (range(1, 100) as $num)
//    $stack->push($num);
//
//while (!$stack->isEmpty()) {
//    var_dump($stack->pop());
//}
//
////splfileinfo
//
//class ArrayObject extends \ArrayObject
//{
//
//    const ASC = 1;
//    const DESC = -1;
//
//    /**
//     * @var int
//     */
//    private $def = self::ASC;
//
//    /**
//     * @param mixed $a
//     * @param mixed $b
//     * @return int
//     */
//    public function cmp($a, $b)
//    {
//
//        if (is_numeric($a) && is_numeric($b)) {
//            return $this->def * ($a - $b);
//        }
//        else if (is_object($a) && is_object($b)) {
//            $a = spl_object_hash($a);
//            $b = spl_object_hash($b);
//        }
//        else if (is_array($a) && is_array($b)) {
//            $a = count($a);
//            $b = count($b);
//        }
//
//        return $this->def * strcmp($a, $b);
//
//    }
//
//    /**
//     * @param int $def
//     */
//    public function sort($def = self::ASC)
//    {
//        $this->def = $def;
//        $this->uasort(array($this, 'cmp'));
//    }
//
//}
//
//$arrayObject = new \D\ArrayObject();
//
//for ($i = 0; $i < 9; ++$i) {
//    $arrayObject[$i] = rand(1, 100);
//}
//
//var_dump($arrayObject);
//$arrayObject->sort();
//var_dump($arrayObject);
//$arrayObject->sort(-1);
//var_dump($arrayObject);
