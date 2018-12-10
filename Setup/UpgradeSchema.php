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

class UpgradeSchema implements \Magento\Framework\Setup\UpgradeSchemaInterface
{
    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '2.0.0', '<')) {
            $this->addErrorMessageField($setup);
            $this->addCreatedAtField($setup);
        }

        if (version_compare($context->getVersion(), '3.1.0', '<')) {
            $this->addFingerPrintCartAbandonment($setup);
            $this->dropFkKey($setup);
            $this->dropCartAbandonmentIndexes($setup);
            $this->addIndexFkKey($setup);
        }
    }

    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @return void
     */
    private function addErrorMessageField(SchemaSetupInterface $setup)
    {
        $setup->getConnection()
            ->addColumn(
                $setup->getTable(ResourceModelCartAbandonment::TABLE_NAME),
                CartAbandonmentInterface::ERROR_MESSAGE,
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'Error Message'
                ]
            );
    }

    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @return void
     */
    private function addCreatedAtField(SchemaSetupInterface $setup)
    {
        $setup->getConnection()
            ->addColumn(
                $setup->getTable(ResourceModelCartAbandonment::TABLE_NAME),
                CartAbandonmentInterface::CREATED_AT,
                [
                    'type' => Table::TYPE_TIMESTAMP,
                    'nullable' => false,
                    'default' => Table::TIMESTAMP_INIT,
                    'comment' => 'Created At'
                ]
            );
    }

    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @return void
     */
    private function dropCartAbandonmentIndexes(SchemaSetupInterface $setup)
    {
        $setup->getConnection()
            ->dropIndex(
                $setup->getTable(ResourceModelCartAbandonment::TABLE_NAME),
                $setup->getIdxName(
                    ResourceModelCartAbandonment::TABLE_NAME,
                    [CartAbandonmentInterface::STORE_ID],
                    AdapterInterface::INDEX_TYPE_INDEX
                )
            );

        $setup->getConnection()
            ->dropIndex(
                $setup->getTable(ResourceModelCartAbandonment::TABLE_NAME),
                $setup->getIdxName(
                    ResourceModelCartAbandonment::TABLE_NAME,
                    [CartAbandonmentInterface::STATUS],
                    AdapterInterface::INDEX_TYPE_INDEX
                )
            );

        $setup->getConnection()
            ->dropIndex(
                $setup->getTable(ResourceModelCartAbandonment::TABLE_NAME),
                $setup->getIdxName(
                    ResourceModelCartAbandonment::TABLE_NAME,
                    [CartAbandonmentInterface::QUOTE_ID],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                )
            );
    }

    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @return void
     */
    private function dropFkKey(SchemaSetupInterface $setup)
    {
        $setup->getConnection()
            ->dropForeignKey(
                $setup->getTable(ResourceModelCartAbandonment::TABLE_NAME),
                $setup->getFkName(
                    ResourceModelCartAbandonment::TABLE_NAME,
                    CartAbandonmentInterface::QUOTE_ID,
                    $setup->getTable('quote'),
                    'entity_id'
                )
            );
    }

    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @return void
     */
    private function addFingerPrintCartAbandonment(SchemaSetupInterface $setup)
    {
        $setup->getConnection()
            ->addColumn(
                $setup->getTable(ResourceModelCartAbandonment::TABLE_NAME),
                CartAbandonmentInterface::FINGERPRINT,
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => false,
                    'length' => 32,
                    'comment' => 'Quote fingerprint md5 from quoteId, productIds with qty',
                    'after' => CartAbandonmentInterface::QUOTE_ID
                ]
            );
    }

    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @return void
     */
    private function addIndexFkKey(SchemaSetupInterface $setup)
    {
        $setup->getConnection()
            ->addIndex(
                ResourceModelCartAbandonment::TABLE_NAME,
                $setup->getIdxName(
                    ResourceModelCartAbandonment::TABLE_NAME,
                    [CartAbandonmentInterface::FINGERPRINT],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                CartAbandonmentInterface::FINGERPRINT,
                AdapterInterface::INDEX_TYPE_UNIQUE
            );


        $fkName = $setup->getFkName(
            ResourceModelCartAbandonment::TABLE_NAME,
            CartAbandonmentInterface::QUOTE_ID,
            'quote',
            'entity_id'
        );

        $setup->getConnection()
            ->addForeignKey(
                $fkName,
                ResourceModelCartAbandonment::TABLE_NAME,
                CartAbandonmentInterface::QUOTE_ID,
                $setup->getTable('quote'),
                'entity_id',
                Table::ACTION_CASCADE
            );
    }
}
