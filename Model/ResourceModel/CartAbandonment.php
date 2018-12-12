<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */
namespace Buzzi\PublishCartAbandonment\Model\ResourceModel;

use Buzzi\PublishCartAbandonment\Api\Data\CartAbandonmentInterface;

class CartAbandonment extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const TABLE_NAME = 'buzzi_publish_cart_abandonment';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, CartAbandonmentInterface::ABANDONMENT_ID);
    }

    /**
     * @param int $quoteId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getQuoteFingerprints($quoteId)
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from($this->getMainTable(), 'fingerprint');
        $select->where('quote_id = :quote_id');

        return $connection->fetchAll($select, ['quote_id' => $quoteId], \Zend_Db::FETCH_COLUMN);
    }
}
