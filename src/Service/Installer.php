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

namespace NG\API\Warehouses\Service;

use NG\API\Warehouses\Model\FieldDefinitionInterface;
use NG\API\Warehouses\Model\LayoutWarehouse;
use WebStone\PDO\Builder\ColumnBuilder;
use WebStone\PDO\Database;
use WebStone\Stdlib\Classes\AutoInitialized;

/**
 * Installer
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 21.08.2024 10:31:00
 */
class Installer
{
    protected ?Database $db = null;
    public function __construct(mixed $db = null)
    {
        if ($db !== null) {
            $this->setDb($db);
        }
    }

    /**
     * Get the value of db
     */
    public function getDb(): Database
    {
        if ($this->db === null) {
            $this->setDb(new Database());
        }

        return $this->db;
    }

    /**
     * Set the value of db
     *
     * @return  self
     */
    public function setDb(mixed $db): self
    {
        if(!is_object($db)){
            $db = AutoInitialized::turnInto($db);
        }

        if(!$db instanceof Database){
            throw new \InvalidArgumentException('Invalid database object');
        }

        $this->db = $db;
        return $this;
    }

    public function install(): void
    {
        if ($forge = $this->getDb()->getSchemaBuilder()) {
            /** warehouses table */
            if (!$forge->existsTable(LayoutWarehouse::getTableName())) {
                $columns = [];
                foreach (LayoutWarehouse::getFields() as $key => $definition) {
                    /** @var ColumnBuilder $column */
                    $columnFunction = 'column' . ucfirst($definition['type']);
                    $column = $forge->$columnFunction($definition['length'] ?? null);
                    $column->name($key);
                    $column->notNull($definition['null'] ?? false);
                    if ($definition['default'] !== null) {
                        $quoted = $definition['quoted'] ?? !is_numeric($definition['default']);
                        $column->defaultValue($definition['default'], $quoted);
                    }
                    $columns[] = $column;
                }
                $forge->addColumn($columns)->createTable(LayoutWarehouse::getTableName());
            }
        }
    }

    public function uninstall(): void
    {
        if ($forge = $this->getDb()->getSchemaBuilder()) {
            /** warehouses table */
            $forge->dropTable(LayoutWarehouse::getTableName(), true);
        }
    }
}
/** End of Installer **/
