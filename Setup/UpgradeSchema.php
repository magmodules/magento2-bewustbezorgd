<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Setup;

use Zend_Db_Exception;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Upgrade module schema
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /** @var SetupSchema */
    private $installer;

    /**
     * UpgradeSchema constructor.
     *
     * @param SetupSchema $installer
     */
    public function __construct(SetupSchema $installer)
    {
        $this->installer = $installer;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     * @throws Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $moduleVersion = $context->getVersion();

        if (version_compare($moduleVersion, '0.9.0', '<')) {
            $this->installer->createOrderEmissionTable($setup);
            $this->installer->addFkToOrderEmissionTable($setup);
        }
    }
}
