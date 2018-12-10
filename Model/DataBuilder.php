<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */
namespace Buzzi\PublishCartAbandonment\Model;

use Buzzi\PublishCartAbandonment\Api\Data\CartAbandonmentInterface;
use Magento\Framework\DataObject;

class DataBuilder
{
    const EVENT_TYPE = 'buzzi.ecommerce.cart-abandonment';

    /**
     * @var \Buzzi\Publish\Helper\DataBuilder\Base
     */
    protected $dataBuilderBase;

    /**
     * @var \Buzzi\Publish\Helper\DataBuilder\Cart
     */
    protected $dataBuilderCart;

    /**
     * @var \Buzzi\Publish\Helper\DataBuilder\Customer
     */
    protected $dataBuilderCustomer;

    /**
     * @var \Buzzi\Publish\Helper\DataBuilder\Address
     */
    protected $dataBuilderAddress;

    /**
     * @var \Magento\Customer\Model\CustomerRegistry
     */
    protected $customerRegistry;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventDispatcher;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param \Buzzi\Publish\Helper\DataBuilder\Base $dataBuilderBase
     * @param \Buzzi\Publish\Helper\DataBuilder\Cart $dataBuilderCart
     * @param \Buzzi\Publish\Helper\DataBuilder\Customer $dataBuilderCustomer
     * @param \Buzzi\Publish\Helper\DataBuilder\Address $dataBuilderAddress
     * @param \Magento\Customer\Model\CustomerRegistry $customerRegistry
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param \Magento\Framework\Event\ManagerInterface $eventDispatcher
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(
        \Buzzi\Publish\Helper\DataBuilder\Base $dataBuilderBase,
        \Buzzi\Publish\Helper\DataBuilder\Cart $dataBuilderCart,
        \Buzzi\Publish\Helper\DataBuilder\Customer $dataBuilderCustomer,
        \Buzzi\Publish\Helper\DataBuilder\Address $dataBuilderAddress,
        \Magento\Customer\Model\CustomerRegistry $customerRegistry,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Framework\Event\ManagerInterface $eventDispatcher,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->dataBuilderBase = $dataBuilderBase;
        $this->dataBuilderCart = $dataBuilderCart;
        $this->dataBuilderCustomer = $dataBuilderCustomer;
        $this->dataBuilderAddress = $dataBuilderAddress;
        $this->customerRegistry = $customerRegistry;
        $this->cartRepository = $cartRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param \Buzzi\PublishCartAbandonment\Api\Data\CartAbandonmentInterface $cartAbandonment
     * @return mixed[]
     */
    public function getPayload(CartAbandonmentInterface $cartAbandonment)
    {
        $quote = $this->cartRepository->getActive($cartAbandonment->getQuoteId(), ['*']);

        $payload = $this->dataBuilderBase->initBaseData(self::EVENT_TYPE);
        $payload['customer'] = $this->getCustomerData($quote);
        $payload['cart'] = $this->dataBuilderCart->getCartData($quote);
        $payload['cart']['cart_items'] = $this->dataBuilderCart->getCartItemsData($quote);
        $payload['cart']['checkout_url'] = $this->prepareStoreLink($cartAbandonment);

        $billingAddress = $this->dataBuilderAddress->getBillingAddressesFromQuote($quote);
        if ($billingAddress) {
            $payload['cart']['billing_address'] = $billingAddress;
        }

        $shippingAddress = $this->dataBuilderAddress->getShippingAddressesFromQuote($quote);
        if ($shippingAddress) {
            $payload['cart']['shipping_address'] = $shippingAddress;
        }

        $transport = new DataObject(['abandonment' => $cartAbandonment, 'payload' => $payload]);
        $this->eventDispatcher->dispatch('buzzi_publish_cart_abandonment_payload', ['transport' => $transport]);

        return (array)$transport->getData('payload');
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return array
     */
    protected function getCustomerData($quote)
    {
        $customerData = [];
        if ($quote->getCustomer()->getId()) {
            $customer = $this->customerRegistry->retrieve($quote->getCustomer()->getId());
            $customerData = $this->dataBuilderCustomer->getCustomerData($customer);
        }
        return $customerData;
    }

    /**
     * @param \Buzzi\PublishCartAbandonment\Api\Data\CartAbandonmentInterface $cartAbandonment
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function prepareStoreLink(CartAbandonmentInterface $cartAbandonment)
    {
        return $this->urlBuilder->setScope($cartAbandonment->getStoreId())
            ->getUrl('cart_abandonment/quote/restore', ['token' => $cartAbandonment->getFingerprint()]);
    }
}
