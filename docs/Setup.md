# Setup
Creating a table to store a directory of warehouses

```php
use NG\API\Warehouses\Service\Installer;
use WebStone\PDO\Database;

// Create a connection to the database (for example, MySQL server)
// Parameters of the addConnection() method:
//    string $key The key to identify the connection.
//    string $dsn The Data Source Name (DSN) for the connection.
//    string|null $username The username for the connection (optional).
//    string|null $password The password for the connection (optional).
//    array $options Additional options for the connection (optional).

$db = new Database();
$db->addConnection('mysql', 'mysql:host=localhost;dbname=test', 'user', 'password');
$db->selectConnection('mysql');

// Create an instance of the `Installer` object and perform the installation
$installer = new Installer($db);
$installer->install();
```
Note!\
In version 1.0.* of `microgears/webstone-pdo` only MySQL driver is supported (critical for `SchemaBuilder`,`ColumnBuilder`)

After setup, you need to get __login and password/API key__ on the NPG website in your personal account. Details [here](<http://localhost.com>).