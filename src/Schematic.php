<?php

namespace NerdsAndCompany\Schematic;

/**
 * Schematic.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class Schematic
{
    const DATA_TYPES = [
        'volumes' => Services\Volumes::class,
        'assetTransforms' => Services\assetTransforms::class,
        'fields' => Services\Fields::class,
        'plugins' => Services\Plugins::class,
        'sections' => Services\Sections::class,
        'globalSets' => Services\GlobalSets::class,
        'userGroups' => Services\UserGroups::class,
        // 'users',
        // 'categoryGroups',
        // 'tagGroups',
        // 'elementIndexSettings',
        // 'pluginData',
    ];
}
