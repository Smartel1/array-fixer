## Laravel 5 - ArrayFixer

Библиотека позволяет проводить валидацию массивов, при этом не только отсеивая непрошедшие результаты, но и предпринимая попытки "починить" значения.

Есть возможность добавить собственные правила валидации/ремонта.



## Установка через composer

Для установки пакета введите в консоли:

```
composer require smartel1/array-fixer
```

Посл установки допишите в `config/app.php` в массив `providers`:

```php
'providers' => [
        ...
    	Smartel1\ArrayFixer\ArrayFixerServiceProvider::class,
]
```


### Использование 

Класс принимает массив массивов: [ [],[],[] ...] и применяет массив правил вида ['key'=>'rule1|rule2', ...].

Для получения исправленных данных используется метод get(),
для получения данных, не прошедших проверку и исправление - getExcluded().

```php
	public function someFunction(ArrayFixer $fixer)
	{
		$array = [['key'=>'123'],['key'=>2]];
        	$rules = ['key'=>'integer|required'];

        	$fixed = $fixer->fixData($array, $rules)->get();

		$excluded = $fixer->fixData($array, $rules)->getExcluded();		
	}
```
Путь к элементу:

```php
	
     $rules = ['key1.key2'=>'integer'];
     $rules = ['key1.*.key2'=>'integer']; //Применение правила integer к полю key2 всех элементов поля key1 

```

Сейчас реализованы правила:

 integer - приводит значение к целочисленному типу

 double - приводит к числу с плавающей точкой

 required - если поля, помеченного этим правилом, не существует, то элемент отправится в массив excluded

 exists - аналог required, но при отсутствии поля оно добавится и заполнится значением null

 url - попытка провалидировать url. Добавит схему "http://" и заменит пробелы на "%20"
 
