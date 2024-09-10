# WarehouseRecordset

WarehouseRecordset - is designed to get a list of warehouses that are available for delivery. \
You can get a list of warehouses in several ways.

Method 1. \
Getting a list of warehouses by country and locality.
```php
use NG\API\Warehouses\Recordset\WarehouseRecordset;
use WebStone\PDO\Database;

$db = new Database();
$db->addConnection('mysql', 'mysql:host=localhost;dbname=test', 'user', 'password');
$db->selectConnection('mysql');

$recordset = new WarehouseRecordset($db);
$recordset
->setCountry($_GET['country']) // to filter by destination country
->setCity($_GET['city']) // to filter by destination country locality
// ->setZipcode($_GET['zipcode']) // to filter by zipcode
->setPageIndex(1)
->setPageSize(25)  // use "0" to get the whole list
->fetchRows();

// Render the HTML SELECT component
if($recordset->getRowsCount() > 0) {
echo "<select name=\"city\">";
while($record = $recordset->fetchRow()) {
    echo "<option value=\"{$record['wh_no']}\">{$record['address_address']}</option>";
}
echo "</select>";
}

// ...

// Send to frontend
// Set Content-Type header to application/json
header('Content-Type: application/json');
// Convert array to JSON string and output JSON string
echo json_encode($recordset->getRows());

```

The `$_GET['country']` and `$_GET['city']` values ​​can be pre-fetched using [CountryRecordset](./CountryRecordset.md) and [CityRecordset](./CityRecordset.md). \
After calling `$recordset->fetchRows();` the `WarehouseRecordset` instance will contain an array of records (arrays, by default) of the following structure:
```
[
    [
        'wh_id' => '71309',
        'wh_no' => 'PL62226',
        'address_address' => 'Szajnowicza Iwanowa 75/1',
        'address_building' => NULL,
        'address_city' => 'Częstochowa',
        'address_country' => 'PL',
        'address_country_name' => 'Polska',
        'address_lang' => 'PL',
        'address_street' => 'Szajnowicza Iwanowa 75/1',
        'address_zipcode' => '42218',
        'latitude' => '50.830820000000',
        'longitude' => '19.104780000000',
        'name' => 'PUDO',
        'owner_id' => '187',
        'owner' => 'DPD Poland',
        'reference' => 'PL62226',
        'services' => 'dropoff,delivery',
        'type_info' => 'Parcel Shop',
        '_active' => '1',
        '_import_key' => '149113329160db2479180aa650d2c1a3',
    ],
    ...
]
```

Method 2.\
Get a list of warehouses by coordinates.
```php
// ...

$recordset = new WarehouseRecordset($db);
$recordset
// ->setCountry($_GET['country'])
// ->setCity($_GET['city'])
// ->setZipcode($_GET['zipcode'])
->setPageIndex(1)
->setPageSize(25)  // use "0" to get the whole list
->fetchRowsByPoint($_GET['lat'], $_GET['lon'], $_GET['radius']);

$rows = $recordset->getRows();
```
As a result of calling `$recordset->fetchRowsByPoint(...)`, the structure of records in the array will be supplemented with the `_distance` attribute, which will contain the distance (in meters) to the warehouse, something like this:
```
[
    [
        'wh_id' => '71309',
        'wh_no' => 'PL62226',
        'address_address' => 'Szajnowicza Iwanowa 75/1',
        'address_building' => NULL,
        'address_city' => 'Częstochowa',
        'address_country' => 'PL',
        'address_country_name' => 'Polska',
        'address_lang' => 'PL',
        'address_street' => 'Szajnowicza Iwanowa 75/1',
        'address_zipcode' => '42218',
        'latitude' => '50.830820000000',
        'longitude' => '19.104780000000',
        'name' => 'PUDO',
        'owner_id' => '187',
        'owner' => 'DPD Poland',
        'reference' => 'PL62226',
        'services' => 'dropoff,delivery',
        'type_info' => 'Parcel Shop',
        '_active' => '1',
        '_import_key' => '149113329160db2479180aa650d2c1a3',
        
        // distance in meters relative to the transmitted coordinates
        '_distance' => 4929.28627239332,
    ],
    ...
]
```

Using the methods *setCountry(), setCity(), setZipcode()* significantly narrows the search area and speeds up the process.

Method 3.\
Custom parameters for searching warehouses.
```php
// ...

$recordset = new WarehouseRecordset($db);

// The built-in expression builder is used for parameterization
$recordset
->getSearchBuilder()
->like('address_address','some address','after')
->orLike('address_street','another address','after')
...
->where('reference','RF1234567890');
        
$recordset
->setPageIndex(1)
->setPageSize(25)  // use "0" to get the whole list
->fetchRows();
// or
// ->fetchRowsByPoint($_GET['lat'], $_GET['lon'], $_GET['radius']);

$rows = $recordset->getRows();
```