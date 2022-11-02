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
                'label' => __('Subscription', 'wpmet-subscription'),
                'labels'          => array(
                    'name'               => __('Subscriptions', 'wpmet-subscription'),
                    'singular_name'      => _x('Subscription', 'singular name', 'wpmet-subscription'),
                    'menu_name'          => _x('Subscriptions', 'admin menu', 'wpmet-subscription'),
                    'name_admin_bar'     => _x('Subscriptions', 'wpmet-subscription'),
                    'all_items'          => __('All subscriptions', 'wpmet-subscription'),
                    'add_new'            => __('Add subscription', 'wpmet-subscription'),
                    'add_new_item'       => __('Add new subscription', 'wpmet-subscription'),
                    'new_item'           => __('New subscription', 'wpmet-subscription'),
                    'edit_item'          => __('Edit subscription', 'wpmet-subscription'),
                    'update_item'        => __('Update subscription', 'wpmet-subscription'),
                    'view_item'          => __('View subscription', 'wpmet-subscription'),
                    'view_items'          => __('View subscriptions', 'wpmet-subscription'),
                    'search_items'       => __('Search subscriptions', 'wpmet-subscription'),
                    'not_found'          => __('No subscription found.', 'wpmet-subscription'),
                    'not_found_in_trash' => __('No subscription found in trash.', 'wpmet-subscription'),
                    'featured_image' => __('Featured image.', 'wpmet-subscription'),
                    'set_featured_image' => __('Set featured image.', 'wpmet-subscription'),
                    'remove_featured_image' => __('Remove featured image.', 'wpmet-subscription'),
                    'use_featured_image' => __('Use featured image.', 'wpmet-subscription'),
                    'insert_into_item' => __('Inser into subscription.', 'wpmet-subscription'),
                    'upload_into_this_item' => __('Upload to this subscription.', 'wpmet-subscription'),
                    'items_list' => __('Subscription list.', 'wpmet-subscription'),
                    'items_list_navigation' => __('Subscription list navigation.', 'wpmet-subscription'),
                    'filter_item_list' => __('Filter Subscription list.', 'wpmet-subscription'),
                ),
                'description'     => __('This is where store subscriptions are stored.', 'wpmet-subscription'),
                'menu_icon'       => 'dashicon-product',
                'supports'        => ['title', 'editor', 'thumbnail', 'excerpt', 'comments', 'revisions', 'custom-fields'],
                'taxonomies'        => [],
                'public'          => true,
                'show_ui'         => true,
                'show_in_menu'    => 'wpmet-subscription',
                'capability_type' => 'post',
                'rewrite'         => false,
                'has_archive'     => false,
                'map_meta_cap'    => true,
                'can_export'    => true,
                // 'capabilities'    => array(
                //     'edit_post'          => 'edit_book', 
                //     'read_post'          => 'read_book', 
                //     'delete_post'        => 'delete_book', 
                //     'edit_posts'         => 'edit_books', 
                //     'edit_others_posts'  => 'edit_others_books', 
                //     'publish_posts'      => 'publish_books',       
                //     'read_private_posts' => 'read_private_books', 
                //     'create_posts'       => 'edit_books', 
                // ),
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
                // 'capabilities'        => array(
                //     'create_posts' => 'do_not_allow',
                // ),
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
                // 'capabilities'    => array(
                //     'create_posts' => 'do_not_allow',
                // ),
            ));
        }
    }

}