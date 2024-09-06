### Импорт/обновление
В зависимости от того, какую стратегию использования данных справочника вы выберите, воспользуйтесь одним из следующих способов импорта:

1. Если ваша "корзина" заказа, в разделе доставки, должна хранить ссылку на данные отделения(к примеру, по полю `reference`), и в дальнейшем вам необходимо предоставлять сервис трекинга заказа, важно чтобы во время импорта/обновления справочника(прим. 1-3 минуты, в зависимости от объема) данные об отделении были доступны.

```php
// import.php
use NG\API\Warehouses\Service\Import;
use NG\API\Warehouses\Service\Subscriber;
use WebStone\PDO\Database;
use WebStone\Requests\Clients\ClientCurl;

// Конфигурация сервиса импорта
$config = [
    // Настройки подключения к базе данных
    'db'         => [
        'class'       => Database::class,
        'connections' => [
            [
                'key'      => 'mysql',
                'dsn'      => 'mysql:host=localhost;dbname=test',
                'user'     => 'user',
                'password' => 'password',
                'options'  => [],
            ],
        ],
        'default'     => 'mysql', // соединение будет активировано автоматически
    ],
    // Настройки клиента API
    'subscriber' => [
        'class'    => Subscriber::class,
        'client'   => [
            'class'            => ClientCurl::class,
            // Реквизиты для авторизации веб-клиета, доступны в вашем личном кабинете на сайте NPG
            'auth_type'        => 'basic',
            'username'         => 'username',
            'password'         => 'password',

            'protocol_version' => '1.1',
            'redirects_count'  => 5,
            'timeout'          => 10,
            'blocking'         => false,
        ],
        // Реквизиты для доступа к сервису справочников отделений, доступны в вашем личном кабинете на сайте NPG
        'host'     => 'api.novaposhta.international',
        'port'     => '8243',
        'protocol' => 'https',
    ],
    // Выполнять логирование процесса или нет(см. ниже)
    'logging' => false
];

// Создаем экземпляр объекта `Import` и выполняем импорт/обновление
$import = new Import($config);
$import->execute([
    // двух-буквенный иденификатор страны
    'country' => 'PL',
    
    // с какой страницы справочника начинать импорт
    'page' => 1,

    // возвращать(1) или нет(0) расширенную информацию об отделении; не используется
    'ext' => '0',

    // дополнительные фильтры(тип, владелец и т.д.) импорта отделений
    // подробности доступны в вашем личном кабинете на сайте NPG
    // ...
    
    // частичный/полный импорт
    // установите true, чтобы выполнить импорт только одной конкретной страницы справочника
    // установите false, чтобы выполнить импорт всего справочника
    'partial'  => false,
    
    // при успешном импорте удалить `старые` данные
    'old_remove' => false,
    
    // при успешном импорте деактивировать `старые` данные
    'old_deactivate' => true,
]);
```
В данном случае будет выполнен импорт всех отделения для страны Польша(PL) и при успешном окончании процесса импорта **старые** данные будут деактивированы(`_active = 0`)

2. Если ваша "корзина" заказа, в разделе доставки, должна хранить(копировать реквизиты) данные отделения, и в случае реализации сервиса трекинга заказа не важно текущее состояние справочника.
   
```php
// import.php
use NG\API\Warehouses\Service\Import;
use NG\API\Warehouses\Service\Subscriber;
use WebStone\PDO\Database;
use WebStone\Requests\Clients\ClientCurl;

// Конфигурация сервиса импорта
$config = [
    // ...
];

// Создаем экземпляр объекта `Import` и выполняем импорт/обновление
$import = new Import($config);
$import->execute([
    'country' => 'PL',
    'page' => 1,
    'ext' => '0',
    'partial'  => false,
    'old_remove' => true,
    'old_deactivate' => false,
]);
```
В данном случае будет выполнен импорт всех отделения для страны Польша(PL) и при успешном окончании процесса импорта **старые** данные будут удалены.

3. Если вы хотите выполнить импорт справочников отделений в нескольких странах:
```php
// import.php
use NG\API\Warehouses\Service\Import;
use NG\API\Warehouses\Service\Subscriber;
use WebStone\PDO\Database;
use WebStone\Requests\Clients\ClientCurl;

// Конфигурация сервиса импорта
$config = [
    // ...
];

// Создаем экземпляр объекта `Import`
$import = new Import($config);

// 1-й импорт
// Выполняем импорт отделений в стране Польша(PL)
$import->execute([
    'country' => 'PL',
    // ...
    'partial'  => false,
    'old_remove' => false,
    'old_deactivate' => false,
]);

// 2-й импорт
// Выполняем импорт отделений в стране Украина(UA)
$import->execute([
    'country' => 'UA',
    // ...
    'partial'  => false,
    'old_remove' => false,
    'old_deactivate' => false,
]);

// Х-й импорт
// Выполняем импорт отделений в стране XXX(XX)
$import->execute([
    'country' => 'XX',
    // ...
    'partial'  => false,
    'old_remove' => true,
    'old_deactivate' => true,
]);
```
В данном случае будет последовательно выполняться импорт по параметрам/фильтрам, "новые" данные будут активированы по завершению импорта, "старые" данные будут удалены/деактивированы только по окончании последнего импорта.

Пояснение.\
Когда параметры/фильтры импорта невозможно комбинировать, выполняйте импорт последовательно(с разными настройками), при этом параметры `old_remove` и `old_deactivate` должны быть установлены как `false` во всех процессах, кроме последнего - в настройках последнего импорта выберите необходимое действие со старыми данными, в зависимости от вашей стратегии.

В процессе импорта, с целью идентификации записей как "новые" и "старые", используется сессионный ключ. Во время жизни экземпляра объекта `Import` сессионный ключ автоматически не изменяется(если вы умышленного этого не делаете). Таким образом, вы можете выполнять сколько угодно импортов, при этом только последний выполнит удаление/деактивацию "старых" данных.

Если вы хотите выполнять импорт(с разными параметрами/фильтрами) параллельно, создайте файл конфигурации сервиса и необходимое количество файлов параметров/фильтров:

```php
// import_config.php
$config = [
    // Настройки подключения к базе данных
    'db'         => [
        'class'       => Database::class,
        'connections' => [
            [
                'key'      => 'mysql',
                'dsn'      => 'mysql:host=localhost;dbname=test',
                'user'     => 'user',
                'password' => 'password',
                'options'  => [],
            ],
        ],
        'default'     => 'mysql', // соединение будет активировано автоматически
    ],
    // ...
    'session_key' => md5('KEY_'.date('Ymd')) // сессионный ключ; 
];
```
```php
// import_filters_1.php
$filters = [
    'country' => 'PL',
    'page' => 1,
    'ext' => '0',
    'partial'  => false,
    'old_remove' => true,
    'old_deactivate' => false,
]
```

Модифицируйте файл `import.php` так:
```php
// import.php
use NG\API\Warehouses\Service\Import;
use NG\API\Warehouses\Service\Subscriber;
use WebStone\PDO\Database;
use WebStone\Requests\Clients\ClientCurl;

if (php_sapi_name() != "cli") exit("I can't do the import");

// Получаем все аргументы командной строки
$args = $argv;

// Удаляем первый элемент массива, так как он содержит имя скрипта
array_shift($args);

// Создаем массив для хранения параметров
$params = [];

// Обрабатываем каждый аргумент
foreach ($args as $arg) {
    // Разделяем аргумент на ключ и значение
    list($key, $value) = explode('=', $arg);
    // Удаляем ведущий слэш из ключа
    $key = ltrim($key, '/');
    // Сохраняем параметр в массив
    $params[$key] = $value;
}

// Конфигурация сервиса импорта
if(isset($params['config'])){
    if(file_exists($config_file = $params['config'])){
        include_once $config_file;
    } else
        exit("Configuration file not found");
} else
    exit("Configuration file not specified");

// Параметры импорта
if(isset($params['filters'])){
    if(file_exists($filters_file = $params['config'])){
        include_once $filters_file;
    } else
        exit("Parameter file not found");
} else
    exit("Parameter file not specified");

// Создаем экземпляр объекта `Import`
$import = new Import($config);

// Выполняем импорт отделений в соответствии условиям/фильтрам
$import->execute($filters);
```

Выполняем вызов `import.php`, или настраиваем задание в кроне, таким образом:
```
php import.php /config=/usr/var/import_config.php /filters=/usr/var/import_filters_1.php
php import.php /config=/usr/var/import_config.php /filters=/usr/var/import_filters_2.php
...
php import.php /config=/usr/var/import_config.php /filters=/usr/var/import_filters_X.php
```
В данном случае будут выполняться параллельно несколько процессов импорта; конфигурация сервиса импорта для всех процессов одинакова(в файле `import_config.php`) и содержит один, и тот же, сессионный ключ(он создан на основе метки даты `md5('KEY_'.date('Ymd'))`, если вы выполняете импорт чаще, чем один раз в сутки - измените формат даты ),\
 т.е. все "новые" данные по окончании импорта будут иметь одно и то же значение в поле `_import_key`, все "старые" данные будут удалены(`'old_remove' => true`) по окончании импорта с параметрами в файле `import_filters_1.php`