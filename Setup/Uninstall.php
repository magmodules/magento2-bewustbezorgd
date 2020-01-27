<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Setup;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Module\Setup;

/**
 * Uninstall
 */
class Uninstall implements UninstallInterface
{
    /**
     * Remove data that was created during module installation.
     *
     * @param SchemaSetupInterface|Setup $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $uninstaller = $setup;

        $defaultConnection = $uninstaller->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $defaultConnection->dropTable(SetupSchema::TABLE_ORDER_EMISSION);
        $configTable = $uninstaller->getTable('core_config_data');
        $defaultConnection->delete($configTable, "`path` LIKE 'bewust_bezorgd/%'");
    }
}
