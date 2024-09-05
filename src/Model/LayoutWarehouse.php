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

namespace NG\API\Warehouses\Model;

use NG\API\Warehouses\Model\Adapter\MySQL\Warehouse;

/**
 * LayoutWarehouse
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 23.08.2024 12:19:00
 */
class LayoutWarehouse implements LayoutInterface
{
    public static function getFields(): array
    {
        return [
            'wh_id'                => [
                'type'    => 'primarykey',
                'length'  => 11,
                'null'    => false,
                'default' => null,
            ],
            'wh_no'                => [
                'type'    => 'string',
                'length'  => 96,
                'null'    => false,
                'default' => null,
            ],

            /** address section will be denormalized from the first language section  */
            'address_address'      => [
                'type'    => 'string',
                'length'  => 256,
                'null'    => false,
                'default' => null,
            ],
            'address_building'     => [
                'type'    => 'string',
                'length'  => 16,
                'null'    => false,
                'default' => null,
            ],
            'address_city'         => [
                'type'    => 'string',
                'length'  => 128,
                'null'    => false,
                'default' => null,
            ],
            'address_country'      => [
                'type'    => 'string',
                'length'  => 2,
                'null'    => false,
                'default' => null,
            ],
            'address_country_name' => [
                'type'    => 'string',
                'length'  => 128,
                'null'    => false,
                'default' => null,
            ],
            'address_lang'         => [
                'type'    => 'string',
                'length'  => 2,
                'null'    => false,
                'default' => null,
            ],
            'address_street'       => [
                'type'    => 'string',
                'length'  => 128,
                'null'    => false,
                'default' => null,
            ],
            'address_zipcode'      => [
                'type'    => 'string',
                'length'  => 32,
                'null'    => false,
                'default' => null,
            ],

            /** location */
            'latitude'             => [
                'type'    => 'decimal',
                'length'  => 14.12,
                'null'    => false,
                'default' => 0.00,
            ],
            'longitude'            => [
                'type'    => 'decimal',
                'length'  => 14.12,
                'null'    => false,
                'default' => 0.00,
            ],

            /** warehouse info */
            'name'                 => [
                'type'    => 'string',
                'length'  => 64,
                'null'    => false,
                'default' => null,
            ],
            'owner_id'             => [
                'type'    => 'string',
                'length'  => 32,
                'null'    => false,
                'default' => null,
            ],
            'owner'                => [
                'type'    => 'string',
                'length'  => 64,
                'null'    => false,
                'default' => null,
            ],

            /** unique key of warehouse */
            'reference'            => [
                'type'    => 'string',
                'length'  => 128,
                'null'    => false,
                'default' => null,
            ],

            'services'             => [
                'type'    => 'string',
                'length'  => 32,
                'null'    => false,
                'default' => null,
            ],
            'type_info'            => [
                'type'    => 'string',
                'length'  => 64,
                'null'    => false,
                'default' => null,
            ],
            '_active'              => [
                'type'    => 'smallint',
                'length'  => 1,
                'null'    => false,
                'default' => 0,
            ],
            '_import_key'          => [
                'type'    => 'string',
                'length'  => 32,
                'null'    => true,
                'default' => '',
            ],
        ];
    }
    public static function getIndexes(): array
    {
        return [];
    }

    public static function getTableName(): string
    {
        return 'ngc_warehouses';
    }

    public static function getPrimaryKey(): string
    {
        return 'wh_id';
    }
}
/** End of LayoutWarehouse **/
