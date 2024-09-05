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

namespace NG\API\Warehouses\Model;

/**
 * LayoutInterface
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 23.08.2024 10:33:00
 */
interface LayoutInterface
{
    public static function getFields(): array;
    public static function getIndexes(): array;
    public static function getTableName(): string;
    public static function getPrimaryKey(): string;
}
/** End of LayoutInterface **/
