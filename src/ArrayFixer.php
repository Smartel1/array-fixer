<?php

namespace Smartel1\ArrayFixer;


/**
 * Класс предназначен для исправления (нормализации) данных в массиве.
 * Для использования передаём в метод fixData массив вида [ [],[],[]... ]
 * Элементы переданного массива будут проверены согласно правилам $rules.
 * Правила задаются как массив вида ['key'=>'rule1|rule2', ...]
 *
 * Правила исправления элементов задаются в классе ArrayFixerRules, передаваемом в конструкторе.
 * Имена методов в этом классе соответствуют названиям правил.
 * Если метод кидает исключение типа UnexpectedValueException, то считается, что
 * элемент не может быть исправлен и исключается из результата. В случае успешного преобразования
 * метод возвращает исправленное значение
 *
 * Поля, не прошедшие исправление исключаются из результата.
 * Доступ к ним через метод getExcluded()
 *
 * @package App\Services
 */
class ArrayFixer
{
    /**
     * Продукт работы программы
     * @var array
     */
    protected $normalizedArray = [];

    /**
     * Побочный продукт - невалидные записи
     * @var array
     */
    protected $excludedArray = [];

    protected $rules;

    /**
     * ArrayFixer constructor.
     * @param ArrayFixerRules $rules
     */
    public function __construct(ArrayFixerRules $rules)
    {
        $this->rules=$rules;
    }

    /**
     * Исправить массив массивов по правилам
     * @param $data
     * @param $rules
     * @return $this
     */

    public function fixData($data, $rules)
    {
        $this->normalizedArray = [];
        $this->excludedArray = [];

        $rules = $this->invertRules($rules);

        foreach ($data as $key=> $value){
            try {
                $fixed = $this->applyRulesToArray((array)$value, $rules);
                $this->normalizedArray[] = $fixed;
            }catch(\UnexpectedValueException $e){
                $this->excludedArray[] = $value;
            }

        }
        return $this;
    }

    /**
     * Применить правила к массиву
     * @param $array
     * @param $rules
     * @return mixed
     */
    private function applyRulesToArray($array, $rules)
    {
        foreach ($rules as $rule=>$fieldNames){
            $array = $this->applyRuleToArray($array, $rule, $fieldNames);
        }
        return $array;
    }



    /**
     * Применить одно правило к массиву
     * @param $array
     * @param $rule
     * @param $fieldNames
     * @return mixed
     */
    private function applyRuleToArray($array, $rule, $fieldNames)
    {
        //Превращаем строку в массив "id, tel.no" => ['id', 'tel.no']
        $fieldNames = explode(',',$fieldNames);
        foreach($fieldNames as $fieldAddress){
            $array = $this->applyRule($array, $rule, $fieldAddress);
        }
        return $array;
    }

    /**
     * Получает массив для применения правила, возвращает правильный массив или исключение
     * @param $arr
     * @param $rule
     * @param $fieldAddress
     * @return mixed
     */
    private function applyRule($arr, $rule, $fieldAddress)
    {
        $fieldAddress = explode('.', $fieldAddress);

        $firstIndex = array_shift($fieldAddress);

        $fieldAddress = implode('.', $fieldAddress);

        return $this->fixField($arr, $firstIndex, $fieldAddress,$rule);
    }

    /**
     * Исправляет поле массива и возвращает массив
     * @param $arr
     * @param $index
     * @param $fieldAddress
     * @param $rule
     * @return mixed
     */
    private function fixField($arr, $index, $fieldAddress, $rule){
        //если надо нырять глубже
        if ($fieldAddress) {
            return $this->dive($arr, $index, $fieldAddress, $rule);
        }

        if (!is_array($arr) or !array_key_exists($index, $arr)) {
            $this->throwIfRequired($rule);
            //Если поля не существует, но стоит правило exists, то создаем
            if ($rule === 'exists') {
                $arr[$index] = null;
            }
            return $arr;
        }

        if ($rule !== 'exists')
            $arr[$index] = $this->rules->$rule($arr[$index]);
        return $arr;
    }

    /**
     *
     * @param $arr
     * @param $index
     * @param $fieldAddress
     * @param $rule
     * @return array
     */
    private function dive($arr, $index, $fieldAddress, $rule){
        //Если встречается *
        if ($index === '*') {
            if(!is_array($arr)){
                $this->throwIfRequired($rule);

                if ($rule === 'exists') {
                    $arr[0] = null;
                } else {
                    return $arr;
                }
            }

            foreach ($arr as $key=>$value){
                $arr[$key] = $this->applyRule($value, $rule, $fieldAddress);
            }
            return $arr;
        }

        //если нырять некуда
        if (!array_key_exists($index, $arr) or !is_array($arr[$index])) {
            $this->throwIfRequired($rule);

            if ($rule === 'exists') {
                $arr[$index] = null;
            } else {
                return $arr;
            }
        }
        $arr[$index] = $this->applyRule($arr[$index], $rule, $fieldAddress);
        return $arr;
    }

    /**
     * Выбросить исключение, если правило === required
     * @param $rule
     */
    private function throwIfRequired($rule){
        if ($rule === 'required') {
            throw new \UnexpectedValueException('Необходимое поле отсутствует');
        }
    }

    /**
     * Переворачивает массив правил
     *
     * @param $rules
     * @return array
     */
    private function invertRules($rules)
    {
        $result = [];

        foreach ($rules as $fieldName=>$ruleNames) {
            $ruleNames = explode('|',$ruleNames);
            foreach ($ruleNames as $ruleName){
                if (array_key_exists($ruleName, $result)){
                    $result[$ruleName] .= ','.$fieldName;
                } else {
                    $result[$ruleName] = $fieldName;
                }
            }
        }
        return $result;
    }

    /**
     * Получить массив нормализованных элементов
     * @return array
     */
    public function get()
    {
        return $this->normalizedArray;
    }

    /**
     * Получить массив исключенных элементов
     * @return array
     */
    public function getExcluded()
    {
        return $this->excludedArray;
    }
}
