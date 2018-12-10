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
    const FINGERPRINT ='fingerprint';
    const CUSTOMER_ID = 'customer_id';
    const STATUS = 'status';
    const ERROR_MESSAGE = 'error_message';
    const CREATED_AT = 'created_at';

    /**
     * @param int $abandonmentId
     * @return \Buzzi\PublishCartAbandonment\Api\Data\CartAbandonmentInterface
     */
    public function setAbandonmentId($abandonmentId);

    /**
     * @return int|null
     */
    public function getAbandonmentId();

    /**
     * @param int $storeId
     * @return \Buzzi\PublishCartAbandonment\Api\Data\CartAbandonmentInterface
     */
    public function setStoreId($storeId);

    /**
     * @return int|null
     */
    public function getStoreId();

    /**
     * @param int $quoteId
     * @return \Buzzi\PublishCartAbandonment\Api\Data\CartAbandonmentInterface
     */
    public function setQuoteId($quoteId);

    /**
     * @param string $fingerprint
     * @return \Buzzi\PublishCartAbandonment\Api\Data\CartAbandonmentInterface
     */
    public function setFingerprint($fingerprint);

    /**
     * @return int|null
     */
    public function getQuoteId();

    /**
     * @return string|null
     */
    public function getFingerprint();

    /**
     * @param int $customerId
     * @return \Buzzi\PublishCartAbandonment\Api\Data\CartAbandonmentInterface
     */
    public function setCustomerId($customerId);

    /**
     * @return int|null
     */
    public function getCustomerId();

    /**
     * @param int $status
     * @return \Buzzi\PublishCartAbandonment\Api\Data\CartAbandonmentInterface
     */
    public function setStatus($status);

    /**
     * @return int|null
     */
    public function getStatus();

    /**
     * @param string $errorMessage
     * @return \Buzzi\PublishCartAbandonment\Api\Data\CartAbandonmentInterface
     */
    public function setErrorMessage($errorMessage);

    /**
     * @return string|null
     */
    public function getErrorMessage();

    /**
     * @param string $createdAt
     * @return \Buzzi\PublishCartAbandonment\Api\Data\CartAbandonmentInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * @return string|null
     */
    public function getCreatedAt();
}
