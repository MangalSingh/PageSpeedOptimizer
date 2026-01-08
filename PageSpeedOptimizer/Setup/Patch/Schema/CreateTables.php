<?php
namespace Octocub\PageSpeedOptimizer\Setup\Patch\Schema;

use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class CreateTables implements SchemaPatchInterface
{
    public function __construct(private SchemaSetupInterface $schemaSetup) {}

    public function apply()
    {
        $setup = $this->schemaSetup;
        $setup->startSetup();

        $conn = $setup->getConnection();
        $tableName = $setup->getTable('octocub_gps_image_queue');

        if (!$conn->isTableExists($tableName)) {
            $table = $conn->newTable($tableName)
                ->addColumn('entity_id', Table::TYPE_INTEGER, null, [
                    'identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true
                ], 'ID')
                ->addColumn('file_path', Table::TYPE_TEXT, 1024, ['nullable' => false], 'Absolute file path')
                ->addColumn('device', Table::TYPE_TEXT, 16, ['nullable' => false, 'default' => 'all'], 'Device group')
                ->addColumn('status', Table::TYPE_TEXT, 16, ['nullable' => false, 'default' => 'pending'], 'pending|processing|done|error')
                ->addColumn('attempts', Table::TYPE_INTEGER, null, ['nullable' => false, 'default' => 0], 'Attempts')
                ->addColumn('last_error', Table::TYPE_TEXT, 2048, ['nullable' => true], 'Last error')
                ->addColumn('created_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT], 'Created')
                ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'Updated')
                ->addIndex($setup->getIdxName($tableName, ['status']), ['status'])
                ->addIndex($setup->getIdxName($tableName, ['file_path']), ['file_path'])
                ->setComment('Octocub GPS Image Optimization Queue');

            $conn->createTable($table);
        }

        $setup->endSetup();
    }

    public static function getDependencies(): array { return []; }
    public function getAliases(): array { return []; }
}
