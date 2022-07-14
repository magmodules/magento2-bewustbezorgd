<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Thuiswinkel\BewustBezorgd\Api\OrderEmission;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Order Emission Repository Interface
 */
interface RepositoryInterface
{

    /**
     * Exception text
     */
    public const INPUT_EXCEPTION = 'An ID is needed. Set the ID and try again.';

    /**
     * Exception text
     */
    public const NO_SUCH_ENTITY_EXCEPTION = 'The order emission entity with id "%1" does not exist.';

    /**
     * Exception text
     */
    public const COULD_NOT_DELETE_EXCEPTION = 'Could not delete the order emission entity: %1';

    /**
     * Exception text
     */
    public const COULD_NOT_SAVE_EXCEPTION = 'Could not save the order emission entity: %1';

    /**
     * Save Order Emission
     *
     * @param DataInterface $orderEmission
     * @return DataInterface
     * @throws LocalizedException
     */
    public function save(
        DataInterface $orderEmission
    );

    /**
     * Retrieves Order Emission matching the specified ID.
     *
     * @param int $entityId
     * @return DataInterface
     */
    public function get(int $entityId): DataInterface;

    /**
     * Retrieves Order Emission matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Order Emission
     *
     * @param DataInterface $entity
     * @return bool true on success
     */
    public function delete(DataInterface $entity);

    /**
     * Delete Order Emission by ID
     *
     * @param int $entityId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById(int $entityId);
}
