<?php

namespace NerdsAndCompany\Schematic\ConsoleCommands;

use Craft;
use craft\helpers\FileHelper;
use NerdsAndCompany\Schematic\Schematic;
use Symfony\Component\Yaml\Yaml;
use yii\console\Controller as Base;

/**
 * Schematic Export Command.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class ExportCommand extends Base
{
    public $file = 'config/schema.yml';
    public $exclude;
    public $include;

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function options($actionID)
    {
        return ['file', 'include', 'exclude'];
    }

    /**
     * Exports the Craft datamodel.
     *
     * @param string $file    file to write the schema to
     * @param array  $exclude Data to not export
     *
     * @return int
     */
    public function actionIndex()
    {
        $dataTypes = Schematic::DATA_TYPES;

        // If include is specified.
        if ($this->include !== null) {
            $dataTypes = $this->applyIncludes($dataTypes);
        }

        // If there are exclusions.
        if ($this->exclude !== null) {
            $dataTypes = $this->applyExcludes($dataTypes);
        }

        $this->exportToYaml($this->file, $dataTypes);
        Craft::info('Exported schema to '.$this->file, 'schematic');

        return 0;
    }

    /**
     * Export to Yaml file.
     *
     * @param string $file
     * @param bool   $autoCreate
     *
     * @return Result
     */
    public function exportToYaml($file, $dataTypes)
    {
        $result = [];
        foreach (array_keys($dataTypes) as $dataType) {
            Craft::info('Exporting '.$dataType, 'schematic');
            $component = 'schematic_'.$dataType;
            $result[$dataType] = Craft::$app->$component->export();
        }

        $yaml = Yaml::dump($result, 10);
        if (!FileHelper::writeToFile($file, $yaml)) {
            Craft::error('error', "Failed to write contents to \"$file\"", 'schematic');
        }

        return true;
    }

    /**
     * Apply given includes
     *
     * @param  array $dataTypes
     * @return array
     */
    private function applyIncludes($dataTypes)
    {
        $inclusions = explode(',', $this->include);
        // Find any invalid data to include.
        $invalidIncludes = array_diff($inclusions, $dataTypes);
        if (count($invalidIncludes) > 0) {
            $errorMessage = 'WARNING: Invalid include(s)';
            $errorMessage .= ': '.implode(', ', $invalidIncludes).'.'.PHP_EOL;
            $errorMessage .= ' Valid inclusions are '.implode(', ', $dataTypes);

            // Output an error message outlining what invalid exclusions were specified.
            echo PHP_EOL.$errorMessage.PHP_EOL;
        }
        // Remove any explicitly included data types from the list of data types to export.
        return array_intersect($dataTypes, $inclusions);
    }

    /**
     * Apply given excludes
     *
     * @param  array $dataTypes
     * @return array
     */
    private function applyExcludes(array $dataTypes)
    {
        $exclusions = explode(',', $this->exclude);
        // Find any invalid data to exclude.
        $invalidExcludes = array_diff($exclusions, $dataTypes);
        if (count($invalidExcludes) > 0) {
            $errorMessage = 'WARNING: Invalid exlude(s)';
            $errorMessage .= ': '.implode(', ', $invalidExcludes).'.'.PHP_EOL;
            $errorMessage .= ' Valid exclusions are '.implode(', ', $dataTypes);

            // Output an error message outlining what invalid exclusions were specified.
            echo PHP_EOL.$errorMessage.PHP_EOL;
        }
        // Remove any explicitly excluded data types from the list of data types to export.
        return array_diff($dataTypes, $exclusions);
    }
}
