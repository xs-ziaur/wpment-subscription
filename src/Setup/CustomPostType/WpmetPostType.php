<?php

namespace Wpmet\WpmetSubscription\Setup\CustomPostType;

/**
 * Post Types
 *
 * Registers post types
 *
 * @class WpmetPostType
 * @package Class
 */
class WpmetPostType
{

    /**
     * Init WpmetPostType.
     */
    public function __construct()
    {
        add_action('init', [$this, 'registerPostTypes']);
    }

    /**
     * Register core post types.
     */
    public function registerPostTypes()
    {
        if (!post_type_exists('wpmet-subscription')) {
            register_post_type('wpmet-subscription', array(
                'labels'          => array(
                    'name'               => __('Subscriptions', 'wpmet-subscription'),
                    'singular_name'      => _x('Subscription', 'singular name', 'wpmet-subscription'),
                    'menu_name'          => _x('Subscriptions', 'admin menu', 'wpmet-subscription'),
                    'add_new'            => __('Add subscription', 'wpmet-subscription'),
                    'add_new_item'       => __('Add new subscription', 'wpmet-subscription'),
                    'new_item'           => __('New subscription', 'wpmet-subscription'),
                    'edit_item'          => __('Edit subscription', 'wpmet-subscription'),
                    'view_item'          => __('View subscription', 'wpmet-subscription'),
                    'search_items'       => __('Search subscriptions', 'wpmet-subscription'),
                    'not_found'          => __('No subscription found.', 'wpmet-subscription'),
                    'not_found_in_trash' => __('No subscription found in trash.', 'wpmet-subscription'),
                ),
                'description'     => __('This is where store subscriptions are stored.', 'wpmet-subscription'),
                'public'          => true,
                'show_ui'         => true,
                'capability_type' => 'post',
                'show_in_menu'    => 'wpmet-subscription',
                'rewrite'         => false,
                'has_archive'     => false,
                'supports'        => false,
                'map_meta_cap'    => true,
                'capabilities'    => array(
                    'create_posts' => 'do_not_allow',
                ),
            ));
        }

        if (!post_type_exists('wpmet-cron-events')) {
            register_post_type('wpmet-cron-events', array(
                'labels'              => array(
                    'name'         => __('Cron events', 'wpmet-subscription'),
                    'menu_name'    => _x('Cron events', 'admin menu', 'wpmet-subscription'),
                    'search_items' => __('Search cron events', 'wpmet-subscription'),
                    'not_found'    => __('No cron event found.', 'wpmet-subscription'),
                ),
                'description'         => __('This is where scheduled cron events are stored.', 'wpmet-subscription'),
                'public'              => false,
                'capability_type'     => 'post',
                /**
                 * Need to show scheduled crons menu?
                 *
                 * @since 1.0
                 */
                'show_ui'             => true,
                'publicly_queryable'  => false,
                'exclude_from_search' => true,
                'show_in_menu'        => 'wpmet-subscription',
                'hierarchical'        => false,
                'show_in_nav_menus'   => true,
                'rewrite'             => false,
                'query_var'           => false,
                'supports'            => false,
                'has_archive'         => false,
                'map_meta_cap'        => true,
                'capabilities'        => array(
                    'create_posts' => 'do_not_allow',
                ),
            ));
        }

        if (!post_type_exists('wpmet-master-log')) {
            register_post_type('wpmet-master-log', array(
                'labels'          => array(
                    'name'         => __('Master log', 'wpmet-subscription'),
                    'menu_name'    => _x('Master log', 'admin menu', 'wpmet-subscription'),
                    'search_items' => __('Search log', 'wpmet-subscription'),
                    'not_found'    => __('No logs found.', 'wpmet-subscription'),
                ),
                'description'     => __('This is where subscription logs are stored.', 'wpmet-subscription'),
                'public'          => false,
                'show_ui'         => true,
                'capability_type' => 'post',
                'show_in_menu'    => 'wpmet-subscription',
                'rewrite'         => false,
                'has_archive'     => false,
                'supports'        => false,
                'map_meta_cap'    => true,
                'capabilities'    => array(
                    'create_posts' => 'do_not_allow',
                ),
            ));
        }
    }

}
