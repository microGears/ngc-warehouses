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

use WebStone\Requests\Clients\Response;

/**
 * @method request(string $url, string $method = 'GET', $headers = [], $content = null, $async = false)
 * @method requestJson(string $url, string $method = 'GET', $headers = [], $content = null, $async = false)
 * @method getRequestUrl($url = '')
 * @method buildRequest(array $fields = [], bool $override = FALSE)
 * @method buildRequestHeaders(array $headers = [], bool $override = FALSE)
 */
trait SubscriberTrait
{
    public function getDirectory($url, array $params = [], string $method = 'GET'): Response{
        return $this->requestJson(
            $url,
            $method,
            $this->buildRequestHeaders(),
            $this->buildRequest($params),
            false
          );
    }
}
