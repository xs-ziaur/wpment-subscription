<?php

namespace Wpmet\WpmetSubscription;


class Admin
{

    public function __construct()
    {
        new Admin\WpmetSubscriptionMenu();
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
