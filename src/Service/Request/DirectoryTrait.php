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

namespace NG\API\Warehouses\Service\Request;

use NG\API\Warehouses\Service\SubscriberTrait;
use WebStone\Requests\Clients\Response;

/**
 * DirectoryTrait
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 19.08.2024 13:27:00
 */
trait DirectoryTrait
{
    use SubscriberTrait;
    public function getWarehouses(array $params = []): Response
    {
        return $this->getDirectory('/npi/Dictionary/getWarehouses', $params);
    }
}
/** End of DirectoryTrait **/
