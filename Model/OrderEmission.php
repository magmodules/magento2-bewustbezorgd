<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Model;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\AbstractModel;
use Thuiswinkel\BewustBezorgd\Api\Data\OrderEmissionInterface;
use Thuiswinkel\BewustBezorgd\Api\Data\OrderEmissionInterfaceFactory;

/**
 * Order Emission model
 */
class OrderEmission extends AbstractModel
{

    /** @var OrderEmissionInterfaceFactory */
    protected $orderEmissionDataFactory;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'thuiswinkel_order_emission';

    /** @var DataObjectHelper */
    protected $dataObjectHelper;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param OrderEmissionInterfaceFactory $orderEmissionDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param ResourceModel\OrderEmission $resource
     * @param ResourceModel\OrderEmission\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        OrderEmissionInterfaceFactory $orderEmissionDataFactory,
        DataObjectHelper $dataObjectHelper,
        ResourceModel\OrderEmission $resource,
        ResourceModel\OrderEmission\Collection $resourceCollection,
        array $data = []
    ) {
        $this->orderEmissionDataFactory = $orderEmissionDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieves Order Emission model with Order Emission data
     *
     * @return OrderEmissionInterface
     */
    public function getDataModel()
    {
        $orderEmissionData = $this->getData();
        
        $orderEmissionDataObject = $this->orderEmissionDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $orderEmissionDataObject,
            $orderEmissionData,
            OrderEmissionInterface::class
        );
        
        return $orderEmissionDataObject;
    }
}
