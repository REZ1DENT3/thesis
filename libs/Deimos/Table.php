<?php

namespace Deimos;

class Table
{

    /**
     * @var array
     */
    private $_row = [];

    /**
     * @var array
     */
    private $_temp = [];

    /**
     * @return $this
     */
    public function save()
    {
        $this->_row[] = $this->_temp;
        $this->currentClear();
        return $this;
    }

    /**
     * @return $this
     */
    public function allClear()
    {
        $this->_row = [];
        return $this;
    }

    /**
     * @return $this
     */
    public function currentClear()
    {
        $this->_temp = [];
        return $this;
    }

    /**
     * @param $row
     * @return $this
     */
    public function newRow($row)
    {
        $index = 0;
        foreach ($this->_temp as $_k => $_v) {
            $index = max($index, count($_v));
        }

        foreach ($row as $key => $value) {
            if (is_array($value)) {
                $this->newRow($value);
            }
            else {
                if (!isset($this->_temp[$key])) {
                    $this->_temp[$key] = [];
                }
                $this->_temp[$key][$index] = $value;
            }
        }

        return $this;
    }

    private function getMax()
    {
        return 30;
    }

    /**
     * @param $tableId
     * @param $key
     * @return int|mixed
     */
    private function maxLengthForKey($tableId, $key)
    {
        $max = mb_strlen($key);
        foreach ($this->_row[$tableId][$key] as $value) {
            $max = max($max, mb_strlen($value, 'utf-8'));
        }
        return min($max << 1, $this->getMax());
    }

    /**
     * @param $maxLength
     * @return int
     */
    private function newLine($maxLength)
    {
        $str = [];
        foreach ($maxLength as $length) {
            $str[] = sprintf("%'-" . $length . "s", '');
        }
        return printf("+" . implode('+', $str) . "+\n");
    }

    /**
     * @return string
     */
    public function sprintf()
    {
        ob_start();

        $maxLength = [];
        foreach ($this->_row as $tableId => $table) {
            foreach ($table as $key => $value) {
                $maxLength[$tableId][$key] = $this->maxLengthForKey($tableId, $key);
            }
        }

        foreach ($this->_row as $tableId => $table) {

            $this->newLine($maxLength[$tableId]);

            $str = [];
            $keys = array_keys($table);
            for ($j = 0; $j < count($keys); ++$j) {
                $str[] = sprintf('%-' . $maxLength[$tableId][$keys[$j]] . 's', $keys[$j]);
            }
            $str = implode("|", $str);
            echo "|", $str, "|", PHP_EOL;

            $this->newLine($maxLength[$tableId]);

            $values = array_values($table);
            for ($i = 0; $i < count($values[0]); ++$i) {
                $str = [];
                for ($j = 0; $j < count($values); ++$j) {
                    if (!isset($values[$j][$i])) $values[$j][$i] = "";
                    if (mb_strlen($values[$j][$i]) >= $this->getMax())
                        $values[$j][$i] = mb_substr($values[$j][$i], 0, $this->getMax() - 3) . '..';
                    $str[] = sprintf('%-' . $maxLength[$tableId][$keys[$j]] . 's', $values[$j][$i]);
                }
                $str = implode("|", $str);
                echo "|", $str, "|", PHP_EOL;
            }

            $this->newLine($maxLength[$tableId]);

            echo PHP_EOL;

        }

        return ob_get_clean();

    }

}