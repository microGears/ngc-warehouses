# CityRecordset

CityRecordset - is designed to obtain a list of settlements, warehouses of which are available for delivery. \
You can obtain a list of settlements like this:
```php
use NG\API\Warehouses\Recordset\CityRecordset;
use WebStone\PDO\Database;

$db = new Database();
$db->addConnection('mysql', 'mysql:host=localhost;dbname=test', 'user', 'password');
$db->selectConnection('mysql');

$recordset = new CityRecordset($db);
$recordset
->setCountry($_GET['country']) // to filter by destination country
->setCity($_GET['city']) // to filter by destination country locality
->setPageIndex(1)
->setPageSize(25) // use "0" to get the whole list
->fetchRows();

// Render the HTML SELECT component
echo "<select name=\"city\">";
while($record = $recordset->fetchRow()) {
    echo "<option value=\"{$record['address_city']}\">{$record['address_city']}</option>";
}
echo "</select>";

// ...

// Send to frontend
// Set Content-Type header to application/json
header('Content-Type: application/json');
// Convert array to JSON string and output JSON string
echo json_encode($recordset->getRows());
```

After calling `$recordset->fetchRows();` the `CityRecordset` instance will contain an array of records (arrays, by default) of the following structure:
```
[
    [
        'address_city' => 'Częstochowa',
        'address_country' => 'PL',
        'address_country_name' => 'Polska',
    ],
    ...
]
```
The value of the `address_city` property can be used in the department filter in `WarehouseRecordset`.

To get the number of records in the current selection, use `$recordset->getRowsCount()`.\
To get the total number of records, excluding page size (pagination), use `$recordset->getRowsTotalCount()`.
