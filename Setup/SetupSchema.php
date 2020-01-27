<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Setup;

use Zend_Db_Exception;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Thuiswinkel\BewustBezorgd\Api\Data\OrderEmissionInterface;

/**
 * Schema setup for use during installation / upgrade
 */
class SetupSchema
{
    const TABLE_ORDER_EMISSION = 'thuiswinkel_order_emission';

    /**
     * @param SchemaSetupInterface $installer
     * @throws Zend_Db_Exception
     *
     * @return void
     */
    public function createOrderEmissionTable(SchemaSetupInterface $installer)
    {
        $table = $installer->getConnection()->newTable($installer->getTable(self::TABLE_ORDER_EMISSION));

        $table->addColumn(
            'order_emission_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true],
            'Entity ID'
        );

        $table->addColumn(
            'order_id',
            Table::TYPE_INTEGER,
            10,
            ['nullable' => false, 'unsigned' => true],
            'Magento Order ID'
        );

        $table->addColumn(
            'service_type',
            Table::TYPE_TEXT,
            25,
            [],
            'Service Type'
        );

        $table->addColumn(
            'emission',
            Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Order Emission'
        );

        $table->addColumn(
            'meters_diesel',
            Table::TYPE_DECIMAL,
            '12,4',
            []
        );

        $table->addColumn(
            'meters_gasoline',
            Table::TYPE_DECIMAL,
            '12,4',
            []
        );

        $installer->getConnection()->createTable($table);
    }

    /**
     * Adds foreign key to table `thuiswinkel_order_emission`
     *
     * @param SchemaSetupInterface $installer
     * @return void
     */
    public function addFkToOrderEmissionTable(SchemaSetupInterface $installer)
    {
        $installer->getConnection()->addForeignKey(
            $installer->getFkName(
                self::TABLE_ORDER_EMISSION,
                OrderEmissionInterface::ORDER_ID,
                'sales_order',
                'entity_id'
            ),
            $installer->getTable(self::TABLE_ORDER_EMISSION),
            OrderEmissionInterface::ORDER_ID,
            $installer->getTable('sales_order'),
            'entity_id'
        );
    }
}
