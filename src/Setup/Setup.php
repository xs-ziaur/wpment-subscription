<?php

namespace Wpmet\WpmetSubscription\Setup;

use Wpmet\WpmetSubscription\Model\WpmetProduct;
use Wpmet\WpmetSubscription\Setup\CustomPostType\WpmetPostType;

class Setup
{
    public function __construct()
    {
        $this->initializeSetup();
    }

    public function initializeSetup()
    {
        new WpmetPostType();
    }
}