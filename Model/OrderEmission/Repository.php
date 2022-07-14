<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Thuiswinkel\BewustBezorgd\Model\OrderEmission;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Thuiswinkel\BewustBezorgd\Api\OrderEmission\DataInterface;
use Thuiswinkel\BewustBezorgd\Api\OrderEmission\DataInterfaceFactory;
use Thuiswinkel\BewustBezorgd\Api\OrderEmission\SearchResultsInterface;
use Thuiswinkel\BewustBezorgd\Api\OrderEmission\SearchResultsInterfaceFactory;
use Thuiswinkel\BewustBezorgd\Api\OrderEmission\RepositoryInterface;
use Thuiswinkel\BewustBezorgd\Api\Log\RepositoryInterface as LogRepository;

/**
 * OrderEmission Repository class
 */
class Repository implements RepositoryInterface
{

    /**
     * @var SearchResultsInterfaceFactory
     */
    private $searchResultFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var DataInterfaceFactory
     */
    private $dataFactory;

    /**
     * @var ResourceModel
     */
    private $resource;

    /**
     * @var LogRepository
     */
    private $logger;

    /**
     * Repository constructor.
     * @param SearchResultsInterfaceFactory $searchResultFactory
     * @param CollectionFactory $collectionFactory
     * @param ResourceModel $resource
     * @param DataInterfaceFactory $dataFactory
     * @param LogRepository $logger
     */
    public function __construct(
        SearchResultsInterfaceFactory $searchResultFactory,
        CollectionFactory $collectionFactory,
        ResourceModel $resource,
        DataInterfaceFactory $dataFactory,
        LogRepository $logger
    ) {
        $this->searchResultFactory = $searchResultFactory;
        $this->collectionFactory = $collectionFactory;
        $this->resource = $resource;
        $this->dataFactory = $dataFactory;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function create(): DataInterface
    {
        return $this->dataFactory->create();
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface
    {
        /* @var Collection $collection */
        $collection = $this->collectionFactory->create();
        return $this->searchResultFactory->create()
            ->setSearchCriteria($searchCriteria)
            ->setItems($collection->getItems())
            ->setTotalCount($collection->getSize());
    }

    /**
     * @inheritDoc
     */
    public function deleteById(int $entityId): bool
    {
        $entity = $this->get((int)$entityId);
        return $this->delete($entity);
    }

    /**
     * @inheritDoc
     */
    public function get(int $entityId): DataInterface
    {
        if (!$entityId) {
            $exceptionMsg = static::INPUT_EXCEPTION;
            throw new InputException(__($exceptionMsg));
        } elseif (!$this->resource->isExists($entityId)) {
            $exceptionMsg = self::NO_SUCH_ENTITY_EXCEPTION;
            throw new NoSuchEntityException(__($exceptionMsg, $entityId));
        }
        return $this->dataFactory->create()
            ->load($entityId);
    }

    /**
     * @inheritDoc
     */
    public function delete(DataInterface $entity): bool
    {
        try {
            $this->resource->delete($entity);
        } catch (\Exception $exception) {
            $this->logger->addDataLog('Delete order emission entity', $exception->getMessage());
            $exceptionMsg = self::COULD_NOT_DELETE_EXCEPTION;
            throw new CouldNotDeleteException(__(
                $exceptionMsg,
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function save(DataInterface $entity): DataInterface
    {
        try {
            $this->resource->save($entity);
        } catch (\Exception $exception) {
            $this->logger->addDataLog('Save order emission entity', $exception->getMessage());
            $exceptionMsg = self::COULD_NOT_SAVE_EXCEPTION;
            throw new CouldNotSaveException(__(
                $exceptionMsg,
                $exception->getMessage()
            ));
        }
        return $entity;
    }
}
