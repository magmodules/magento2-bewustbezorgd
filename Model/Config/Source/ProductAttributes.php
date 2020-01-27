<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\DataObject;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;

/**
 * Source model for Product Attributes select drop down
 */
class ProductAttributes implements OptionSourceInterface
{
    /** @var AttributeCollectionFactory */
    protected $attributeCollectionFactory;

    /**
     * Constructor.
     *
     * @param AttributeCollectionFactory $attributeCollectionFactory
     */
    public function __construct(AttributeCollectionFactory $attributeCollectionFactory)
    {
        $this->attributeCollectionFactory = $attributeCollectionFactory;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        /** @var Attribute[] */
        $attributes = $this->getProductAttributes();
        $arr = [];

        foreach ($attributes as $attribute) {
            $arr[] = ['value' => $attribute->getAttributeCode(), 'label' => $attribute->getFrontendLabel()];
        }
        array_unshift($arr, ['value' => '', 'label' => __('-- Please select --')]);

        return $arr;
    }

    /**
     * Retrieves all product attributes
     *
     * @return Attribute[]|DataObject[]
     */
    protected function getProductAttributes()
    {
        return $this->attributeCollectionFactory->create()
            ->addFieldToFilter('frontend_label', ['notnull' => true])
            ->addVisibleFilter()
            ->removePriceFilter()
            ->getItems();
    }
}
