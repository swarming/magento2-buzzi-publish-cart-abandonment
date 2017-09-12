<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */
namespace Buzzi\PublishCartAbandonment\Api;

interface CartAbandonmentManagerInterface
{
    /**
     * @param int|null $storeId
     * @return void
     */
    public function sendPending($storeId = null);
}
