<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */
namespace Buzzi\PublishCartAbandonment\Model;

use Buzzi\PublishCartAbandonment\Model\ResourceModel\CartAbandonment as ResourceModelCartAbandonment;

class CartAbandonment extends \Magento\Framework\Model\AbstractExtensibleModel
    implements \Buzzi\PublishCartAbandonment\Api\Data\CartAbandonmentInterface
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModelCartAbandonment::class);
    }

    /**
     * @param int $abandonmentId
     * @return $this
     */
    public function setAbandonmentId($abandonmentId)
    {
        return $this->setData(self::ABANDONMENT_ID, $abandonmentId);
    }

    /**
     * @return int|null
     */
    public function getAbandonmentId()
    {
        return $this->_getData(self::ABANDONMENT_ID);
    }

    /**
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * @return int|null
     */
    public function getStoreId()
    {
        return $this->_getData(self::STORE_ID);
    }

    /**
     * @param int $quoteId
     * @return $this
     */
    public function setQuoteId($quoteId)
    {
        return $this->setData(self::QUOTE_ID, $quoteId);
    }

    /**
     * @return int|null
     */
    public function getQuoteId()
    {
        return $this->_getData(self::QUOTE_ID);
    }

    /**
     * @param string $fingerprint
     * @return \Buzzi\PublishCartAbandonment\Api\Data\CartAbandonmentInterface
     */
    public function setFingerprint($fingerprint)
    {
        return $this->setData(self::FINGERPRINT, $fingerprint);
    }

    /**
     * @return string|null
     */
    public function getFingerprint()
    {
        return $this->_getData(self::FINGERPRINT);
    }

    /**
     * @param int $sequence
     * @return \Buzzi\PublishCartAbandonment\Api\Data\CartAbandonmentInterface
     */
    public function setSequence($sequence)
    {
        return $this->setData(self::SEQUENCE, $sequence);
    }

    /**
     * @return int
     */
    public function getSequence()
    {
        return (int)$this->_getData(self::SEQUENCE);
    }

    /**
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->_getData(self::CUSTOMER_ID);
    }

    /**
     * @param int $status
     * @return $this
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @return int|null
     */
    public function getStatus()
    {
        return $this->_getData(self::STATUS);
    }

    /**
     * @param string $errorMessage
     * @return $this
     */
    public function setErrorMessage($errorMessage)
    {
        return $this->setData(self::ERROR_MESSAGE, $errorMessage);
    }

    /**
     * @return string|null
     */
    public function getErrorMessage()
    {
        return $this->_getData(self::ERROR_MESSAGE);
    }

    /**
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->_getData(self::CREATED_AT);
    }
}
