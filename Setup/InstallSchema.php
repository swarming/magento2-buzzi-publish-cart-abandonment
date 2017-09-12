<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */
namespace Buzzi\PublishCartAbandonment\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;

use Buzzi\PublishCartAbandonment\Model\ResourceModel\CartAbandonment as ResourceModelCartAbandonment;
use Buzzi\PublishCartAbandonment\Api\Data\CartAbandonmentInterface;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->createCartAbandonmentTable($setup);

        $setup->endSetup();
    }

    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @return void
     */
    protected function createCartAbandonmentTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()->newTable(
            $setup->getTable(ResourceModelCartAbandonment::TABLE_NAME)
        )->addColumn(
            CartAbandonmentInterface::ABANDONMENT_ID,
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'primary' => true, 'unsigned' => true, 'nullable' => false],
            'Id'
        )->addColumn(
            CartAbandonmentInterface::STORE_ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Website ID'
        )->addColumn(
            CartAbandonmentInterface::QUOTE_ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Quote ID'
        )->addColumn(
            CartAbandonmentInterface::CUSTOMER_ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Customer ID'
        )->addColumn(
            CartAbandonmentInterface::STATUS,
            Table::TYPE_TEXT,
            10,
            ['nullable' => false, 'default' => CartAbandonmentInterface::STATUS_PENDING],
            'Status'
        )->addForeignKey(
            $setup->getFkName(ResourceModelCartAbandonment::TABLE_NAME, CartAbandonmentInterface::QUOTE_ID, 'quote', 'entity_id'),
            CartAbandonmentInterface::QUOTE_ID,
            $setup->getTable('quote'),
            'entity_id',
            Table::ACTION_CASCADE
        )->addIndex(
            $setup->getIdxName(ResourceModelCartAbandonment::TABLE_NAME, [CartAbandonmentInterface::STORE_ID], AdapterInterface::INDEX_TYPE_INDEX),
            [CartAbandonmentInterface::STORE_ID],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addIndex(
            $setup->getIdxName(ResourceModelCartAbandonment::TABLE_NAME, [CartAbandonmentInterface::QUOTE_ID], AdapterInterface::INDEX_TYPE_UNIQUE),
            [CartAbandonmentInterface::QUOTE_ID],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $setup->getIdxName(ResourceModelCartAbandonment::TABLE_NAME, [CartAbandonmentInterface::STATUS], AdapterInterface::INDEX_TYPE_INDEX),
            [CartAbandonmentInterface::STATUS],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->setComment(
            'Buzzi Publish Cart Abandonment'
        );
        $setup->getConnection()->createTable($table);
    }
}
