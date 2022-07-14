<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Thuiswinkel\BewustBezorgd\Model\OrderEmission;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

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
        $this->_init(DataModel::class, ResourceModel::class);
    }
}
