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
}
