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
 * CityRecordset
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 03.09.2024 10:36:00
 */
class CityRecordset extends SearchRecordsetAbstract
{
    protected ?string $country = null;
    protected ?string $city    = null;
    protected int $rowsTotal   = 0;

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

        return $this;
    }
    public function getQuery(): string
    {
        $result = '';
        if ($query = $this->getSearchBuilder()) {
            $query
                ->prepare(true)
                ->modifiers('SQL_CALC_FOUND_ROWS') //only for MySQL, for another PDO driver you should use another method calculate found rows
                ->select("address_country, address_country_name, address_city")
                ->from(LayoutWarehouse::getTableName())
                ->groupBy('address_country, address_country_name, address_city');

            if ($country = $this->getCountry()) {
                $query->whereBrackets();
                $query->like('address_country', $country, 'none');
                $query->orLike('address_country_name', $country, 'after');
                $query->whereBracketsEnd();
            }

            if ($city = $this->getCity()) {
                $query->like('address_city', $city, 'after');
            }
            
            $query->where('_active', 1);
            $query->limit($this->getPageSize(), $this->getPageSize() * ($this->getPageIndex() - 1));
            $query->orderBy('address_country, address_city');

            $result = $query->rows();
        }

        return $result;
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

    public function getRowsTotalCount()
    {
        return $this->rowsTotal;
    }
}
/** End of CityRecordset **/
