<?php

namespace NerdsAndCompany\Schematic\DataTypes;

use Craft;
use NerdsAndCompany\Schematic\Schematic;
use NerdsAndCompany\Schematic\Interfaces\DataTypeInterface;

/**
 * Schematic GeneralSettings DataType.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class GeneralSettingsDataType implements DataTypeInterface
{
    /**
     * Get mapper component handle.
     *
     * @return string
     */
    public function getMapperHandle(): string
    {
        return 'generalSettingsMapper';
    }

    /**
     * Get data of this type.
     *
     * @return array
     */
    public function getRecords(): array
    {
        return [];
    }
}
