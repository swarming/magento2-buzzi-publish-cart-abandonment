<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */
namespace Buzzi\PublishCartAbandonment\Api\Data;

interface CartAbandonmentInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const STATUS_PENDING = 'pending';
    const STATUS_DONE = 'done';
    const STATUS_FAIL = 'fail';

    const ABANDONMENT_ID = 'abandonment_id';
    const STORE_ID = 'store_id';
    const QUOTE_ID  = 'quote_id';
    const CUSTOMER_ID = 'customer_id';
    const STATUS = 'status';
    const ERROR_MESSAGE = 'error_message';
    const CREATED_AT = 'created_at';

    /**
     * @param int $abandonmentId
     * @return $this
     */
    public function setAbandonmentId($abandonmentId);

    /**
     * @return int|null
     */
    public function getAbandonmentId();

    /**
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * @return int|null
     */
    public function getStoreId();

    /**
     * @param int $quoteId
     * @return $this
     */
    public function setQuoteId($quoteId);

    /**
     * @return int|null
     */
    public function getQuoteId();

    /**
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId);

    /**
     * @return int|null
     */
    public function getCustomerId();

    /**
     * @param int $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * @return int|null
     */
    public function getStatus();

    /**
     * @param string $errorMessage
     * @return $this
     */
    public function setErrorMessage($errorMessage);

    /**
     * @return string|null
     */
    public function getErrorMessage();

    /**
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * @return string|null
     */
    public function getCreatedAt();
}
