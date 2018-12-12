<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

namespace Buzzi\PublishCartAbandonment\Controller\Quote;

class Restore extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \Buzzi\PublishCartAbandonment\Model\QuoteRestorer
     */
    private $quoteRestorer;

    /**
     * @param \Buzzi\PublishCartAbandonment\Model\QuoteRestorer $quoteRestorer
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Buzzi\PublishCartAbandonment\Model\QuoteRestorer $quoteRestorer,
        \Magento\Framework\App\Action\Context $context
    ) {
        parent::__construct($context);
        $this->quoteRestorer = $quoteRestorer;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $token = $this->getRequest()->getParam('token');

        try {
            $this->quoteRestorer->restore($token);
            $this->messageManager->addSuccessMessage(__('You shopping cart has been restored successfully.'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('En error occurred while restoring the quote.'));
        }

        return $resultRedirect->setPath('checkout/cart');
    }
}
