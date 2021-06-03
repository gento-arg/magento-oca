<?php
declare (strict_types=1);

namespace Gento\Oca\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface
{
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            $setup->getConnection()
                ->update(
                    $setup->getTable('core_config_data'),
                    ['path' => 'carriers/gento_oca/cuit'],
                    [$setup->getConnection()->quoteInto('path = ?', 'tax/defaults/cuit')]
                );
        }

        $setup->endSetup();
    }
}
