<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Thuiswinkel\BewustBezorgd\Api\OrderEmission;

use Magento\Framework\Api\SearchResultsInterface as MagentoSearchResultsInterface;

/**
 * Order Emission SearchResults Interface
 */
interface SearchResultsInterface extends MagentoSearchResultsInterface
{

    /**
     * Get Order Emission list.
     *
     * @return DataInterface[]
     */
    public function getItems();

    /**
     * Set Order Emission list.
     *
     * @param DataInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
