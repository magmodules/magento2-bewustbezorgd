<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Plugin\Magento\Framework\View\Element\UiComponent\DataProvider;

use Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory as OriginClass;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as OrderGridCollection;
use Thuiswinkel\BewustBezorgd\Model\Config as ConfigModel;
use Thuiswinkel\BewustBezorgd\Model\ResourceModel\OrderEmission as EmissionResourceModel;

/**
 * Plugin for Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory
 */
class CollectionFactory
{
    /** @var ConfigModel */
    protected $configModel;

    /**
     * Constructor.
     *
     * @param ConfigModel $configModel
     */
    public function __construct(ConfigModel $configModel)
    {
        $this->configModel = $configModel;
    }

    /**
     * After plugin for method Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory::getReport
     *
     * @param OriginClass $subject
     * @param $result
     * @param $requestName
     * @return mixed
     */
    public function afterGetReport(OriginClass $subject, $result, $requestName)
    {
        if ($requestName !== 'sales_order_grid_data_source') {
            return $result;
        }

        if (!$this->configModel->isEnabled()) {
            return $result;
        }
        /** @var OrderGridCollection $collection */
        $collection = $result;
        $collection->getSelect()->joinLeft(
            ['emission_table' => $collection->getTable(EmissionResourceModel::MAIN_TABLE)],
            'emission_table.order_id = main_table.entity_id',
            'emission'
        );

        return $result;
    }
}
