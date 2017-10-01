<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */
namespace Buzzi\PublishCartAbandonment\Service;

use Buzzi\PublishCartAbandonment\Api\Data\CartAbandonmentInterface;

class CartAbandonmentIndexer implements \Buzzi\PublishCartAbandonment\Api\CartAbandonmentIndexerInterface
{
    /**
     * @var \Magento\Customer\Model\Visitor
     */
    protected $visitorModel;

    /**
     * @var \Magento\Reports\Model\ResourceModel\Quote\CollectionFactory
     */
    protected $quoteCollectionFactory;

    /**
     * @var \Buzzi\PublishCartAbandonment\Api\CartAbandonmentRepositoryInterface
     */
    protected $cartAbandonmentRepository;

    /**
     * @param \Magento\Customer\Model\Visitor $visitorModel
     * @param \Magento\Reports\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory
     * @param \Buzzi\PublishCartAbandonment\Api\CartAbandonmentRepositoryInterface $cartAbandonmentRepository
     */
    public function __construct(
        \Magento\Customer\Model\Visitor $visitorModel,
        \Magento\Reports\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory,
        \Buzzi\PublishCartAbandonment\Api\CartAbandonmentRepositoryInterface $cartAbandonmentRepository
    ) {
        $this->visitorModel = $visitorModel;
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->cartAbandonmentRepository = $cartAbandonmentRepository;
    }

    /**
     * @param int $quoteLastActionDays
     * @param int|null $storeId
     * @return void
     */
    public function reindex($quoteLastActionDays = 1, $storeId = null)
    {
        $quoteCollection = $this->quoteCollectionFactory->create();
        $this->prepareFilters($quoteCollection, $quoteLastActionDays, $storeId);

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
     * @param int|null $storeId
     * @return void
     */
    protected function prepareFilters($quoteCollection, $quoteLastActionDays, $storeId = null)
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
    }

    /**
     * @param \Magento\Reports\Model\ResourceModel\Quote\Collection $quoteCollection
     * @return void
     */
    protected function filterOnlineCustomers($quoteCollection)
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
}
