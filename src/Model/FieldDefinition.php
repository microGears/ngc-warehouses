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
use WebStone\Stdlib\Classes\AutoInitialized;

/**
 * Field
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 * @datetime 27.08.2024 16:14:00
 */
class FieldDefinition extends AutoInitialized implements FieldDefinitionInterface
{
    private string $type = 'string';
    private int $length = 0;
    private bool $null = false;
    private mixed $default = null;

    public function getType(): string
    {
        return strtolower($this->type);
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function setLength(int $length): void
    {
        $this->length = $length;
    }

    public function isNull(): bool
    {
        return $this->null;
    }

    public function setNull(bool $null): void
    {
        $this->null = $null;
    }

    public function getDefault()
    {
        return $this->default;
    }

    public function setDefault(mixed $default): void
    {
        $this->default = $default;
    }

    public function toArray(): array{
        return [
            'type' => $this->type,
            'length' => $this->length,
            'null' => $this->null,
            'default' => $this->default
        ];
    }
}
/** End of Field **/
