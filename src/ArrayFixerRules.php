<?php

namespace Smartel1\ArrayFixer;


class ArrayFixerRules
{
    /**
     * Правка integer
     * @param $value
     * @return int
     */
    public function integer($value)
    {
        $value = preg_replace('~[^0-9]+~','',$value);

        $fixed = intval($value);

        return $fixed;
    }

    /**
     * Правка double
     * @param $value
     * @return float
     */
    public function double($value){

        $value = preg_replace('~[^0-9,.]+~','',$value);
        $value = preg_replace('~,~','.',$value);

        $fixed = doubleval($value);

        return $fixed;
    }

    /**
     * Правка url-ов
     * @param $value
     * @return float
     */
    public function url($value){
        if (!$value) return null;
        //Если нет схемы, то добавить
        if (!preg_match("~^(?:f|ht)tps?://~i", $value)) {
            $value = "http://" . $value;
        }
        //Пробелы заменяем на
        $value = str_replace(" ","%20", $value);

        return $value;
    }

    /**
     * Проверка необходимых полей
     * @param $value
     * @return mixed
     */
    public function required($value)
    {
        if (!$value and $value!=='' and $value!==0) {
            throw new \UnexpectedValueException('Необходимое поле отсутствует');
        }
        return $value;
    }


    public function __call($name, $arguments)
    {
        throw new \BadMethodCallException('Правило проверки '.$name.' не реализовано');
    }
}
