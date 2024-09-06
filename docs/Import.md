# Import
To effectively use the warehouses directory, it is necessary to keep it up to date.
Depending on which strategy you choose for using the directory data, use one of the following import methods:

## Method 1
If your order 'cart', in the delivery section, needs to store a reference to branch data (e.g., using the reference field), and you need to provide an order tracking service later, it's crucial that warehouse data is accessible during the import/update of the reference data (estimated to take 1-3 minutes, depending on the volume).

```php
// import.php
use NG\API\Warehouses\Service\Import;
use NG\API\Warehouses\Service\Subscriber;
use WebStone\PDO\Database;
use WebStone\Requests\Clients\ClientCurl;

// Import service configuration
$config = [
    // Database connection settings
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
        'default'     => 'mysql', // the connection will be activated automatically
    ],
    // API client settings
    'subscriber' => [
        'class'    => Subscriber::class,
        'client'   => [
            'class'            => ClientCurl::class,
            // Web client authorization details are available in your personal account on the NPG website
            'auth_type'        => 'basic',
            'username'         => 'username',
            'password'         => 'password',

            'protocol_version' => '1.1',
            'redirects_count'  => 5,
            'timeout'          => 10,
            'blocking'         => false,
        ],
        // Details for accessing the branch reference service are available in your personal account on the NPG website
        'host'     => 'api.novaposhta.international',
        'port'     => '8243',
        'protocol' => 'https',
    ],
    // Whether to log the process or not (see below)
    'logging' => false
];

// Create an instance of the `Import` object and perform the import/update
$import = new Import($config);
$import->execute([
    // two-letter country identifier
    'country' => 'PL',
    
    // from which page of the directory to start importing
    'page' => 1,

    // whether to return(1) or not(0) extended information about the branch; not used
    'ext' => '0',

    // additional filters (type, owner, etc.) for importing warehouses
    // details are available in your personal account on the NPG website
    // ...
    
    // partial/full import
    // set true to import only one specific directory page
    // set false to import the entire directory
    'partial'  => false,
    
    // if import is successful, delete `old` data
    'old_remove' => false,
    
    // if import is successful, deactivate `old` data
    'old_deactivate' => true,
]);
```
Explanation:\
In this case, all warehouses for the country Poland(PL) will be imported and upon successful completion of the import process, the **old** data will be deactivated(`_active = 0`)

## Method 2
If your order "cart", in the delivery section, should store (copy details) of the warehouse data, and in the case of implementing the order tracking service, the current state of the directory is not important.
   
```php
// import.php
use NG\API\Warehouses\Service\Import;
use NG\API\Warehouses\Service\Subscriber;
use WebStone\PDO\Database;
use WebStone\Requests\Clients\ClientCurl;

// Import service configuration
$config = [
    // ...
];

// Create an instance of the `Import` object and perform the import/update
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
Explanation:\
In this case, all warehouses for the country Poland(PL) will be imported and if the import process is successfully completed, the **old** data will be deleted.

## Method 3
If you want to import warehouses directories in several countries:
```php
// import.php
use NG\API\Warehouses\Service\Import;
use NG\API\Warehouses\Service\Subscriber;
use WebStone\PDO\Database;
use WebStone\Requests\Clients\ClientCurl;

// Import service configuration
$config = [
    // ...
];

// Create an instance of the `Import` object
$import = new Import($config);

// 1st import
// We carry out import of warehouses in the country Poland(PL)
$import->execute([
    'country' => 'PL',
    // ...
    'partial'  => false,
    'old_remove' => false,
    'old_deactivate' => false,
]);


// 2st import
// We carry out import of warehouses in the country Ukraine(UA)
$import->execute([
    'country' => 'UA',
    // ...
    'partial'  => false,
    'old_remove' => false,
    'old_deactivate' => false,
]);

// Other imports
// ...

// Nst import
// We carry out import of warehouses in the country XXXXXX(YY)
$import->execute([
    'country' => 'YY',
    // ...
    'partial'  => false,
    'old_remove' => true,
    'old_deactivate' => true,
]);
```
Explanation:\
In this case, the import will be performed sequentially by parameters/filters, the "new" data will be activated upon completion of the import, the "old" data will be deleted/deactivated only upon completion of the last import.

When import parameters/filters cannot be combined, perform imports sequentially (with different settings), with `old_remove` and `old_deactivate` parameters set to `false` in all processes except the last one - in the settings of the last import, select the necessary action with old data, depending on your strategy.

During the import process, a session key is used to identify records as "new" and "old". During the lifetime of an `Import` object instance, the session key does not change automatically (unless you do so intentionally). Thus, you can perform as many imports as you like, but only the last one will perform the deletion/deactivation of the "old" data.

## Method 3
If you want to perform imports (with different parameters/filters) in parallel, create a service configuration file and the required number of parameter/filter files:

```php
// import_config.php
$config = [
    // Database connection settings
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
        'default'     => 'mysql',
    ],
    // ...
    'session_key' => md5('KEY_'.date('Ymd')) // assign a session key; 
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

Modify the `import.php` file as follows:
```php
// import.php
use NG\API\Warehouses\Service\Import;
use NG\API\Warehouses\Service\Subscriber;
use WebStone\PDO\Database;
use WebStone\Requests\Clients\ClientCurl;

if (php_sapi_name() != "cli") exit("I can't do the import");

// Get all command line arguments
$args = $argv;

// Remove the first element of the array since it contains the script name
array_shift($args);

// Create an array to store parameters
$params = [];

// Process each argument
foreach ($args as $arg) {
    // Split the argument into key and value
    list($key, $value) = explode('=', $arg);
    // Remove the leading slash from the key
    $key = ltrim($key, '/');
    // Save the parameter to an array
    $params[$key] = $value;
}

// Import service configuration
if(isset($params['config'])){
    if(file_exists($config_file = $params['config'])){
        include_once $config_file;
    } else
        exit("Configuration file not found");
} else
    exit("Configuration file not specified");

// Import parameters
if(isset($params['filters'])){
    if(file_exists($filters_file = $params['config'])){
        include_once $filters_file;
    } else
        exit("Parameter file not found");
} else
    exit("Parameter file not specified");

// Create an instance of the `Import` object
$import = new Import($config);

// Perform import of departments according to conditions/filters
$import->execute($filters);
```

We call `import.php`, or set up a task in the cron, like this:
```
php import.php /config=/usr/var/import_config.php /filters=/usr/var/import_filters_1.php
php import.php /config=/usr/var/import_config.php /filters=/usr/var/import_filters_2.php
...
php import.php /config=/usr/var/import_config.php /filters=/usr/var/import_filters_X.php
```
Explanation:\
In this case, several import processes will be executed in parallel; the import service configuration is the same for all processes (in the `import_config.php` file) and contains the same session key (it is created based on the date stamp `md5('KEY_'.date('Ymd'))`, if you perform import more often than once a day - change the date format), i.e. all "new" data at the end of the import will have the same value in the `_import_key` field, all "old" data will be deleted (`'old_remove' => true`) at the end of the import with the parameters in the `import_filters_1.php` file.

## Logging

The import service supports process logging, for this the [Chronolog](https://github.com/microGears/chronolog) library is used. \
Modify the `import.php` file as follows:
```php
// import.php
use Chronolog\LogBook;
use Chronolog\LogBookShelf;
use Chronolog\Scriber\FileScriber;
use Chronolog\Scriber\Renderer\StringRenderer;
use Chronolog\Severity;

$config_log = [
    'class'   => LogBook::class,
    'enabled' => true,
    'track'   => 'import',
    'scribes' => [
        [
            'class'             => FileScriber::class,
            'severity'          => Severity::Debug,
            'renderer'          => [
                'class'           => StringRenderer::class,
                'pattern'         => "%datetime%~%severity_name% %message% %assets%",
                'format'          => 'Y-m-d\TH:i:s.vP',
                'allow_multiline' => true,
                'include_traces'  => true,
                'base_path'       => __DIR__,
            ],
            'path'              => '/var/log/',
            'basename'          => 'import',
            'size_threshold'    => 1024 * 1000,
            'max_files'         => 7,
            'write_immediately' => false,
            'collaborative'     => true,
        ],
    ],
];

if ($logging = ($log = AutoInitialized::turnInto($config_log)) instanceof LogBook) {
    LogBookShelf::put($log, true);
}

if (php_sapi_name() != "cli") exit("I can't do the import");
...
```

Modify the `import_config.php` file as follows:
```php
// import_config.php
$config = [
    // other params
    // ...

    'logging' => $logging
];
```

When importing, log files (like `import_*.log`) will be created in the `/var/log/` folder, with contents similar to the following:
```
2024-09-03T11:41:50.216+00:00~DEBUG Start import, key #149113329160db2479180aa650d2c1a3 
2024-09-03T11:41:50.217+00:00~DEBUG Requesting page 1 {"params":{"country":"XX","page":1,"ext":"0"}}
2024-09-03T11:41:51.791+00:00~DEBUG Received page 1 from 57, 1000 items 
2024-09-03T11:41:53.202+00:00~DEBUG Requesting page 2 {"params":{"country":"XX","page":2,"ext":"0"}}
2024-09-03T11:41:54.756+00:00~DEBUG Received page 2 from 57, 1000 items
...
2024-09-03T11:45:10.290+00:00~DEBUG Received page 57 from 57, 100 items 
2024-09-03T11:45:10.734+00:00~DEBUG Finish import 
```