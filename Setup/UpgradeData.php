<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Setup;

use Zend_Validate_Exception;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Upgrade module data
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var SetupData
     */
    private $installer;

    /**
     * UpgradeData constructor.
     *
     * @param SetupData $installer
     */
    public function __construct(SetupData $installer)
    {
        $this->installer = $installer;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws LocalizedException
     * @throws Zend_Validate_Exception
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $moduleVersion = $context->getVersion();

        if (version_compare($moduleVersion, '0.9.0', '<')) {
            $this->installer->addBewustbezorgdLegsAttribute($setup);
        }
    }
}
