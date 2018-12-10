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
     * @param \Magento\Framework\Session\SessionManager $customerSession
     * @param \Magento\Framework\Session\SessionManager $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Buzzi\PublishCartAbandonment\Api\CartAbandonmentRepositoryInterface $abandonmentRepository,
        \Magento\Framework\Session\SessionManager $customerSession,
        \Magento\Framework\Session\SessionManager $checkoutSession,
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

        $token = $this->getRequest()->getParam('token');

        if (!$token) {
            return $resultRedirect->setPath('/');
        }

        $abandonmentItem = $this->validateToken($token);
        if (!$abandonmentItem) {
            $this->messageManager
                ->addNotice(__('Sorry, we were unable to restore the referenced shopping cart as it does not belong to this account.'));
            return $resultRedirect->setPath('checkout/cart');
        }

        try {
            /** @var \Magento\Quote\Api\Data\CartInterface $quote */
            $quote = $this->cartRepository->get($abandonmentItem->getQuoteId());
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->messageManager
                ->addNotice(__('Sorry, we were unable to restore the referenced shopping cart as it does not belong to this account.'));
            return $resultRedirect->setPath('checkout/cart');
        }

        if ($abandonmentItem->getCustomerId() != $this->customerSession->getCustomerId() ||
            $abandonmentItem->getStoreId() != $this->storeManager->getStore()->getId() ||
            $quote->getCustomer()->getId() != $this->customerSession->getCustomerId()
        ) {
            $this->messageManager
                ->addNotice(__('Sorry, we were unable to restore the referenced shopping cart as it does not belong to this account.'));
            return $resultRedirect->setPath('checkout/cart');
        }

        $this->setupCurrentQuote($quote);

        $this->messageManager->addSuccess(__('You shopping cart has been restored successfully.'));

        return $resultRedirect->setPath('checkout/cart');
    }

    /**
     * @param string $token
     * @return bool|\Buzzi\PublishCartAbandonment\Api\Data\CartAbandonmentInterface
     */
    private function validateToken($token)
    {
        try {
            $item = $this->abandonmentRepository->getByFingerprint($token);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return false;
        }

        return $item;
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
