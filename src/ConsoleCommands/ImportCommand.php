<?php

namespace NerdsAndCompany\Schematic\ConsoleCommands;

use Craft;
use yii\console\Controller as Base;
use Craft\IOHelper;
use NerdsAndCompany\Schematic\Services\Schematic;

/**
 * Schematic Import Command.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class ImportCommand extends Base
{
    /**
     * Imports the Craft datamodel.
     *
     * @param string $file          yml file containing the schema definition
     * @param string $override_file yml file containing the override values
     * @param bool   $force         if set to true items not in the import will be deleted
     * @param array  $exclude       Data to not import
     *
     * @return int
     */
    public function actionIndex($file = 'craft/config/schema.yml', $override_file = 'craft/config/override.yml', $force = false, array $exclude = null)
    {
        if (!IOHelper::fileExists($file)) {
            $this->usageError('File not found.');
        }

        $dataTypes = Schematic::getExportableDataTypes();

        // If there are data exclusions.
        if ($exclude !== null) {
            // Find any invalid data to exclude.
            $invalidExcludes = array_diff($exclude, $dataTypes);

            // If any invalid exclusions were specified.
            if (count($invalidExcludes) > 0) {
                $errorMessage = 'Invalid exlude';

                if (count($invalidExcludes) > 1) {
                    $errorMessage .= 's';
                }

                $errorMessage .= ': '.implode(', ', $invalidExcludes).'.';
                $errorMessage .= ' Valid exclusions are '.implode(', ', $dataTypes);

                // Output an error message outlining what invalid exclusions were specified.
                echo "\n".$errorMessage."\n\n";

                return 1;
            }

            // Remove any explicitly excluded data types from the list of data types to export.
            $dataTypes = array_diff($dataTypes, $exclude);
        }

        $result = Craft::$app->schematic->importFromYaml($file, $override_file, $force, $dataTypes);

        if (!$result->hasErrors()) {
            Craft::info('Loaded schema from {file}', ['file' => $file], 'schematic');

            return 0;
        }

        Craft::info('There was an error loading schema from {file}', ['file' => $file], 'schematic');
        print_r($result->getErrors());

        return 1;
    }

    // /**
    //  * Import from Yaml file.
    //  *
    //  * @param string $file
    //  * @param string $override
    //  * @param bool   $force if set to true items not included in import will be deleted
    //  * @param string $dataTypes The data types to import
    //  *
    //  * @return Result
    //  * @throws Exception
    //  */
    // public function importFromYaml($file, $override = null, $force = false, $dataTypes = 'all')
    // {
    //     Craft::$app->config->maxPowerCaptain();
    //     Craft::$app->setComponent('userSession', $this);
    //
    //     $yaml = FileHelper::getFileContents($file);
    //     $yaml_override = FileHelper::getFileContents($override);
    //     $dataModel = Data::fromYaml($yaml, $yaml_override);
    //
    //     return $this->importDataModel($dataModel, $force, $dataTypes);
    // }
}
