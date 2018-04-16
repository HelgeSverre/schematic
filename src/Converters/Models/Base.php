<?php

namespace NerdsAndCompany\Schematic\Converters\Models;

use Craft;
use craft\base\Model;
use craft\models\MatrixBlockType;
use yii\base\Component as BaseComponent;
use NerdsAndCompany\Schematic\Schematic;
use NerdsAndCompany\Schematic\Behaviors\FieldLayoutBehavior;
use NerdsAndCompany\Schematic\Behaviors\SourcesBehavior;
use NerdsAndCompany\Schematic\Interfaces\ConverterInterface;

/**
 * Schematic Base Converter.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
abstract class Base extends BaseComponent implements ConverterInterface
{
    /**
     * Load fieldlayout and sources behaviors.
     *
     * @return array
     */
    public function behaviors()
    {
        return [
          FieldLayoutBehavior::className(),
          SourcesBehavior::className(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    abstract public function saveRecord(Model $record, array $definition): bool;

    /**
     * {@inheritdoc}
     */
    abstract public function deleteRecord(Model $record): bool;

    /**
     * {@inheritdoc}
     */
    public function getRecordDefinition(Model $record): array
    {
        $definition = [
          'class' => get_class($record),
          'attributes' => $record->getAttributes(),
        ];
        unset($definition['attributes']['id']);
        unset($definition['attributes']['structureId']);
        unset($definition['attributes']['dateCreated']);
        unset($definition['attributes']['dateUpdated']);

        // Define sources
        if (isset($definition['attributes']['sources'])) {
            $definition['attributes']['sources'] = $this->getSources($definition['class'], $definition['attributes']['sources'], 'id', 'handle');
        }

        if (isset($definition['attributes']['source'])) {
            $definition['attributes']['source'] = $this->getSource($definition['class'], $definition['attributes']['sources'], 'id', 'handle');
        }

        // Define field layout
        if (isset($definition['attributes']['fieldLayoutId'])) {
            if (!$record instanceof MatrixBlockType) {
                $definition['fieldLayout'] = $this->getFieldLayoutDefinition($record->getFieldLayout());
            }
        }
        unset($definition['attributes']['fieldLayoutId']);

        // Define site settings
        if (isset($record->siteSettings)) {
            $definition['siteSettings'] = [];
            foreach ($record->getSiteSettings() as $siteSetting) {
                $definition['siteSettings'][$siteSetting->site->handle] = $this->getRecordDefinition($siteSetting);
            }
        }

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function setRecordAttributes(Model &$record, array $definition, array $defaultAttributes): void
    {
        // Set sources
        if (isset($definition['attributes']['sources'])) {
            $definition['attributes']['sources'] = $this->getSources($definition['class'], $definition['attributes']['sources'], 'handle', 'id');
        }

        if (isset($definition['attributes']['source'])) {
            $definition['attributes']['source'] = $this->getSource($definition['class'], $definition['attributes']['sources'], 'handle', 'id');
        }

        $attributes = array_merge($definition['attributes'], $defaultAttributes);
        $record->setAttributes($attributes, false);

        // Set field layout
        if (array_key_exists('fieldLayout', $definition)) {
            $record->setFieldLayout($this->getFieldLayout($definition['fieldLayout']));
        }

        // Set site settings
        if (array_key_exists('siteSettings', $definition)) {
            $siteSettings = [];
            foreach ($definition['siteSettings'] as $handle => $siteSettingDefinition) {
                $siteSetting = new $siteSettingDefinition['class']($siteSettingDefinition['attributes']);
                $site = Craft::$app->sites->getSiteByHandle($handle);
                if ($site) {
                    $siteSetting->siteId = $site->id;
                    $siteSettings[$site->id] = $siteSetting;
                } else {
                    Schematic::warning('  - Site '.$handle.' could not be found');
                }
            }
            $record->setSiteSettings($siteSettings);
        }
    }
}