<?php

namespace NerdsAndCompany\Schematic\Converters\Fields;

use Craft;
use craft\fields\Matrix as MatrixModel;
use Codeception\Test\Unit;

/**
 * Class MatrixTest.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class MatrixTest extends Unit
{
    /**
     * @var Matrixs
     */
    private $converter;

    /**
     * Set the converter.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _before()
    {
        $this->converter = new Matrix();
    }

    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @dataProvider provideMatrices
     *
     * @param MatrixModel $matrix
     * @param array       $definition
     */
    public function testGetRecordDefinition(MatrixModel $matrix, array $definition)
    {
        Craft::$app->schematic_fields->expects($this->exactly(1))
                           ->method('export')
                           ->with($matrix->getBlockTypes())
                           ->willReturn($definition['blockTypes']);

        $result = $this->converter->getRecordDefinition($matrix);

        $this->assertSame($definition, $result);
    }

    /**
     * @dataProvider provideMatrices
     *
     * @param MatrixModel $matrix
     * @param array       $definition
     * @param bool        $valid
     */
    public function testSaveRecord(MatrixModel $matrix, array $definition, bool $valid)
    {
        Craft::$app->fields->expects($this->exactly(1))
                           ->method('saveField')
                           ->with($matrix)
                           ->willReturn($valid);

        Craft::$app->schematic_fields->expects($this->exactly($valid ? 1 : 0))
                           ->method('import')
                           ->with($definition['blockTypes'], $matrix->getBlockTypes(), ['fieldId' => $matrix->id])
                           ->willReturn($matrix->getBlockTypes());

        $result = $this->converter->saveRecord($matrix, $definition);

        $this->assertSame($valid, $result);
    }

    /**
     * @dataProvider provideMatrices
     *
     * @param MatrixModel $matrix
     */
    public function testDeleteRecord(MatrixModel $matrix)
    {
        Craft::$app->fields->expects($this->exactly(1))
                           ->method('deleteField')
                           ->with($matrix);

        $this->converter->deleteRecord($matrix);
    }

    //==============================================================================================================
    //==============================================  PROVIDERS  ===================================================
    //==============================================================================================================

    /**
     * @return array
     */
    public function provideMatrices()
    {
        $mockMatrix1 = $this->getMockMatrix(1, 1);
        $mockMatrix2 = $this->getMockMatrix(2, 1);

        return [
            'valid matrix' => [
                'matrix' => $mockMatrix1,
                'definition' => $this->getMockMatrixDefinition($mockMatrix1),
                'validSave' => true,
            ],
            'invalid matrix' => [
                'matrix' => $mockMatrix2,
                'definition' => $this->getMockMatrixDefinition($mockMatrix2),
                'validSave' => false,
            ],
        ];
    }

    //==============================================================================================================
    //================================================  HELPERS  ===================================================
    //==============================================================================================================

    /**
     * @param MatrixModel $mockMatrix
     *
     * @return array
     */
    private function getMockMatrixDefinition(MatrixModel $mockMatrix)
    {
        return [
            'class' => get_class($mockMatrix),
            'attributes' => [
                'minBlocks' => null,
                'maxBlocks' => null,
                'localizeBlocks' => false,
                'name' => 'matrixName'.$mockMatrix->id,
                'handle' => 'matrixHandle'.$mockMatrix->id,
                'instructions' => null,
                'translationMethod' => 'none',
                'translationKeyFormat' => null,
                'oldHandle' => null,
                'columnPrefix' => null,
                'required' => false,
                'sortOrder' => null,
            ],
            'group' => $mockMatrix->group->name,
            'blockTypes' => [
                'blockTypeDefinition1',
                'blockTypeDefinition2',
            ],
        ];
    }

    /**
     * @param int $matrixId
     * @param int $groupId
     *
     * @return Mock|MatrixModel
     */
    private function getMockMatrix(int $matrixId, int $groupId)
    {
        $mockMatrix = $this->getMockBuilder(MatrixModel::class)
                           ->setMethods(['getGroup', 'getBlockTypes'])
                           ->disableOriginalConstructor()
                           ->getMock();

        $mockMatrix->id = $matrixId;
        $mockMatrix->groupId = $matrixId;
        $mockMatrix->handle = 'matrixHandle'.$matrixId;
        $mockMatrix->name = 'matrixName'.$matrixId;

        $mockMatrix->expects($this->any())
                 ->method('getGroup')
                 ->willReturn($this->getMockFieldGroup($groupId));

        $mockMatrix->expects($this->any())
                   ->method('getBlockTypes')
                   ->willReturn([
                       $this->getMockMatrixBlockType(1),
                       $this->getMockMatrixBlockType(2),
                   ]);

        return $mockMatrix;
    }

    /**
     * Get a mock matrix group.
     *
     * @param int $groupId
     *
     * @return Mock|FieldGroup
     */
    private function getMockFieldGroup(int $groupId)
    {
        $mockGroup = $this->getMockBuilder(FieldGroup::class)
                          ->disableOriginalConstructor()
                          ->getmock();

        $mockGroup->id = $groupId;
        $mockGroup->name = 'fieldGroup'.$groupId;

        return $mockGroup;
    }

    /**
     * Get a mock matrix block type.
     *
     * @param int $blockId
     *
     * @return Mock|MatrixBlockType
     */
    private function getMockMatrixBlockType($blockId)
    {
        $mockBlockType = $this->getMockBuilder(MatrixBlockType::class)
                              ->disableOriginalConstructor()
                              ->getmock();

        $mockBlockType->id = $blockId;
        $mockBlockType->handle = 'blockHandle'.$blockId;

        return $mockBlockType;
    }
}
