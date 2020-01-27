<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Model\ResourceModel\OrderEmission;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Thuiswinkel\BewustBezorgd\Model\OrderEmission;
use Thuiswinkel\BewustBezorgd\Model\ResourceModel\OrderEmission as ResourceModel;

/**
 * Order Emission Collection
 */
class Collection extends AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(OrderEmission::class, ResourceModel::class);
    }
}
