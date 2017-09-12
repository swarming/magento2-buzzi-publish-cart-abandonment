<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */
namespace Buzzi\PublishCartAbandonment\Api;

use Buzzi\PublishCartAbandonment\Api\Data\CartAbandonmentInterface;

interface CartAbandonmentRepositoryInterface
{
    /**
     * @param array $data
     * @return \Buzzi\PublishCartAbandonment\Api\Data\CartAbandonmentInterface
     */
    public function getNew(array $data = []);

    /**
     * @param int $entityId
     * @param bool $donNotCheck
     * @return \Buzzi\PublishCartAbandonment\Api\Data\CartAbandonmentInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($entityId, $donNotCheck = false);

    /**
     * @param int $quoteId
     * @param bool $donNotCheck
     * @return \Buzzi\PublishCartAbandonment\Api\Data\CartAbandonmentInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByQuoteId($quoteId, $donNotCheck = false);

    /**
     * @param \Buzzi\PublishCartAbandonment\Api\Data\CartAbandonmentInterface $cartAbandonment
     * @return \Buzzi\PublishCartAbandonment\Api\Data\CartAbandonmentInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(CartAbandonmentInterface $cartAbandonment);

    /**
     * @param \Buzzi\PublishCartAbandonment\Api\Data\CartAbandonmentInterface $cartAbandonment
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(CartAbandonmentInterface $cartAbandonment);

    /**
     * @param int $entityId
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteById($entityId);
}
