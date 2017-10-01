<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */
namespace Buzzi\PublishCartAbandonment\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
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
        }
    }

    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @return void
     */
    protected function addErrorMessageField(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable(ResourceModelCartAbandonment::TABLE_NAME),
            CartAbandonmentInterface::ERROR_MESSAGE,
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'Error Message'
            ]
        );
    }
}
