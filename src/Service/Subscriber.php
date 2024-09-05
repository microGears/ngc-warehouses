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

use NG\API\Warehouses\Service\Request\DirectoryTrait;
use WebStone\Requests\Clients\Response;
use WebStone\Requests\Requester;

/**
 * Subscriber
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 19.08.2024 12:27:00
 */
class Subscriber extends Requester
{   
    use DirectoryTrait;
    
    public function requestJson(string $url, string $method = 'GET', $headers = [], $content = null, $async = false):Response
    {
        if (!empty($content)) {
            $headers = array_merge($headers, [
                'Content-Type'   => 'application/json',
                'Content-Length' => mb_strlen($content = json_encode($content)),
            ]);
        }

        return $this->request($url, $method, $headers, $content, $async);
    }

    /**
     * Reserved for future use
     * @param array $fields
     * @param bool $override
     * @return array
     */
    public function buildRequest(array $fields = [], bool $override = FALSE): array
    {
        return $fields;
    }

    /**
     * Reserved for future use
     * @param array $headers
     * @param bool $override
     * @return array
     */
    public function buildRequestHeaders(array $headers = [], bool $override = FALSE): array
    {
        return $headers;
    }

}
/** End of Subscriber **/