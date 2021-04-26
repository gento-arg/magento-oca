<?php
declare (strict_types=1);

namespace Gento\Oca\Setup;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * Uninstall constructor.
     * @param ResourceConnection $resource
     */
    public function __construct(ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.Generic.CodeAnalysis.UnusedFunctionParameter)
     */
    public function uninstall(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        //remove ui bookmark data
        $this->resource->getConnection()->delete(
            $this->resource->getTableName('ui_bookmark'),
            [
                'namespace IN (?)' => [
                    'gento_oca_operatory_listing',
                    'gento_oca_branch_listing',
                ],
            ]
        );
        if ($setup->tableExists('oca_operatories')) {
            $setup->getConnection()->dropTable('oca_operatories');
        }
        if ($setup->tableExists('oca_branches')) {
            $setup->getConnection()->dropTable('oca_branches');
        }
    }
}
