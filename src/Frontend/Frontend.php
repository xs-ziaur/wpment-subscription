<?php

namespace Wpmet\WpmetSubscription\Frontend;

use Wpmet\WpmetSubscription\Frontend\Ajax\WmsAjax;

class Frontend
{
    public function __construct()
    {
        new WmsAjax();
    }
}