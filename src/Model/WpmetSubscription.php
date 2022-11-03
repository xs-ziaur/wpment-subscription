<?php

namespace Wpmet\WpmetSubscription\Model;

class WpmetSubscription
{
	public $id ;
	public $subscribed_product ;
	public $trial                        = array(
		'optional'               => null,
		'forced'                 => null,
		'type'                   => null,
		'fee'                    => null,
		'duration_period'        => null,
		'duration_period_length' => null,
			) ;
	public $signup                       = array(
		'optional' => null,
		'forced'   => null,
		'fee'      => null,
			) ;
	public $sync                         = array(
		'type'                                    => null,
		'subscribed_after_sync_date_type'         => null,
		'xtra_time_to_charge_full_fee'            => null,
		'cutoff_time_to_not_renew_nxt_subs_cycle' => null,
		'start_year'                              => null,
		'duration_period'                         => null,
		'duration_period_length'                  => null,
			) ;
	public $additional_digital_downloads = array(
		'products' => null,
			) ;
	public $payment_reminder_email_for   = array(
		'auto'   => 'yes',
		'manual' => 'yes',
			) ;
	public $recurring_amount ;
	public $subscribed_qty               = 1 ;
	public $coupons ;
	public $installments ;
	public $subscription ;
	public $item_meta ;

    public function __construct( $subscription )
    {
        if ($subscription instanceof WpmetSubscription) {
            $this->id           = $subscription->id;
            $this->subscription = get_post($this->id);
            $this->populate($subscription->item_meta);
        } else if (is_numeric($subscription)) {
            $this->id           = absint($subscription);
            $this->subscription = get_post($this->id);
            $this->populate();
        }
    }

    protected function populate( $item_meta = null ) {
		if ( is_null( $item_meta ) ) {
			$this->item_meta = SUMOSubs_Subscription_Factory::get_subscription( $this ) ;
		} else {
			$this->item_meta = $item_meta ;
		}

		$this->get_subscribed_product() ;
		$this->get_recurring_amount() ;
		$this->get_trial() ;
		$this->get_signup() ;
		$this->get_sync() ;
		$this->get_coupons() ;
		$this->get_installments() ;
		$this->get_subscribed_qty() ;
		$this->get_additional_digital_downloads() ;
		$this->send_payment_reminder_email_for() ;
	}
}