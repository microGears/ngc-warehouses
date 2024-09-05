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
 * FieldInterface
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 27.08.2024 16:13:00
 */
interface FieldDefinitionInterface
{
    public function getType(): string;
    public function setType(string $type): void;

    public function getLength(): int;
    public function setLength(int $length): void;

    public function isNull(): bool;
    public function setNull(bool $null): void;

    public function getDefault();
    public function setDefault($default): void;

    public function toArray(): array;
}
/** End of FieldInterface **/