<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Order Emission SearchResults Interface
 */
interface OrderEmissionSearchResultsInterface extends SearchResultsInterface
{

    /**
     * Get Order Emission list.
     *
     * @return OrderEmissionInterface[]
     */
    public function getItems();

    /**
     * Set Order Emission list.
     *
     * @param OrderEmissionInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
