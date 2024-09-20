<?php

declare(strict_types=1);
/**
 * This file is part of NG\API\Warehouses.
 *
 * (C) 2009-2024 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NG\API\Warehouses\Tests\Model;

use NG\API\Warehouses\Model\Warehouse;
use NG\API\Warehouses\Service\Installer;
use PHPUnit\Framework\TestCase;
use WebStone\PDO\Database;

class WarehouseTest extends TestCase
{
    protected Database $db;
    protected Warehouse $model;
    protected array $simple_data;

    protected function setUp(): void
    {
        $this->db = new Database();
        $this->db->addConnection('mysql', 'mysql:host=localhost;dbname=test', 'user', 'password');
        $this->db->selectConnection('mysql');

        $this->model       = new Warehouse($this->db);
        $this->simple_data =
            [
                "name"                   => "KRAKÓW 1",
                "name_pl"                => "KRAKÓW 1",
                "reference"              => "5",
                "owner"                  => "NOVA POST POLAND SP ZОО",
                "owner_id"               => "176",
                "external_source"        => "NPAX",
                "services"               => [
                    "dropoff",
                    "delivery",
                ],
                "max_weight_kg"          => null,
                "max_parcel_weight_kg"   => "200",
                "send_dim_max_values"    => [
                    "length" => "300",
                    "width"  => "170",
                    "height" => "170",
                ],
                "receive_dim_max_values" => [
                    "length" => "300",
                    "width"  => "170",
                    "height" => "170",
                ],
                "wh_no"                  => "30/1",
                "type_info"              => "Parcel depot",
                "latitude"               => 50.0758964906,
                "longitude"              => 19.9416230983,
                "address"                => [
                    [
                        "country"      => "PL",
                        "zipcode"      => "30-001",
                        "lang"         => "PL",
                        "country_name" => "Polska",
                        "address"      => "30-001, Polska, Województwo małopolskie, Kraków County, Kraków, Kamienna, 19b",
                        "city"         => "Kraków",
                        "street"       => "Kamienna",
                        "building"     => "19b",
                    ],
                ],
            ];
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(Warehouse::class, $this->model);
    }

    public function testBeforeLoadWithValidData()
    {
        $data   = $this->simple_data;
        $result = $this->model->beforeLoad($data);

        $this->assertTrue($result);
        $this->assertArrayHasKey('address_address', $data);
        $this->assertArrayHasKey('address_building', $data);
        $this->assertArrayHasKey('address_city', $data);
        $this->assertArrayHasKey('address_country', $data);
        $this->assertArrayHasKey('address_country_name', $data);
        $this->assertArrayHasKey('address_lang', $data);
        $this->assertArrayHasKey('address_street', $data);
        $this->assertArrayHasKey('address_zipcode', $data);
    }

    public function testBeforeLoadWithInvalidData()
    {
        $data = [
            'invalid_key' => 'invalid_value',
            'wh_id'       => 1,
            'wh_no'       => 'WH001',
        ];
        $result = $this->model->beforeLoad($data);

        $this->assertTrue($result);
        $this->assertArrayNotHasKey('invalid_key', $data);
        $this->assertArrayHasKey('wh_id', $data);
        $this->assertArrayHasKey('wh_no', $data);
    }

    public function testSchemaValidation()
    {
        $data              = $this->simple_data;
        $data['extra_key'] = 'extra_value';
        $result            = $this->model->beforeLoad($data);

        $this->assertTrue($result);
        $this->assertArrayNotHasKey('extra_key', $data);
    }

    public function testInsertAndUpdate()
    {
        $installer = new Installer();
        $installer->setDb($this->db);
        $installer->install();

        $data = $this->simple_data;
        if ($this->model->load($data)) {
            $this->assertTrue((bool)$this->model->update());
        }

        if($this->model->findByCondition(['reference' => 5],true)){
            $this->model->wh_no = '123/456';
            $this->assertTrue((bool)$this->model->update());
        }

        if($this->model->findByCondition(['wh_no' => '123/456'],true)){
            $this->assertEquals(5,$this->model->reference);
        }

        $installer->uninstall();
    }
}
