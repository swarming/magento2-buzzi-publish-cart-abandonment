<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */
namespace Buzzi\PublishCartAbandonment\Model\ResourceModel\CartAbandonment;

use Buzzi\PublishCartAbandonment\Api\Data\CartAbandonmentInterface;
use Buzzi\PublishCartAbandonment\Model\CartAbandonment;
use Buzzi\PublishCartAbandonment\Model\ResourceModel\CartAbandonment as ResourceModelCartAbandonment;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(CartAbandonment::class, ResourceModelCartAbandonment::class);
    }

    /**
     * @param int $storeId
     * @return $this
     */
    public function filterStore($storeId)
    {
        $this->addFilter(CartAbandonmentInterface::STORE_ID, $storeId);
        return $this;
    }

    /**
     * @return $this
     */
    public function filterStatusPending()
    {
        $this->addFilter(CartAbandonmentInterface::STATUS, CartAbandonmentInterface::STATUS_PENDING);
        return $this;
    }
}
