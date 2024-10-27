<?php

namespace App\Telegraph\Event;

use DefStudio\Telegraph\Handlers\WebhookHandler;

class TelegraphEventHandler extends WebhookHandler
{
    public function __construct(
        protected WebhookHandler $handler
    )
    {

    }


}
