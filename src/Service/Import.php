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

use Chronolog\DateTimeStatement;
use Chronolog\LogBookShelf;
use Chronolog\Severity;
use NG\API\Warehouses\Model\LayoutWarehouse;
use NG\API\Warehouses\Model\Warehouse;
use WebStone\PDO\Database;
use WebStone\Stdlib\Classes\AutoInitialized;
use WebStone\Stdlib\Helpers\ArrayHelper;

/**
 * Import
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 23.08.2024 13:24:00
 */
class Import extends AutoInitialized
{
    protected ?Database $db           = null;
    protected ?Subscriber $subscriber = null;
    protected string $logID           = 'import';
    protected bool $logging           = false;
    protected string $session_key     = '';

    /**
     * Executes the import process.
     *
     * @param array $params The parameters for the import process.
     * @return void
     */
    public function execute(array $params = []): void
    {
        $this->logMsg(sprintf('Start import, key #%s', $this->getSessionKey()), Severity::Debug);
        try {
            $import_params = ['old_remove' => false, 'old_deactivate' => true, 'partial' => false];
            
            $intersect_keys = array_intersect_key($params, $import_params);
            foreach ($intersect_keys as $key => $value) {
                $import_params[$key] = $value;
            }

            $self_params_keys = array_keys($import_params);
            $params           = array_diff_key($params, array_flip($self_params_keys));

            $page    = ArrayHelper::element('page', $params, 1);
            $pages   = ArrayHelper::element('pages', $params, 1);
            $partial = ArrayHelper::element('partial', $import_params, false);

            do {
                $this->logMsg(sprintf("Requesting page %d", ArrayHelper::element('page', $params, 1)), Severity::Debug, ['params' => $params]);
                if ($response = $this->getSubscriber()->getWarehouses($params)) {
                    if ($response->getStatusCode() == 200) {

                        $warehouses = ArrayHelper::element('warehouse_list', $response->getContents(), []);
                        $this->logMsg(sprintf("Received page %d from %d, %d items", $response->getContentsItem('page', 1), $response->getContentsItem('pages', 1), count($warehouses)), Severity::Debug);
                        foreach ($warehouses as $warehouse) {
                            /** insert */
                            $model = new Warehouse($this->getDb());
                            try {
                                if ($model->load($warehouse)) {
                                    $model->_import_key = $this->getSessionKey();
                                    $model->update();
                                }
                            } catch (\Exception $e) {
                                $this->logMsg($e->getMessage(), Severity::Error);
                                continue;
                            }
                        }

                        $page  = ArrayHelper::element('page', $response->getContents(), 1);
                        $pages = ArrayHelper::element('pages', $response->getContents(), 1);
                        if ($page < $pages) {
                            $params['page'] = $page + 1;
                        }
                    } else {
                        throw new \RuntimeException("Invalid response status code: {$response->getStatusCode()}");
                    }
                }
            } while ($page < $pages && !$partial);

            // Deactivate old records
            if (ArrayHelper::element('old_deactivate', $import_params, false)) {
                $this->getDb()->loadSql(sprintf("UPDATE `%s` SET `_active` = 0 WHERE `_import_key` != '%s'", LayoutWarehouse::getTableName(), $this->getSessionKey()))->execute();
            }

            // Delete old records
            if (ArrayHelper::element('old_remove', $import_params, false)) {
                $this->getDb()->loadSql(sprintf("DELETE FROM `%s` WHERE `_import_key` != '%s'", LayoutWarehouse::getTableName(), $this->getSessionKey()))->execute();
            }

            // Activate new entries
            $this->getDb()->loadSql(sprintf("UPDATE `%s` SET `_active` = 1 WHERE `_import_key` = '%s'", LayoutWarehouse::getTableName(), $this->getSessionKey()))->execute();
        } catch (\Exception $e) {
            $this->logMsg($e->getMessage(), Severity::Error);
            $this->getDb()->loadSql(sprintf("DELETE FROM `%s` WHERE `_import_key` = '%s'", LayoutWarehouse::getTableName(), $this->getSessionKey()))->execute();
        }
        $this->logMsg('Finish import', Severity::Debug);
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
        if (!is_object($db)) {
            $db = AutoInitialized::turnInto($db);
        }

        if (!$db instanceof Database) {
            throw new \InvalidArgumentException('Invalid database object');
        }
        $this->db = $db;
        return $this;
    }

    /**
     * Get the value of subscriber
     */
    public function getSubscriber(): Subscriber
    {
        if ($this->subscriber === null) {
            $this->setSubscriber(new Subscriber());
        }

        return $this->subscriber;
    }

    /**
     * Set the value of subscriber
     *
     * @return  self
     */
    public function setSubscriber(mixed $subscriber)
    {
        if (!is_object($subscriber)) {
            $subscriber = AutoInitialized::turnInto($subscriber);
        }

        if (!$subscriber instanceof Subscriber) {
            throw new \InvalidArgumentException('Invalid subscriber object');
        }

        $this->subscriber = $subscriber;

        return $this;
    }

    public function getLogID(): string
    {
        return $this->logID;
    }

    public function setLogID(string $logID): self
    {
        $this->logID = $logID;
        return $this;
    }
    public function logMsg(string $message, int | Severity $severity = Severity::Debug, array $assets = [], null | DateTimeStatement $datetime = null): bool
    {
        if (LogBookShelf::has($id = $this->getLogID())) {
            $logger = LogBookShelf::get($id);
            return $logger->log($severity, $message, $assets, $datetime);
        }

        return false;
    }

    /**
     * @return boolean
     */
    public function getLogging()
    {
        return $this->logging;
    }

    /**
     * @param $logging
     *
     * @return $this
     */
    public function setLogging($logging)
    {
        $this->logging = $logging;

        return $this;
    }

    /**
     * Get the value of session
     */
    public function getSessionKey()
    {
        if (empty($this->session_key)) {
            $this->setSessionKey(md5(uniqid()));
        }

        return $this->session_key;
    }

    /**
     * Set the value of session, must be unique
     *
     * @return  self
     */
    public function setSessionKey(string $key)
    {
        $this->session_key = $key;

        return $this;
    }
}
/** End of Import **/
