<?php

declare(strict_types=1);

namespace NG\API\Warehouses\Tests\Service;

use NG\API\Warehouses\Service\Installer;
use NG\API\Warehouses\Model\Warehouse;
use PHPUnit\Framework\TestCase;
use WebStone\PDO\Database;

class InstallerTest extends TestCase
{
    protected Installer $installer;
    protected Database $db;

    protected function setUp(): void
    {
        $this->db = new Database();
        $this->db->addConnection('mysql', 'mysql:host=localhost;dbname=test', 'user', 'password');
        $this->db->selectConnection('mysql');

        $this->installer = new Installer($this->db);
    }

    public function testInstall(): void
    {
        $this->installer->install();
    }

    public function testUninstall(): void
    {
        $this->installer->uninstall();
    }
}