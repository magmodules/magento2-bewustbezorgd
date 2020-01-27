<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Order Emission Repository Interface
 */
interface OrderEmissionRepositoryInterface
{

    /**
     * Save Order Emission
     *
     * @param Data\OrderEmissionInterface $orderEmission
     * @return Data\OrderEmissionInterface
     * @throws LocalizedException
     */
    public function save(
        Data\OrderEmissionInterface $orderEmission
    );

    /**
     * Retrieves Order Emission by ID
     *
     * @param string $orderEmissionId
     * @return Data\OrderEmissionInterface
     * @throws LocalizedException
     */
    public function getById($orderEmissionId);

    /**
     * Retrieves Order Emission matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return Data\OrderEmissionSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Order Emission
     *
     * @param Data\OrderEmissionInterface $orderEmission
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(
        Data\OrderEmissionInterface $orderEmission
    );

    /**
     * Delete Order Emission by ID
     *
     * @param string $orderEmissionId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($orderEmissionId);
}
