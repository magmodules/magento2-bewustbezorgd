<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Model;

use Thuiswinkel\BewustBezorgd\Api\OrderEmissionRepositoryInterface;
use Thuiswinkel\BewustBezorgd\Api\Data\OrderEmissionSearchResultsInterfaceFactory;
use Thuiswinkel\BewustBezorgd\Api\Data\OrderEmissionInterfaceFactory;
use Thuiswinkel\BewustBezorgd\Api\Data\OrderEmissionInterface;
use Thuiswinkel\BewustBezorgd\Model\ResourceModel\OrderEmission as ResourceOrderEmission;
use Thuiswinkel\BewustBezorgd\Model\ResourceModel\OrderEmission\CollectionFactory as OrderEmissionCollectionFactory;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Exception;

/**
 * Order Emission repository
 */
class OrderEmissionRepository implements OrderEmissionRepositoryInterface
{

    /** @var ResourceOrderEmission */
    protected $resource;

    /** @var JoinProcessorInterface */
    protected $extensionAttributesJoinProcessor;

    /** @var ExtensibleDataObjectConverter */
    protected $extensibleDataObjectConverter;

    /** @var OrderEmissionFactory */
    protected $orderEmissionFactory;

    /** @var OrderEmissionInterfaceFactory */
    protected $dataOrderEmissionFactory;

    /** @var DataObjectProcessor */
    protected $dataObjectProcessor;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var CollectionProcessorInterface */
    private $collectionProcessor;

    /** @var OrderEmissionCollectionFactory */
    protected $orderEmissionCollectionFactory;

    /** @var DataObjectHelper */
    protected $dataObjectHelper;

    /** @var OrderEmissionSearchResultsInterfaceFactory */
    protected $searchResultsFactory;

    /**
     * Constructor.
     *
     * @param ResourceOrderEmission $resource
     * @param OrderEmissionFactory $orderEmissionFactory
     * @param OrderEmissionInterfaceFactory $dataOrderEmissionFactory
     * @param OrderEmissionCollectionFactory $orderEmissionCollectionFactory
     * @param OrderEmissionSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceOrderEmission $resource,
        OrderEmissionFactory $orderEmissionFactory,
        OrderEmissionInterfaceFactory $dataOrderEmissionFactory,
        OrderEmissionCollectionFactory $orderEmissionCollectionFactory,
        OrderEmissionSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->orderEmissionFactory = $orderEmissionFactory;
        $this->orderEmissionCollectionFactory = $orderEmissionCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataOrderEmissionFactory = $dataOrderEmissionFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function save(OrderEmissionInterface $orderEmission)
    {
        $orderEmissionData = $this->extensibleDataObjectConverter->toNestedArray(
            $orderEmission,
            [],
            OrderEmissionInterface::class
        );
        
        $orderEmissionModel = $this->orderEmissionFactory->create()->setData($orderEmissionData);
        
        try {
            $this->resource->save($orderEmissionModel);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the orderEmission: %1',
                $exception->getMessage()
            ));
        }
        return $orderEmissionModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getById($orderEmissionId)
    {
        $orderEmission = $this->orderEmissionFactory->create();
        $this->resource->load($orderEmission, $orderEmissionId);
        if (!$orderEmission->getId()) {
            throw new NoSuchEntityException(__('OrderEmission with id "%1" does not exist.', $orderEmissionId));
        }
        return $orderEmission->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $collection = $this->orderEmissionCollectionFactory->create();
        
        $this->extensionAttributesJoinProcessor->process(
            $collection,
            OrderEmissionInterface::class
        );
        
        $this->collectionProcessor->process($criteria, $collection);
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        
        $items = [];
        foreach ($collection as $model) {
            $items[] = $model->getDataModel();
        }
        
        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(OrderEmissionInterface $orderEmission)
    {
        try {
            $orderEmissionModel = $this->orderEmissionFactory->create();
            $this->resource->load($orderEmissionModel, $orderEmission->getOrderEmissionId());
            $this->resource->delete($orderEmissionModel);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the OrderEmission: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($orderEmissionId)
    {
        return $this->delete($this->getById($orderEmissionId));
    }
}
