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

namespace NG\API\Warehouses\Recordset;

use NG\API\Warehouses\Model\LayoutWarehouse;

/**
 * WarehouseRecordset
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 03.09.2024 12:44:00
 */
class WarehouseRecordset extends SearchRecordsetAbstract
{
    protected int $rowsTotal   = 0;
    protected ?string $country = null;
    protected ?string $city    = null;
    protected ?string $zipcode = null;

    public function fetchRows(array $args = []): self
    {
        parent::fetchRows($args);
        $this->rowsTotal = 0;

        // only for MySQL, for another PDO driver you should use another method calculate found rows
        if ($query = $this->getDb()->getQueryBuilder()) {
            if ($row = $query->select('FOUND_ROWS() as rows_count')->row()) {
                $this->rowsTotal = (int) $row['rows_count'];
            }
        }

        $this->country = null;
        $this->city    = null;
        $this->zipcode = null;

        return $this;
    }

    public function fetchRowsByPoint(float $lat, float $lon, float $max_distance = 10000): self
    {
        // save original page size
        if ($page_size = $this->getPageSize()) {
            $this->setPageSize(0);
        }

        // get rows
        $this->fetchRows([]);
        echo "Total rows: " . count($this->getRows()) . "\n";

        $result = [];
        $rows = $this->getRows();

        // back to original page size
        if ($page_size) {
            $this->setPageSize($page_size);
        }

        if (count($rows)) {
            foreach ($rows as $row) {
                $distance = $this->haversineDistance($lat, $lon, (float)$row['latitude'], (float)$row['longitude']);
                if ($distance <= $max_distance) {
                    $row['_distance'] = $distance;
                    $result[] = $row;
                }
            }

            // sort by distance
            if (count($result)) {
                usort($result, fn($a, $b) => $a['_distance'] <=> $b['_distance']);
            }

            // apply page size
            if($page_size){
                $result = array_slice($result, $this->getPageSize() * ($this->getPageIndex() - 1), $this->getPageSize());
            }
        }

        // override rows
        $this->setRows($result);
        echo "Total rows(after): " . count($this->getRows()) . "\n";
        return $this;
    }

    // Функція для обчислення відстані між двома точками за формулою Гарсина
    protected function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // радіус Землі в метрах
        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLon = deg2rad($lon2 - $lon1);
        $a = sin($deltaLat / 2) ** 2 + cos($lat1Rad) * cos($lat2Rad) * sin($deltaLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c; // Відстань в метрах
    }


    public function getQuery(): string
    {
        $result = '';
        if ($query = $this->getSearchBuilder()) {
            $query
                ->prepare(true)
                ->modifiers('SQL_CALC_FOUND_ROWS') //only for MySQL, for another PDO driver you should use another method calculate found rows
                ->select("*")
                ->from(LayoutWarehouse::getTableName());

            if ($country = $this->getCountry()) {
                $query->whereBrackets();
                $query->like('address_country', $country, 'none');
                $query->orLike('address_country_name', $country, 'after');
                $query->whereBracketsEnd();
            }

            if ($city = $this->getCity()) {
                $query->like('address_city', $city, 'after');
            }

            if ($zipcode = $this->getZipcode()) {
                $query->like('address_zipcode', $zipcode, 'after');
            }

            $query->where('_active', 1);

            $query->limit($this->getPageSize(), $this->getPageSize() * ($this->getPageIndex() - 1));
            // $query->orderBy('address_country, address_city');

            $result = $query->rows();
        }
        return $result;
    }

    public function getRowsTotalCount()
    {
        return $this->rowsTotal;
    }
    /**
     * Get the value of country
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * Set the value of country
     *
     * @return  self
     */
    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get the value of city
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * Set the value of city
     *
     * @return  self
     */
    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get the value of zipcode
     */
    public function getZipcode(): ?string
    {
        return $this->zipcode;
    }

    /**
     * Set the value of zipcode
     *
     * @return  self
     */
    public function setZipcode(string $zipcode): self
    {
        $this->zipcode = $zipcode;

        return $this;
    }
}
/** End of WarehouseRecordset **/
