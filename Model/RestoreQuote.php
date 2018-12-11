<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

namespace Buzzi\PublishCartAbandonment\Model;

class RestoreQuote
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Buzzi\PublishCartAbandonment\Api\CartAbandonmentRepositoryInterface
     */
    private $abandonmentRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @param \Magento\Customer\Model\Session\Proxy $customerSession
     * @param \Magento\Checkout\Model\Session\Proxy $checkoutSession
     * @param \Buzzi\PublishCartAbandonment\Api\CartAbandonmentRepositoryInterface $abandonmentRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     */
    public function __construct(
        \Magento\Customer\Model\Session\Proxy $customerSession,
        \Magento\Checkout\Model\Session\Proxy $checkoutSession,
        \Buzzi\PublishCartAbandonment\Api\CartAbandonmentRepositoryInterface $abandonmentRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository
    ) {
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->abandonmentRepository = $abandonmentRepository;
        $this->storeManager = $storeManager;
        $this->cartRepository = $cartRepository;
    }

    /**
     * @param $token
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function restore($token)
    {
        if (!$token) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Sorry, we were unable to restore the referenced shopping cart as it does not belong to this account.')
            );
        }

        $abandonment = $this->isAbandonmentItemExist($token);
        if (!$abandonment) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Sorry, we were unable to restore the referenced shopping cart as it does not belong to this account.')
            );
        }

        $quote = $this->loadQuote($abandonment->getQuoteId());
        if (!$quote) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Sorry, we were unable to restore the referenced shopping cart as it does not belong to this account.')
            );
        }

        if (!$this->allowAbandonmentForCustomer($abandonment)
            || !$this->allowForStore($abandonment)
            || !$this->allowQuoteForCustomer($quote)
        ) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Sorry, we were unable to restore the referenced shopping cart as it does not belong to this account.')
            );
        }

        $this->setupCurrentQuote($quote);
    }

    /**
     * @param \Buzzi\PublishCartAbandonment\Api\Data\CartAbandonmentInterface $abandonmentItem
     * @return bool
     */
    private function allowAbandonmentForCustomer($abandonmentItem)
    {
        if ($abandonmentItem->getCustomerId() != $this->customerSession->getCustomerId()) {
            return false;
        }
        return true;
    }

    /**
     * @param \Buzzi\PublishCartAbandonment\Api\Data\CartAbandonmentInterface $abandonmentItem
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function allowForStore($abandonmentItem)
    {
        if ($abandonmentItem->getStoreId() != $this->storeManager->getStore()->getId()) {
            return false;
        }
        return true;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return bool
     */
    private function allowQuoteForCustomer($quote)
    {
        if ($quote->getCustomer()->getId() != $this->customerSession->getCustomerId()) {
            return false;
        }
        return true;
    }

    /**
     * @param string $token
     * @return \Buzzi\PublishCartAbandonment\Api\Data\CartAbandonmentInterface|null
     */
    private function isAbandonmentItemExist($token)
    {
        try {
            $item = $this->abandonmentRepository->getByFingerprint($token);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }

        return $item;
    }

    /**
     * @param int $quoteId
     * @return \Magento\Quote\Api\Data\CartInterface|null
     */
    private function loadQuote($quoteId)
    {
        try {
            /** @var \Magento\Quote\Api\Data\CartInterface $quote */
            $quote = $this->cartRepository->get($quoteId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }

        return $quote;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return void
     */
    private function setupCurrentQuote($quote)
    {
        if (!$quote->getIsActive()) {
            $quote->setIsActive(1);
            $this->cartRepository->save($quote);
        }
        $this->checkoutSession->replaceQuote($quote);
    }
}
