<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */
namespace Buzzi\PublishCartAbandonment\Controller\Quote;

class Restore extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \Buzzi\PublishCartAbandonment\Api\CartAbandonmentRepositoryInterface
     */
    private $abandonmentRepository;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @param \Buzzi\PublishCartAbandonment\Api\CartAbandonmentRepositoryInterface $abandonmentRepository
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Buzzi\PublishCartAbandonment\Api\CartAbandonmentRepositoryInterface $abandonmentRepository,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Framework\App\Action\Context $context
    ) {
        parent::__construct($context);
        $this->abandonmentRepository = $abandonmentRepository;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->storeManager = $storeManager;
        $this->cartRepository = $cartRepository;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $token = $this->getRequest()
            ->getParam('token');

        if (!$token) {
            return $resultRedirect->setPath('/');
        }

        $abandonment = $this->isAbandonmentItemExist($token);
        if (!$abandonment) {
            $this->messageManager
                ->addNoticeMessage(
                    __('Sorry, we were unable to restore the referenced shopping cart as it does not belong to this account.')
                );
            return $resultRedirect->setPath('checkout/cart');
        }

        $quote = $this->loadQuote($abandonment->getQuoteId());
        if (!$quote) {
            $this->messageManager
                ->addNoticeMessage(
                    __('Sorry, we were unable to restore the referenced shopping cart as it does not belong to this account.')
                );
            return $resultRedirect->setPath('checkout/cart');
        }

        if (!$this->allowAbandonmentForCustomer($abandonment)
            || !$this->allowForStore($abandonment)
            || !$this->allowQuoteForCustomer($quote)
        ) {
            $this->messageManager
                ->addNoticeMessage(
                    __('Sorry, we were unable to restore the referenced shopping cart as it does not belong to this account.')
                );
            return $resultRedirect->setPath('checkout/cart');
        }

        $this->setupCurrentQuote($quote);

        $this->messageManager->addSuccessMessage(__('You shopping cart has been restored successfully.'));

        return $resultRedirect->setPath('checkout/cart');
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
        if ($abandonmentItem->getStoreId() != $this->storeManager->getStore()
                ->getId()) {
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
        if ($quote->getCustomer()
                ->getId() != $this->customerSession->getCustomerId()) {
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
