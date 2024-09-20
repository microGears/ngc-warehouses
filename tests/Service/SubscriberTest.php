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

namespace NG\API\Warehouses\Tests\Service;

use NG\API\Warehouses\Service\Subscriber;
use PHPUnit\Framework\TestCase;
use WebStone\Requests\Clients\ClientCurl;

class SubscriberTest extends TestCase
{
    protected array $config = [];
    protected function setUp(): void
    {
        $this->config = [
            // Transport configuration
            'client'       => [
                'class'            => ClientCurl::class,
                'auth_type'        => 'basic',
                'username'         => 'demo', // enter your real username
                'password'         => '5jTmD669jP', // enter your real password
                'protocol_version' => '1.1',
                'redirects_count'  => 5,
                'timeout'          => 5,
                'blocking'         => false,
            ],
            // API server configuration
            'host'     => 'api.novaposhta.international',
            'port'     => '8243',
            'protocol' => 'https',
        ];
    }

    public function testConstructor()
    {
        $subcriber = new Subscriber($this->config);
        $this->assertInstanceOf(Subscriber::class, $subcriber);
    }

    public function testAuthAndRequest()
    {
        $subcriber = new Subscriber($this->config);
        $this->assertInstanceOf(Subscriber::class, $subcriber);

        $responce = $subcriber->requestJson('/api/getTime','GET');
        
        $this->assertEquals(200, $responce->getStatusCode());
        $this->assertIsString($responce->getContentsItem('time',false));
    }    
}
