## Setup
Создание таблицы для хранения справочника отделений

```php
use NG\API\Warehouses\Service\Installer;
use WebStone\PDO\Database;

// Создаем подключение к базе данных (к примеру, MySQL сервера)
// Параметры метода addConnection():
//    string $key The key to identify the connection.
//    string $dsn The Data Source Name (DSN) for the connection.
//    string|null $username The username for the connection (optional).
//    string|null $password The password for the connection (optional).
//    array $options Additional options for the connection (optional).

$db = new Database();
$db->addConnection('mysql', 'mysql:host=localhost;dbname=test', 'user', 'password');
$db->selectConnection('mysql');

// Создаем экземпляр объекта `Installer` и выполняем установку
$installer = new Installer($db);
$installer->install();
```
Важно! В версии 1.0.* `microgears/webstone-pdo` поддерживается только MySQL-драйвер (критично для `SchemaBuilder`,`ColumnBuilder`)

После установки и настройки необходимо получить __логин и пароль/ключ API__ на сайте NPG в личном кабинете. Подробности [здесь](<http://localhost.com>).