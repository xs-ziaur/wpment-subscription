<?php

namespace Wpmet\WpmetSubscription;

use Wpmet\WpmetSubscription\Setup\Setup;

class Admin
{

    public function __construct()
    {
        new Admin\WpmetSubscriptionMenu();
        new Setup();
    }

    /**
     * actions handler for formm
     *
     * @return void
     */
    public function dispatch_actions($addressBook)
    {
        
        add_action('admin_init', [$addressBook, 'form_handler']);
    }
}
