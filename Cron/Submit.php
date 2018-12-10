<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */
namespace Buzzi\PublishCartAbandonment\Cron;

use Buzzi\PublishCartAbandonment\Model\DataBuilder;

class Submit
{
    /**
     * @var \Buzzi\Publish\Model\Config\Events
     */
    protected $configEvents;

    /**
     * @var \Buzzi\PublishCartAbandonment\Api\CartAbandonmentIndexerInterface
     */
    protected $cartAbandonmentIndexer;

    /**
     * @var \Buzzi\PublishCartAbandonment\Api\CartAbandonmentManagerInterface
     */
    protected $cartAbandonmentManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Buzzi\Publish\Model\Config\Events $configEvents
     * @param \Buzzi\PublishCartAbandonment\Api\CartAbandonmentIndexerInterface $indexer
     * @param \Buzzi\PublishCartAbandonment\Api\CartAbandonmentManagerInterface $manager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Buzzi\Publish\Model\Config\Events $configEvents,
        \Buzzi\PublishCartAbandonment\Api\CartAbandonmentIndexerInterface $indexer,
        \Buzzi\PublishCartAbandonment\Api\CartAbandonmentManagerInterface $manager,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->configEvents = $configEvents;
        $this->cartAbandonmentIndexer = $indexer;
        $this->cartAbandonmentManager = $manager;
        $this->storeManager = $storeManager;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $stores = $this->storeManager->getStores();
        foreach (array_keys($stores) as $storeId) {
            if (!$this->configEvents->isEventEnabled(DataBuilder::EVENT_TYPE, $storeId)) {
                continue;
            }

            $this->cartAbandonmentIndexer->reindex(
                $this->configEvents->getValue(DataBuilder::EVENT_TYPE, 'quote_last_action', $storeId),
                $this->configEvents->isSetFlag(DataBuilder::EVENT_TYPE, 'respect_accepts_marketing', $storeId),
                $this->configEvents->getValue(DataBuilder::EVENT_TYPE, 'quotes_limit', $storeId),
                $this->configEvents->isSetFlag(DataBuilder::EVENT_TYPE, 'resubmission', $storeId),
                $storeId
            );

            $this->cartAbandonmentManager->sendPending($storeId);
        }
    }
}
