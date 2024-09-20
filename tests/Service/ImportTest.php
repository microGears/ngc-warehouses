<?php

declare(strict_types=1);

namespace NG\API\Warehouses\Tests\Service;

use Chronolog\LogBook;
use Chronolog\LogBookShelf;
use Chronolog\Scriber\FileScriber;
use Chronolog\Scriber\Renderer\StringRenderer;
use Chronolog\Severity;
use NG\API\Warehouses\Service\Import;
use NG\API\Warehouses\Service\Installer;
use NG\API\Warehouses\Service\Subscriber;
use PHPUnit\Framework\TestCase;
use WebStone\PDO\Database;
use WebStone\Requests\Clients\ClientCurl;
use WebStone\Stdlib\Classes\AutoInitialized;

class ImportTest extends TestCase
{
    public function testExecute()
    {
        /** logging */
        $config = [
            'class'   => LogBook::class,
            'enabled' => true,
            'track'   => 'import',
            'scribes' => [
                [
                    'class'             => FileScriber::class,
                    'severity'          => Severity::Debug,
                    'renderer'          => [
                        'class'           => StringRenderer::class,
                        'pattern'         => "%datetime%~%severity_name% %message% %assets%",
                        'format'          => 'Y-m-d\TH:i:s.vP',
                        'allow_multiline' => true,
                        'include_traces'  => true,
                        'base_path'       => __DIR__,
                        // 'row_max_length' => 128,
                        // 'row_oversize_replacement' => '...',
                    ],
                    'path'              => dirname(__DIR__, 2) . '/runtime/logs/',
                    'basename'          => 'import',
                    'size_threshold'    => 1024 * 1000,
                    'max_files'         => 7,
                    'write_immediately' => false,
                    'collaborative'     => true,
                ],
            ],
        ];

        if ($logging = ($log = AutoInitialized::turnInto($config)) instanceof LogBook) {
            LogBookShelf::put($log, true);
        }

        /** basic config */
        $config = [
            'db'         => [
                'class'       => Database::class,
                'connections' => [
                    [
                        'key'      => 'mysql',
                        'dsn'      => 'mysql:host=localhost;dbname=test',
                        'user'     => 'user',
                        'password' => 'password',
                        'options'  => [],
                    ],
                ],
                'default'     => 'mysql',
            ],
            'subscriber' => [
                'class'    => Subscriber::class,
                'client'   => [
                    'class'            => ClientCurl::class,
                    'auth_type'        => 'basic',
                    'username'         => 'demo',
                    'password'         => '5jTmD669jP',
                    'protocol_version' => '1.1',
                    'redirects_count'  => 5,
                    'timeout'          => 10,
                    'blocking'         => false,
                ],
                'host'     => 'api.novaposhta.international',
                'port'     => '8243',
                'protocol' => 'https',
            ],
            'logging' => $logging
        ];

        /** create table(s) */
        $installer = new Installer($config['db']);
        $installer->install();

        /** import */
        $import = new Import($config);        
        $import->execute([
            'country' => 'PL',
            'page' => 1,
            'ext' => 0,
            'partial'  => false,
            'old_remove' => true,
            'old_deactivate' => false,
        ]);

        /** remove table(s) */
        $installer->uninstall();

        $this->assertTrue(true);
    }
}
