<?php

namespace Wpmet\WpmetSubscription\Frontend\Ajax;

class WmsAjax
{
    /**
     * Wpmet Subscription Ajax
     */
    public function __construct()
    {
        //Get Ajax Events.
		$ajax_events = array(
			'add_subscription_note'                             => false,
			'delete_subscription_note'                          => false,
			'get_subscribed_optional_plans_by_user'             => true,
			'subscriber_request'                                => false,
			'cancel_request'                                    => false,
			'checkout_order_subscription'                       => true,
			'get_subscription_variation_attributes_upon_switch' => false,
			'save_swapped_subscription_variation'               => false,
			'init_data_export'                                  => false,
			'handle_exported_data'                              => false,
			'bulk_update_products'                              => false,
			'get_subscription_as_regular_product_row'           => false,
			'json_search_subscription_products_and_variations'  => false,
			'json_search_downloadable_products_and_variations'  => false,
			'json_search_customers_by_email'                    => false,
		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( "wp_ajax_wpmet_subscription_{$ajax_event}", __CLASS__ . "::{$ajax_event}" );

			if ( $nopriv ) {
				add_action( "wp_ajax_nopriv_wpmet_subscription_{$ajax_event}", __CLASS__ . "::{$ajax_event}" );
			}
		}
    }

    public static function add_subscription_note() {
		check_ajax_referer( 'add-subscription-note', 'security' );

		$posted = $_POST;
		$note   = wpmet_add_subscription_note( wc_clean( wp_unslash( $posted[ 'content' ] ) ), absint( wp_unslash( $posted[ 'post_id' ] ) ), 'processing', __( 'Note added by admin', 'sumosubscriptions' ) );
		$note   = sumosubs_get_subscription_note( $note );

		if ( $note ) {
			include 'admin/views/html-admin-subscription-note.php';
		}
		die();
	}
}