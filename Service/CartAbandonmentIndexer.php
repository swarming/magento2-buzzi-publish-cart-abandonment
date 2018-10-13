<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */
namespace Buzzi\PublishCartAbandonment\Service;

use Buzzi\PublishCartAbandonment\Api\Data\CartAbandonmentInterface;
use Magento\Customer\Model\Customer;
use Buzzi\Publish\Helper\ExceptsMarketing;

class CartAbandonmentIndexer implements \Buzzi\PublishCartAbandonment\Api\CartAbandonmentIndexerInterface
{
    /**
     * @var \Magento\Customer\Model\Visitor
     */
    private $visitorModel;

    /**
     * @var \Magento\Reports\Model\ResourceModel\Quote\CollectionFactory
     */
    private $quoteCollectionFactory;

    /**
     * @var \Buzzi\PublishCartAbandonment\Api\CartAbandonmentRepositoryInterface
     */
    private $cartAbandonmentRepository;

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @param \Magento\Customer\Model\Visitor $visitorModel
     * @param \Magento\Reports\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory
     * @param \Buzzi\PublishCartAbandonment\Api\CartAbandonmentRepositoryInterface $cartAbandonmentRepository
     * @param \Magento\Eav\Model\Config|null $eavConfig
     */
    public function __construct(
        \Magento\Customer\Model\Visitor $visitorModel,
        \Magento\Reports\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory,
        \Buzzi\PublishCartAbandonment\Api\CartAbandonmentRepositoryInterface $cartAbandonmentRepository,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->visitorModel = $visitorModel;
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->cartAbandonmentRepository = $cartAbandonmentRepository;
        $this->eavConfig = $eavConfig;
    }

    /**
     * @param int $quoteLastActionDays
     * @param bool $isRespectAcceptsMarketing
     * @param int|null $storeId
     * @return void
     */
    public function reindex($quoteLastActionDays = 1, $isRespectAcceptsMarketing = false, $storeId = null)
    {
        $quoteCollection = $this->quoteCollectionFactory->create();
        $this->prepareFilters($quoteCollection, $quoteLastActionDays, $isRespectAcceptsMarketing, $storeId);

        /** @var \Magento\Quote\Model\Quote $quote */
        foreach ($quoteCollection as $quote) {
            $cartAbandonment = $this->cartAbandonmentRepository->getByQuoteId($quote->getId(), true);
            if ($cartAbandonment->getAbandonmentId() && $cartAbandonment->getCreatedAt() > $quote->getUpdatedAt()) {
                continue;
            }
            $cartAbandonment->setStoreId($quote->getStoreId());
            $cartAbandonment->setQuoteId($quote->getId());
            $cartAbandonment->setCustomerId($quote->getCustomerId());
            $cartAbandonment->setStatus(CartAbandonmentInterface::STATUS_PENDING);
            $cartAbandonment->setErrorMessage('');
            $cartAbandonment->setCreatedAt($quoteCollection->getConnection()->formatDate(true));
            $this->cartAbandonmentRepository->save($cartAbandonment);
        }
    }

    /**
     * @param \Magento\Reports\Model\ResourceModel\Quote\Collection $quoteCollection
     * @param int $quoteLastActionDays
     * @param bool $isRespectAcceptsMarketing
     * @param int|null $storeId
     * @return void
     */
    private function prepareFilters($quoteCollection, $quoteLastActionDays, $isRespectAcceptsMarketing = false, $storeId = null)
    {
        $quoteCollection->prepareForAbandonedReport(null);
        $quoteCollection->addFieldToFilter(
            ['main_table.updated_at', 'main_table.updated_at'],
            [
                ['gteq' => new \Zend_Db_Expr(sprintf('DATE_SUB(NOW(), INTERVAL %d DAY)', $quoteLastActionDays))],
                ['eq' => '0000-00-00 00:00:00']
            ]
        );
        if ($storeId) {
            $quoteCollection->addFieldToFilter('main_table.store_id', ['eq' => $storeId]);
        }
        $this->filterOnlineCustomers($quoteCollection);

        if ($isRespectAcceptsMarketing) {
            $this->filterNotAllowedCustomers($quoteCollection);
        }
    }

    /**
     * @param \Magento\Reports\Model\ResourceModel\Quote\Collection $quoteCollection
     * @return void
     */
    private function filterOnlineCustomers($quoteCollection)
    {
        $quoteCollection->getSelect()
            ->joinInner(
                ['visitor' => $quoteCollection->getTable('customer_visitor')],
                'visitor.customer_id = main_table.customer_id',
                ['last_action' => 'max(visitor.last_visit_at)']
            )
            ->group('main_table.customer_id')
            ->having('last_action < DATE_SUB(NOW(), INTERVAL ? MINUTE)', $this->visitorModel->getOnlineInterval());
    }

    /**
     * @param \Magento\Reports\Model\ResourceModel\Quote\Collection $quoteCollection
     * @return void
     */
    private function filterNotAllowedCustomers($quoteCollection)
    {
        $exceptsMarketingAttribute = $this->eavConfig->getAttribute(Customer::ENTITY, ExceptsMarketing::CUSTOMER_ATTR);

        $quoteCollection->getSelect()->joinLeft(
            'customer_entity_int',
            sprintf(
                'customer_entity_int.entity_id=main_table.customer_id and customer_entity_int.attribute_id=%d',
                $exceptsMarketingAttribute->getId()
            ),
            []
        );

        $fields = ['customer_entity_int.value'];
        $conditions = [['eq' => '1']];

        if ($exceptsMarketingAttribute->getDefaultValue()) {
            $fields[] = 'customer_entity_int.value';
            $conditions[] = ['null' => null];
        }

        $quoteCollection->addFieldToFilter($fields, $conditions);
    }
}
