<?php

declare (strict_types = 1);
/**
 * This file is part of NG\API\Warehouses.
 *
 * (C) 2009-2024 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NG\API\Warehouses\Model;

use Chronolog\Helper\ArrayHelper;
use WebStone\PDO\Database;
use WebStone\PDO\ModelAbstract;

/**
 * Warehouse
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 21.08.2024 08:53:00
 *
 * @property int    $wh_id
 * @property string $wh_no
 * @property string $address_address
 * @property string $address_building
 * @property string $address_city
 * @property string $address_country
 * @property string $address_country_name
 * @property string $address_lang
 * @property string $address_street
 * @property string $address_zipcode
 * @property float  $latitude
 * @property float  $longitude
 * @property string $name
 * @property string $owner_id
 * @property string $owner
 * @property string $reference
 * @property string $services
 * @property string $type_info
 * @property int    $_active
 * @property string $_import_key
 */
final class Warehouse extends ModelAbstract {
    public function __construct(Database $db) {
        $this->setDb($db);
        parent::__construct(LayoutWarehouse::getTableName(), LayoutWarehouse::getPrimaryKey());
    }

    public function beforeLoad(array &$data = null): bool {
        if ($data !== null) {
            /** denormalize data */
            if (!isset($data[$this->getPrimaryKey()])) {
                /** transform address */
                if (isset($data['address']) && is_array($data['address']) && count($data['address']) > 0) {
                    /** fetch only first object of address  */
                    foreach ($data['address'][0] as $key => $value) {
                        if (!array_key_exists($_key = "address_$key", LayoutWarehouse::getFields())) {
                            continue;
                        }

                        $data[$_key] = $value;
                    }
                    unset($data['address']);
                }

                /** transform services */
                if (isset($data['services']) && is_array($data['services'])) {
                    $data['services'] = implode(',', $data['services']);
                }

                /** transform schedule */
                if (isset($data['schedule']) && is_array($data['schedule'])) {
                    $schedule = [];
                    foreach ($data['schedule'] as $timetable) {
                        if (is_array($timetable)) {
                            $day  = ucfirst(ArrayHelper::element('day_of_week', $timetable, 'Mon'));
                            $from = ArrayHelper::element('open_time', $timetable, '00:00');
                            $to   = ArrayHelper::element('close_time', $timetable, '00:00');

                            $schedule[] = "$day: $from-$to";
                        }
                    }
                    $data['schedule'] = implode(',', $schedule);
                }
            }

            /** delete all keys that are not in the scheme */
            foreach ($data as $key => $value) {
                if (!array_key_exists($key, LayoutWarehouse::getFields())) {
                    unset($data[$key]);
                }
            }

            return true;
        }

        return false;
    }
}
/** End of Warehouse **/
