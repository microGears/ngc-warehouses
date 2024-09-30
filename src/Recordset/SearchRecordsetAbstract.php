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

use Chronolog\Helper\ArrayHelper;
use NG\API\Warehouses\Model\FieldDefinition;
use WebStone\PDO\Builder\Query;
use WebStone\PDO\Builder\QueryBuilderInterface;
use WebStone\PDO\Database;
use WebStone\PDO\RecordsetAbstract;

/**
 * CustomRecordset
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 27.08.2024 13:51:00
 */
abstract class SearchRecordsetAbstract extends RecordsetAbstract
{
    protected ?QueryBuilderInterface $searchBuilder = null;

    public function __construct(Database $db)
    {
        $this->setDb($db);
        $this->setSearchBuilder($this->getDb()->getQueryBuilder());
    }

    public function getSearchBuilder(): QueryBuilderInterface
    {
        if ($this->searchBuilder === null) {
            $this->setSearchBuilder($this->getDb()->getQueryBuilder());
        }

        return $this->searchBuilder;
    }

    public function setSearchBuilder(QueryBuilderInterface $builder): self
    {
        $this->searchBuilder = $builder;
        return $this;
    }
}
/** End of CustomRecordset **/
