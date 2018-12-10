<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */
namespace Buzzi\PublishCartAbandonment\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Buzzi\PublishCartAbandonment\Model\ResourceModel\CartAbandonment as ResourceModelCartAbandonment;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '4.0.0', '<')) {
            $this->setupFingerprint($setup);
        }
    }

    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     * @return void
     */
    private function setupFingerprint(ModuleDataSetupInterface $setup)
    {
        $query = 'UPDATE ' . ResourceModelCartAbandonment::TABLE_NAME . ' as sbca ' .
            'SET fingerprint = md5(CONCAT(sbca.quote_id, (' .
            'SELECT GROUP_CONCAT(sqi.product_id, sqi.qty ORDER BY sqi.product_id ASC) ' .
            'FROM quote_item as sqi WHERE sbca.quote_id = sqi.quote_id GROUP BY sqi.quote_id)))';

        $setup->getConnection()->query($query);
    }
}
