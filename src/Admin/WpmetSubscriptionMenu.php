<?php

namespace Xpeed\WpmetSubscription\Admin;

class WpmetSubscriptionMenu
{
    function __construct()
    {
        add_action('admin_menu', [$this, 'msAdminMenu']);
    }

    public function msAdminMenu()
    {
        $parent_slug = 'wpmet-subscription';
        $capability = 'manage_options';

        add_menu_page(
            __('Wpment Subscription', 'wpmet-subscription'),
            __('Wpmet Subscription', 'wpmet-subscription'),
            $capability,
            $parent_slug,
            [ $this, 'wpmetSubscription'],
            'dashicons-welcome-view-site'
        );

        add_submenu_page(
            $parent_slug,
            __('Master Log', 'wpmet-subscription'),
            __('Master Log', 'wpmet-subscription'),
            $capability,
            $parent_slug,
            [$this, 'masterLog']
        );

        add_submenu_page(
            $parent_slug,
            __('Settings', 'wpmet-subscription'),
            __('Settings', 'wpmet-subscription'),
            $capability,
            'ms-settings',
            [$this, 'settings']
        );

    }

    public function wpmetSubscription(){
        
    }

    /**
     * master log function
     *
     * @return void
     */
    public function masterLog() {
        echo "hello from Master Log";
    }

    /**
     * settings function
     *
     * @return void
     */
    public function settings() {
        echo "hello world from settings";
    }
}
