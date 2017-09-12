<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */
namespace Buzzi\PublishCartAbandonment\Api;

interface CartAbandonmentIndexerInterface
{
    /**
     * @param int $quoteLastActionDays
     * @param int|null $storeId
     * @return void
     */
    public function reindex($quoteLastActionDays = 1, $storeId = null);
}
