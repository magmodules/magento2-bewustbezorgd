<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Setup;

use Zend_Validate_Exception;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type as CatalogProductTypeModel;
use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedProductTypeModel;
use Magento\Bundle\Model\Product\Type as BundleProductTypeModel;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProductTypeModel;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Exception\LocalizedException;
use Thuiswinkel\BewustBezorgd\Model\Product\Attribute\Source\BewustbezorgdLegs;

/**
 * Data setup for use during installation / upgrade
 */
class SetupData
{
    const ATTRIBUTE_CODE_BEWUSTBEZORGD_LEGS = 'bewustbezorgd_legs';

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * SetupData constructor.
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Add bewustbezorgd_legs attributes.
     *
     * @param ModuleDataSetupInterface $setup
     * @throws LocalizedException
     * @throws Zend_Validate_Exception
     */
    public function addBewustbezorgdLegsAttribute(ModuleDataSetupInterface $setup)
    {
        $applyTo = implode(',', [
            CatalogProductTypeModel::TYPE_SIMPLE,
            GroupedProductTypeModel::TYPE_CODE,
            BundleProductTypeModel::TYPE_CODE,
            ConfigurableProductTypeModel::TYPE_CODE
        ]);
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->addAttribute(Product::ENTITY, self::ATTRIBUTE_CODE_BEWUSTBEZORGD_LEGS, [
            'type' => 'int',
            'label' => 'Bewustbezorgd Legs',
            'input' => 'select',
            'source' => BewustbezorgdLegs::class,
            'global' => 1,
            'visible' => true,
            'required' => true,
            'user_defined' => true,
            'default' => 2,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'used_in_product_listing' => false,
            'unique' => false,
            'apply_to' => $applyTo,
        ]);

        $groupName = 'BewustBezorgen';
        $entityTypeId = $eavSetup->getEntityTypeId(Product::ENTITY);
        $attributeSetIds = $eavSetup->getAllAttributeSetIds($entityTypeId);
        $attribute = $eavSetup->getAttribute($entityTypeId, self::ATTRIBUTE_CODE_BEWUSTBEZORGD_LEGS);
        foreach ($attributeSetIds as $attributeSetId) {
            $eavSetup->addAttributeGroup($entityTypeId, $attributeSetId, $groupName, 11);
            $eavSetup->addAttributeToGroup($entityTypeId, $attributeSetId, $groupName, $attribute['attribute_id'], 10);
        }
    }
}
