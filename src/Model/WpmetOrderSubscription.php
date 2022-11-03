<?php

namespace Wpmet\WpmetSubscription\Model;

/**
 * Handle regular products in cart to Order Subscription conversion.
 *
 * @class WpmetOrderSubscription
 */
class WpmetOrderSubscription
{

    /**
     * Check whether the customer can proceed to subscribe
     *
     * @var bool
     */
    protected $can_user_subscribe;

    /**
     * Form to render Order Subscription
     */
    protected $form;

    /**
     * Get options
     */
    public $get_option = array();

    public $wms_admin_subs;

    /**
     * Get subscribed plan props
     *
     * @var array
     */
    protected $subscribed_plan_props = array(
        'subscribed'               => null,
        'has_signup'               => null,
        'signup_fee'               => null,
        'recurring_fee'            => null,
        'duration_period'          => null,
        'duration_length'          => null,
        'recurring_length'         => null,
        'item_fee'                 => null,
        'item_qty'                 => null,
        'discounted_recurring_fee' => null,
    );

    public function __construct()
    {
        $this->wms_admin_subs = new WpmetAdminSubscription();
    }

    /**
     * Get form to render Order Subscription
     */
    public function getForm()
    {
        if (is_null($this->form)) {
            $this->form = $this->wms_admin_subs->getOption('order_subs_checkout_position');
        }
        return $this->form;
    }

    /**
     * Init WpmetOrderSubscription.
     */
    public function init()
    {
        if (empty($this->get_option)) {
            $this->populate();
        }

        if ('yes' !== $this->wms_admin_subs->getOption('order_subs_allow_in_checkout')) {
            return;
        }

        if ('yes' === $this->wms_admin_subs->getOption('order_subs_allow_in_cart')) {
            add_action('woocommerce_before_cart_totals', __CLASS__ . '::renderSubscribeForm');
            add_action('woocommerce_before_cart_totals', __CLASS__ . '::add_custom_style');
        }

        add_action('woocommerce_' . $this->getForm(), __CLASS__ . '::renderSubscribeForm');
        add_action('woocommerce_' . $this->getForm(), __CLASS__ . '::add_custom_style');

        add_action('wp_loaded', __CLASS__ . '::get_subscription_from_session', 20);
        add_action('woocommerce_after_calculate_totals', __CLASS__ . '::get_subscription_from_session', 20);
        add_action('woocommerce_cart_loaded_from_session', __CLASS__ . '::maybe_unsubscribe');
        add_action('woocommerce_cart_emptied', __CLASS__ . '::unsubscribe');
        add_filter('woocommerce_cart_total', __CLASS__ . '::render_subscribed_plan_message', 10, 1);
        add_filter('sumosubscriptions_alter_subscription_plan_meta', __CLASS__ . '::save_subscribed_plan_meta', 10, 4);
    }

    public function can_user_subscribe()
    {
        if (is_bool($this->can_user_subscribe)) {
            return $this->can_user_subscribe;
        }

        if (
            ('yes' === $this->wms_admin_subs->getOption('order_subs_allow_in_checkout')) &&
            !sumo_is_cart_contains_subscription_items(true) &&
            (
                !is_numeric($this->get_option['min_order_total']) ||
                (isset(WC()->cart->total) && WC()->cart->total >= floatval($this->get_option['min_order_total']))
            ) &&
            $this->cart_contains_valid_products()
        ) {
            $this->can_user_subscribe = true;
        }

        return $this->can_user_subscribe;
    }

    public function get_default_props()
    {
        return array_map('__return_null', $this->subscribed_plan_props);
    }

    public function populate()
    {
        $this->get_option = array(
            'default_subscribed'                   => 'yes' === $this->wms_admin_subs->getOption('order_subs_subscribed_default'),
            'can_user_select_plan'                 => 'userdefined' === $this->wms_admin_subs->getOption('order_subs_subscribe_values'),
            'can_user_select_recurring_length'     => 'yes' === $this->wms_admin_subs->getOption('order_subs_userdefined_allow_indefinite_subscription_length'),
            'min_order_total'                      => $this->wms_admin_subs->getOption('order_subs_min_order_total'),
            'default_duration_period'              => $this->wms_admin_subs->getOption('order_subs_predefined_subscription_period'),
            'default_duration_length'              => $this->wms_admin_subs->getOption('order_subs_predefined_subscription_period_interval'),
            'default_recurring_length'             => $this->wms_admin_subs->getOption('order_subs_predefined_subscription_length'),
            'duration_period_selector'             => $this->wms_admin_subs->getOption('order_subs_userdefined_subscription_periods'),
            'min_duration_length_user_can_select'  => $this->wms_admin_subs->getOption('order_subs_userdefined_min_subscription_period_intervals'),
            'max_duration_length_user_can_select'  => $this->wms_admin_subs->getOption('order_subs_userdefined_max_subscription_period_intervals'),
            'min_recurring_length_user_can_select' => $this->wms_admin_subs->getOption('order_subs_userdefined_min_subscription_length'),
            'max_recurring_length_user_can_select' => $this->wms_admin_subs->getOption('order_subs_userdefined_max_subscription_length'),
            'has_signup'                           => $this->wms_admin_subs->getOption('order_subs_charge_signupfee'),
            'signup_fee'                           => $this->wms_admin_subs->getOption('order_subs_signupfee'),
            'product_wide_selection'               => $this->wms_admin_subs->getOption('order_subs_product_wide_selection'),
            'allowed_product_ids'                  => $this->wms_admin_subs->getOption('order_subs_allowed_product_ids'),
            'restricted_product_ids'               => $this->wms_admin_subs->getOption('order_subs_restricted_product_ids'),
            'allowed_product_cat_ids'              => $this->wms_admin_subs->getOption('order_subs_allowed_product_cat_ids'),
            'restricted_product_cat_ids'           => $this->wms_admin_subs->getOption('order_subs_restricted_product_cat_ids'),
        );
    }

    /**
     * Check whether the cart contains valid products to perform Order Subscription by the user.
     *
     * @return bool
     */
    public function cart_contains_valid_products()
    {
        $product_ids_in_cart = array();

        if (!is_null(WC()->cart)) {
            foreach (WC()->cart->cart_contents as $values) {
                if ($values['variation_id'] > 0) {
                    $product_ids_in_cart[$values['variation_id']] = $values['product_id'];
                } else {
                    $product_ids_in_cart[$values['product_id']] = 0;
                }
            }
        }

        $valid = true;
        switch ($this->get_option['product_wide_selection']) {
            case 'allowed-product-ids':
                $allowed_product_ids_count     = 0;
                $not_allowed_product_ids_count = 0;

                if (count($this->get_option['allowed_product_ids'])) {
                    foreach ($product_ids_in_cart as $product_id => $parent_id) {
                        $product_ids = array($product_id, $parent_id);

                        if (count(array_intersect($product_ids, $this->get_option['allowed_product_ids'])) > 0) {
                            $allowed_product_ids_count++;
                        } else {
                            $not_allowed_product_ids_count++;
                        }
                    }
                }

                $valid = ($allowed_product_ids_count > 0 && 0 === $not_allowed_product_ids_count) ? true : false;
                break;
            case 'restricted-product-ids':
                $allowed_product_ids_count     = 0;
                $not_allowed_product_ids_count = 0;

                if (count($this->get_option['restricted_product_ids'])) {
                    foreach ($product_ids_in_cart as $product_id => $parent_id) {
                        $product_ids = array($product_id, $parent_id);

                        if (count(array_intersect($product_ids, $this->get_option['restricted_product_ids'])) > 0) {
                            $not_allowed_product_ids_count++;
                        } else {
                            $allowed_product_ids_count++;
                        }
                    }
                }

                $valid = $not_allowed_product_ids_count > 0 ? false : true;
                break;
            case 'allowed-product-cat-ids':
                $allowed_product_cat_ids_count     = 0;
                $not_allowed_product_cat_ids_count = 0;

                if (count($this->get_option['allowed_product_cat_ids'])) {
                    foreach ($product_ids_in_cart as $product_id => $parent_id) {
                        $product_cats = wc_get_product_cat_ids($parent_id > 0 ? $parent_id : $product_id);

                        if (count(array_intersect($product_cats, $this->get_option['allowed_product_cat_ids'])) > 0) {
                            $allowed_product_cat_ids_count++;
                        } else {
                            $not_allowed_product_cat_ids_count++;
                        }
                    }
                }

                $valid = ($allowed_product_cat_ids_count > 0 && 0 === $not_allowed_product_cat_ids_count) ? true : false;
                break;
            case 'restricted-product-cat-ids':
                $allowed_product_cat_ids_count     = 0;
                $not_allowed_product_cat_ids_count = 0;

                if (count($this->get_option['restricted_product_cat_ids'])) {
                    foreach ($product_ids_in_cart as $product_id => $parent_id) {
                        $product_cats = wc_get_product_cat_ids($parent_id > 0 ? $parent_id : $product_id);

                        if (count(array_intersect($product_cats, $this->get_option['restricted_product_cat_ids'])) > 0) {
                            $not_allowed_product_cat_ids_count++;
                        } else {
                            $allowed_product_cat_ids_count++;
                        }
                    }
                }

                $valid = $not_allowed_product_cat_ids_count > 0 ? false : true;
                break;
        }

        return $valid;
    }

    public function is_subscribed($subscription_id = 0, $parent_order_id = 0, $customer_id = 0)
    {
        if ($subscription_id) {
            return 'yes' === get_post_meta($subscription_id, 'sumo_is_order_based_subscriptions', true);
        }

        if ($parent_order_id && 'yes' === get_post_meta($parent_order_id, 'sumo_is_order_based_subscriptions', true)) {
            return true;
        }

        $customer_id = absint($customer_id);
        if ($customer_id) {
            $subscribed_plan = get_user_meta($customer_id, 'sumo_subscriptions_order_details', true);

            if (!empty($subscribed_plan['subscribed']) && 'yes' === $subscribed_plan['subscribed']) {
                return true;
            }
        }

        if ($this->can_user_subscribe() && !empty(WC()->cart->sumosubscriptions['order']['subscribed'])) {
            return 'yes' === WC()->cart->sumosubscriptions['order']['subscribed'];
        }

        return false;
    }

    public function get_subscribed_plan($customer_id = 0)
    {
        $subscribed_plan = array();

        $customer_id = absint($customer_id);
        if ($customer_id) {
            $subscribed_plan = get_user_meta($customer_id, 'sumo_subscriptions_order_details', true);
        }

        if (empty($subscribed_plan) && $this->is_subscribed()) {
            $subscribed_plan = WC()->cart->sumosubscriptions['order'];
        }

        $this->subscribed_plan_props = wp_parse_args(is_array($subscribed_plan) ? $subscribed_plan : array(), $this->get_default_props());
        return $this->subscribed_plan_props;
    }

    public function add_custom_style()
    {
        if ($this->can_user_subscribe()) {
            wp_register_style('sumo-order-subsc-inline', false, array(), SUMO_SUBSCRIPTIONS_VERSION);
            wp_enqueue_style('sumo-order-subsc-inline');
            wp_add_inline_style('sumo-order-subsc-inline', $this->wms_admin_subs->getOption('order_subs_inline_style'));
        }
    }

    public function renderSubscribeForm()
    {
        if ((!is_cart() && !is_checkout()) || !$this->can_user_subscribe()) {
            return;
        }

        sumosubscriptions_get_template('order-subscription-form.php', array(
            'options'                     => $this->get_option,
            'subscribe_label'             => $this->wms_admin_subs->getOption('order_subs_subscribe_label'),
            'subscription_duration_label' => $this->wms_admin_subs->getOption('order_subs_subscription_duration_label'),
            'subscription_length_label'   => $this->wms_admin_subs->getOption('order_subs_subscription_length_label'),
            'chosen_plan'                 => $this->get_subscribed_plan(),
        ));
    }

    public function render_subscribed_plan_message($total)
    {
        if ($this->is_subscribed()) {
            $total = sumo_display_subscription_plan();

            if (is_numeric(WC()->cart->sumosubscriptions['order']['discounted_recurring_fee'])) {
                $total .= str_replace('[renewal_fee_after_discount]', wc_price(WC()->cart->sumosubscriptions['order']['discounted_recurring_fee']), $this->wms_admin_subs->getOption('discounted_renewal_amount_strings'));
            }
        }

        return $total;
    }

    public function get_shipping_to_apply_in_renewal($calc_tax = false)
    {
        if ('yes' !== $this->wms_admin_subs->getOption('charge_shipping_during_renewals')) {
            return false;
        }

        $totals         = is_callable(array(WC()->cart, 'get_totals')) ? WC()->cart->get_totals() : WC()->cart->totals;
        $shipping_total = !empty($totals['shipping_total']) ? floatval($totals['shipping_total']) : false;
        $shipping_tax   = $calc_tax && !empty($totals['shipping_tax']) ? floatval($totals['shipping_tax']) : false;

        if ($shipping_total && $shipping_tax) {
            $shipping_total += $shipping_tax;
        }

        return $shipping_total;
    }

    public function get_items_tax_to_apply_in_renewal($cart_item = array())
    {
        if ('yes' !== $this->wms_admin_subs->getOption('charge_tax_during_renewals') || !wc_tax_enabled()) {
            return false;
        }

        $items_tax = false;
        if (!empty($cart_item)) {
            if (!empty($cart_item['line_tax'])) {
                $items_tax = floatval($cart_item['line_tax']);
            }
        } else {
            $totals       = is_callable(array(WC()->cart, 'get_totals')) ? WC()->cart->get_totals() : WC()->cart->totals;
            $discount_tax = !empty($totals['discount_tax']) ? floatval($totals['discount_tax']) : false;
            $items_tax    = !empty($totals['cart_contents_tax']) ? floatval($totals['cart_contents_tax']) : false;
            $items_tax    = $discount_tax && $items_tax ? $items_tax + $discount_tax : $items_tax;
        }

        return $items_tax;
    }

    public function update_user_meta($customer_id)
    {
        delete_user_meta($customer_id, 'sumo_subscriptions_order_details');

        if ($this->is_subscribed()) {
            add_user_meta($customer_id, 'sumo_subscriptions_order_details', $this->get_subscribed_plan());
        }
    }

    public function check_session_data()
    {
        if (!in_array(WC()->session->get('sumo_order_subscription_duration_period'), (array) $this->get_option['duration_period_selector'])) {
            $this->unsubscribe();
        }
    }

    public function get_subscription_from_session()
    {
        if (!did_action('woocommerce_loaded') || !isset(WC()->cart)) {
            return;
        }

        if (!$this->can_user_subscribe()) {
            return;
        }

        $this->check_session_data();
        WC()->cart->sumosubscriptions                        = array();
        WC()->cart->sumosubscriptions['order']['subscribed'] = WC()->session->get('sumo_is_order_subscription_subscribed');

        if ('yes' !== WC()->cart->sumosubscriptions['order']['subscribed']) {
            return;
        }

        $recurring_fee        = 0;
        $items_tax_in_renewal = false;
        $totals               = is_callable(array(WC()->cart, 'get_totals')) ? WC()->cart->get_totals() : WC()->cart->totals;

        WC()->cart->sumosubscriptions['order']['duration_period']  = WC()->session->get('sumo_order_subscription_duration_period', 'D');
        WC()->cart->sumosubscriptions['order']['duration_length']  = WC()->session->get('sumo_order_subscription_duration_length', '1');
        WC()->cart->sumosubscriptions['order']['recurring_length'] = WC()->session->get('sumo_order_subscription_recurring_length', '0');

        if (!empty($totals['cart_contents_tax'])) {
            WC()->cart->sumosubscriptions['order']['has_signup'] = true;
            $items_tax_in_renewal                                = $this->get_items_tax_to_apply_in_renewal();

            if (is_numeric($items_tax_in_renewal)) {
                $recurring_fee += $items_tax_in_renewal;
            }
        }

        if (!empty($totals['shipping_total'])) {
            WC()->cart->sumosubscriptions['order']['has_signup'] = true;
            $shipping_in_renewal                                 = $this->get_shipping_to_apply_in_renewal(is_numeric($items_tax_in_renewal));

            if (is_numeric($shipping_in_renewal)) {
                $recurring_fee += $shipping_in_renewal;
            }
        }

        if (!empty($totals['discount_total'])) {
            WC()->cart->sumosubscriptions['order']['has_signup'] = true;
        }

        foreach (WC()->cart->cart_contents as $cart_item) {
            if (empty($cart_item['product_id'])) {
                continue;
            }
            //Calculate Recurring Fee based no. of Item Qty
            $recurring_fee += floatval(wc_format_decimal(wc_get_price_excluding_tax($cart_item['data'], array('qty' => $cart_item['quantity'])), wc_get_price_decimals()));
            $item_id = $cart_item['variation_id'] > 0 ? $cart_item['variation_id'] : $cart_item['product_id'];

            WC()->cart->sumosubscriptions['order']['item_fee'][$item_id] = $cart_item['data']->get_price();
            WC()->cart->sumosubscriptions['order']['item_qty'][$item_id] = $cart_item['quantity'];
        }

        if (!empty($totals['discount_total']) && $recurring_fee) {
            WC()->cart->sumosubscriptions['order']['discounted_recurring_fee'] = WC()->cart->total;
        }

        if ('yes' === $this->get_option['has_signup'] && is_numeric($this->get_option['signup_fee']) && $this->get_option['signup_fee'] > 0) {
            WC()->cart->total += wc_format_decimal($this->get_option['signup_fee']);
            WC()->cart->sumosubscriptions['order']['has_signup'] = true;
        }

        if (wc_format_decimal($recurring_fee) == WC()->cart->total) {
            WC()->cart->sumosubscriptions['order']['has_signup'] = null;
        }

        if (isset(WC()->cart->sumosubscriptions['order']['has_signup']) && WC()->cart->sumosubscriptions['order']['has_signup']) {
            WC()->cart->sumosubscriptions['order']['signup_fee']    = WC()->cart->total;
            WC()->cart->sumosubscriptions['order']['recurring_fee'] = $recurring_fee;
        } else {
            WC()->cart->sumosubscriptions['order']['recurring_fee'] = WC()->cart->total;
        }

        WC()->cart->sumosubscriptions['order'] = $this->get_subscribed_plan();
    }

    /**
     * Maybe unsubscribe.
     */
    public function maybe_unsubscribe($cart)
    {
        if ($cart->is_empty()) {
            $this->unsubscribe();
        }
    }

    /**
     * Unsubscribe.
     */
    public function unsubscribe()
    {
        WC()->session->__unset('sumo_is_order_subscription_subscribed');
        WC()->session->__unset('sumo_order_subscription_duration_period');
        WC()->session->__unset('sumo_order_subscription_duration_length');
        WC()->session->__unset('sumo_order_subscription_recurring_length');
    }

    public function save_subscribed_plan_meta($subscribed_plan, $subscription_id, $product_id, $customer_id)
    {
        if ($subscription_id || $product_id) {
            return $subscribed_plan;
        }

        if ($this->is_subscribed(0, 0, $customer_id)) {
            $this->get_subscribed_plan($customer_id);

            $subscribed_plan['susbcription_status']   = '1';
            $subscribed_plan['subfee']                = $this->subscribed_plan_props['recurring_fee'];
            $subscribed_plan['subperiod']             = $this->subscribed_plan_props['duration_period'];
            $subscribed_plan['subperiodvalue']        = $this->subscribed_plan_props['duration_length'];
            $subscribed_plan['instalment']            = $this->subscribed_plan_props['recurring_length'];
            $subscribed_plan['signusumoee_selection'] = $this->subscribed_plan_props['has_signup'] ? '1' : '';
            $subscribed_plan['signup_fee']            = $this->subscribed_plan_props['signup_fee'];
            $subscribed_plan['productid']             = array_keys($this->subscribed_plan_props['item_fee']);
            $subscribed_plan['item_fee']              = $this->subscribed_plan_props['item_fee'];
            $subscribed_plan['product_qty']           = $this->subscribed_plan_props['item_qty'];
        }

        return $subscribed_plan;
    }

}

WpmetOrderSubscription::init();

/**
 * For Backward Compatibility.
 */
class SUMO_Order_Subscription extends WpmetOrderSubscription
{

}
