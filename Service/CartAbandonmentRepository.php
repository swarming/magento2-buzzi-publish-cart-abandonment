<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */
namespace Buzzi\PublishCartAbandonment\Service;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Buzzi\PublishCartAbandonment\Api\Data\CartAbandonmentInterface;

class CartAbandonmentRepository implements \Buzzi\PublishCartAbandonment\Api\CartAbandonmentRepositoryInterface
{
    /**
     * @var \Buzzi\PublishCartAbandonment\Api\Data\CartAbandonmentInterfaceFactory
     */
    protected $cartAbandonmentFactory;

    /**
     * @var \Buzzi\PublishCartAbandonment\Model\ResourceModel\CartAbandonment
     */
    protected $cartAbandonmentResource;

    /**
     * @param \Buzzi\PublishCartAbandonment\Api\Data\CartAbandonmentInterfaceFactory $cartAbandonmentFactory
     * @param \Buzzi\PublishCartAbandonment\Model\ResourceModel\CartAbandonment $cartAbandonmentResource
     */
    public function __construct(
        \Buzzi\PublishCartAbandonment\Api\Data\CartAbandonmentInterfaceFactory $cartAbandonmentFactory,
        \Buzzi\PublishCartAbandonment\Model\ResourceModel\CartAbandonment $cartAbandonmentResource
    ) {
        $this->cartAbandonmentFactory = $cartAbandonmentFactory;
        $this->cartAbandonmentResource = $cartAbandonmentResource;
    }

    /**
     * @param array $data
     * @return \Buzzi\PublishCartAbandonment\Api\Data\CartAbandonmentInterface
     */
    public function getNew(array $data = [])
    {
        return $this->cartAbandonmentFactory->create($data);
    }

    /**
     * @param int $entityId
     * @param bool $donNotCheck
     * @return \Buzzi\PublishCartAbandonment\Api\Data\CartAbandonmentInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($entityId, $donNotCheck = false)
    {
        $cartAbandonment = $this->getNew();
        $this->cartAbandonmentResource->load($cartAbandonment, $entityId);
        if (!$donNotCheck && !$cartAbandonment->getAbandonmentId()) {
            throw new NoSuchEntityException(__('CartAbandonment with id "%1" does not exist.', $cartAbandonment));
        }
        return $cartAbandonment;
    }

    /**
     * @param int $quoteId
     * @param bool $donNotCheck
     * @return \Buzzi\PublishCartAbandonment\Api\Data\CartAbandonmentInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByQuoteId($quoteId, $donNotCheck = false)
    {
        $cartAbandonment = $this->getNew();
        $this->cartAbandonmentResource->load($cartAbandonment, $quoteId, CartAbandonmentInterface::QUOTE_ID);
        if (!$donNotCheck && !$cartAbandonment->getAbandonmentId()) {
            throw new NoSuchEntityException(__('CartAbandonment for quote_id "%1" does not exist.', $cartAbandonment));
        }
        return $cartAbandonment;
    }

    /**
     * @param \Buzzi\PublishCartAbandonment\Api\Data\CartAbandonmentInterface $cartAbandonment
     * @return \Buzzi\PublishCartAbandonment\Api\Data\CartAbandonmentInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(CartAbandonmentInterface $cartAbandonment)
    {
        try {
            $this->cartAbandonmentResource->save($cartAbandonment);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save cartAbandonment: %1', $e->getMessage()));
        }
        return $cartAbandonment;
    }

    /**
     * @param \Buzzi\PublishCartAbandonment\Api\Data\CartAbandonmentInterface $cartAbandonment
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(CartAbandonmentInterface $cartAbandonment)
    {
        try {
            $this->cartAbandonmentResource->delete($cartAbandonment);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete cartAbandonment: %1', $e->getMessage()));
        }
        return true;
    }

    /**
     * @param int $entityId
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteById($entityId)
    {
        return $this->delete($this->getById($entityId));
    }

    /**
     * @param int $quoteId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getQuoteFingerprints($quoteId)
    {
        return $this->cartAbandonmentResource->getQuoteFingerprints($quoteId);
    }

    /**
     * @param string $fingerprint
     * @return \Buzzi\PublishCartAbandonment\Api\Data\CartAbandonmentInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByFingerprint($fingerprint)
    {
        $cartAbandonment = $this->getNew();
        $this->cartAbandonmentResource->load($cartAbandonment, $fingerprint, CartAbandonmentInterface::FINGERPRINT);
        if (!$cartAbandonment->getAbandonmentId()) {
            throw new NoSuchEntityException(
                __('CartAbandonment with fingerprint "%1" does not exist.', $cartAbandonment)
            );
        }
        return $cartAbandonment;
    }
}
