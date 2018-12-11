<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

namespace Buzzi\PublishCartAbandonment\Controller\Quote;

class Restore extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \Buzzi\PublishCartAbandonment\Model\RestoreQuote
     */
    private $restoreQuote;

    /**
     * @param \Buzzi\PublishCartAbandonment\Model\RestoreQuote $restoreQuote
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Buzzi\PublishCartAbandonment\Model\RestoreQuote $restoreQuote,
        \Magento\Framework\App\Action\Context $context
    ) {
        parent::__construct($context);
        $this->restoreQuote = $restoreQuote;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $token = $this->getRequest()->getParam('token');

        try {
            $this->restoreQuote->restore($token);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setPath('checkout/cart');
        }

        $this->messageManager->addSuccessMessage(__('You shopping cart has been restored successfully.'));
        return $resultRedirect->setPath('checkout/cart');
    }
}
