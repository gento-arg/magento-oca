<?php

namespace Gento\Oca\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 *
 * @package Gento\Oca\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $table = $setup->getConnection()
            ->newTable($setup->getTable('oca_operatories'))
            ->addColumn(
                'operatory_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                ],
                'Id'
            )
            ->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                ['default' => ''],
                'Name'
            )
            ->addColumn(
                'code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                10,
                ['default' => ''],
                'Code'
            )
            ->addColumn(
                'active',
                \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                null,
                [],
                'Active'
            )
            ->addColumn(
                'uses_idci',
                \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                null,
                ['defaults' => 0],
                'Uses id centro imposicion'
            )
            ->addColumn(
                'pays_on_destination',
                \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                null,
                ['defaults' => 0],
                'Pays on destination branch'
            )
            ->setComment('OCA Operatories');
        $setup->getConnection()->createTable($table);

        $table = $setup->getConnection()
            ->newTable($setup->getTable('oca_branches'))
            ->addColumn(
                'branch_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                ],
                'Id'
            )
            ->addColumn(
                'code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                10,
                ['default' => ''],
                'Code'
            )
            ->addColumn(
                'short_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                10,
                ['default' => ''],
                'Short name'
            )
            ->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                ['default' => ''],
                'Name'
            )
            ->addColumn(
                'description',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                ['default' => ''],
                'Description'
            )
            ->addColumn(
                'address_street',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                ['default' => ''],
                'Address street'
            )
            ->addColumn(
                'address_number',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                ['default' => ''],
                'Address number'
            )
            ->addColumn(
                'address_floor',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                ['default' => ''],
                'Address floor'
            )
            ->addColumn(
                'city',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                ['default' => ''],
                'City'
            )
            ->addColumn(
                'zipcode',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                20,
                ['default' => ''],
                'Zipcode'
            )
            ->addColumn(
                'active',
                \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                null,
                ['default' => 0],
                'Active'
            )
            ->setComment('OCA Branches');
        $setup->getConnection()->createTable($table);

        $setup->endSetup();
    }
}
