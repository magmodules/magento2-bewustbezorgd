<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Setup\Patch\Data;

use Magento\Bundle\Model\Product\Type as BundleProductTypeModel;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type as CatalogProductTypeModel;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProductTypeModel;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedProductTypeModel;
use Thuiswinkel\BewustBezorgd\Model\Product\Attribute\Source\BewustbezorgdLegs;
use Zend_Validate_Exception;

/**
 * Add Product Attributes
 */
class ProductAttributes implements DataPatchInterface, PatchRevertableInterface
{

    public const ATTRIBUTE_CODE_BEWUSTBEZORGD_LEGS = 'bewustbezorgd_legs';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * ProductAttributes constructor.
     * @param EavSetupFactory $eavSetupFactory
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @return DataPatchInterface|void
     * @throws LocalizedException
     * @throws Zend_Validate_Exception
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        if (!$eavSetup->getAttributeId(Product::ENTITY, self::ATTRIBUTE_CODE_BEWUSTBEZORGD_LEGS)) {
            $this->addBewustbezorgdLegsAttribute();
        }
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function revert()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        if ($eavSetup->getAttributeId(Product::ENTITY, self::ATTRIBUTE_CODE_BEWUSTBEZORGD_LEGS)) {
            $eavSetup->removeAttribute(
                Product::ENTITY,
                self::ATTRIBUTE_CODE_BEWUSTBEZORGD_LEGS
            );
        }
    }

    /**
     * Add bewustbezorgd_legs attributes.
     *
     * @throws LocalizedException
     * @throws Zend_Validate_Exception
     */
    public function addBewustbezorgdLegsAttribute()
    {
        $applyTo = implode(',', [
            CatalogProductTypeModel::TYPE_SIMPLE,
            GroupedProductTypeModel::TYPE_CODE,
            BundleProductTypeModel::TYPE_CODE,
            ConfigurableProductTypeModel::TYPE_CODE
        ]);
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
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
