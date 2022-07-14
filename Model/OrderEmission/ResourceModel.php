<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Thuiswinkel\BewustBezorgd\Model\OrderEmission;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Order Emission entity resource model
 */
class ResourceModel extends AbstractDb
{
    public const MAIN_TABLE = 'thuiswinkel_order_emission';
    public const ID_FIELD_NAME = 'order_emission_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, self::ID_FIELD_NAME);
    }
}
