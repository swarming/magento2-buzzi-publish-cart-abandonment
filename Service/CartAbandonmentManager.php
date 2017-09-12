<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */
namespace Buzzi\PublishCartAbandonment\Service;

use Buzzi\PublishCartAbandonment\Api\Data\CartAbandonmentInterface;
use Buzzi\PublishCartAbandonment\Model\DataBuilder;

class CartAbandonmentManager implements \Buzzi\PublishCartAbandonment\Api\CartAbandonmentManagerInterface
{
    /**
     * @var \Buzzi\PublishCartAbandonment\Model\DataBuilder
     */
    protected $dataBuilder;

    /**
     * @var \Buzzi\Publish\Api\QueueInterface
     */
    protected $queue;

    /**
     * @var \Buzzi\PublishCartAbandonment\Api\CartAbandonmentRepositoryInterface
     */
    protected $cartAbandonmentRepository;

    /**
     * @var \Buzzi\PublishCartAbandonment\Model\ResourceModel\CartAbandonment\CollectionFactory
     */
    protected $cartAbandonmentCollectionFactory;

    /**
     * @param \Buzzi\PublishCartAbandonment\Model\DataBuilder $dataBuilder
     * @param \Buzzi\Publish\Api\QueueInterface $queue
     * @param \Buzzi\PublishCartAbandonment\Api\CartAbandonmentRepositoryInterface $cartAbandonmentRepository
     * @param \Buzzi\PublishCartAbandonment\Model\ResourceModel\CartAbandonment\CollectionFactory $cartAbandonmentCollectionFactory
     */
    public function __construct(
        \Buzzi\PublishCartAbandonment\Model\DataBuilder $dataBuilder,
        \Buzzi\Publish\Api\QueueInterface $queue,
        \Buzzi\PublishCartAbandonment\Api\CartAbandonmentRepositoryInterface $cartAbandonmentRepository,
        \Buzzi\PublishCartAbandonment\Model\ResourceModel\CartAbandonment\CollectionFactory $cartAbandonmentCollectionFactory
    ) {
        $this->dataBuilder = $dataBuilder;
        $this->queue = $queue;
        $this->cartAbandonmentRepository = $cartAbandonmentRepository;
        $this->cartAbandonmentCollectionFactory = $cartAbandonmentCollectionFactory;
    }

    /**
     * @param int|null $storeId
     * @return void
     */
    public function sendPending($storeId = null)
    {
        $cartAbandonmentCollection = $this->cartAbandonmentCollectionFactory->create();
        $cartAbandonmentCollection->filterStatusPending();
        if ($storeId) {
            $cartAbandonmentCollection->filterStore($storeId);
        }

        /** @var \Buzzi\PublishCartAbandonment\Api\Data\CartAbandonmentInterface $cartAbandonment */
        foreach ($cartAbandonmentCollection as $cartAbandonment) {
            $this->send($cartAbandonment);
            $cartAbandonment->setStatus(CartAbandonmentInterface::STATUS_DONE);
            $this->cartAbandonmentRepository->save($cartAbandonment);
        }
    }

    /**
     * @param \Buzzi\PublishCartAbandonment\Api\Data\CartAbandonmentInterface $cartAbandonment
     * @return void
     */
    protected function send(CartAbandonmentInterface $cartAbandonment)
    {
        try {
            $payload = $this->dataBuilder->getPayload($cartAbandonment);
            $this->queue->send(DataBuilder::EVENT_TYPE, $payload, $cartAbandonment->getStoreId());
        } catch (\Exception $e) {
            // Do nothing
        }
    }
}
