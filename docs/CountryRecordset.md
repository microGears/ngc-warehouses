# CountryRecordset

CountryRecordset - is designed to get a list of countries, warehouses in which are available for delivery. \
You can get a list of countries like this:
```php
use NG\API\Warehouses\Recordset\CountryRecordset;
use WebStone\PDO\Database;

$db = new Database();
$db->addConnection('mysql', 'mysql:host=localhost;dbname=test', 'user', 'password');
$db->selectConnection('mysql');

$recordset = new CountryRecordset($db);
$recordset
// ->setCountry($_GET['country']) // to filter by destination country
->setPageIndex(1)
->setPageSize(25)  // use "0" to get the whole list
->fetchRows();

// Render the HTML SELECT component
$recordset->fetchRows();
echo "<select name=\"country\">";
while($record = $recordset->fetchRow()) {
    echo "<option value=\"{$record['address_country']}\">{$record['address_country_name']}</option>";
}
echo "</select>";

// ...

// Send to frontend
// Set Content-Type header to application/json
header('Content-Type: application/json');
// Convert array to JSON string and output JSON string
echo json_encode($recordset->getRows());
```

After calling `$recordset->fetchRows();` the `CountryRecordset` instance will contain an array of records (arrays, by default) of the following structure:
```
[
    [
        'address_country' => 'PL',
        'address_country_name' => 'Polska',
    ],
    ...
]
```
The value of the `address_country` property can be used in the department filter in `WarehouseRecordset`.

To get the number of records in the current selection, use `$recordset->getRowsCount()`.\
To get the total number of records, excluding page size (pagination), use `$recordset->getRowsTotalCount()`.


