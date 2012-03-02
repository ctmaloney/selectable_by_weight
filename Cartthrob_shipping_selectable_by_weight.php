<?php if ( ! defined('CARTTHROB_PATH')) Cartthrob_core::core_error('No direct script access allowed');


/**************************************************************
	EE Cartthrob Shipping Plugin
	
	Customer Selectable - By Weight + Free Shipping Threshold
	author: Christian Maloney
	version: 1.0
	
	Provides customer selectable shipping methods based on per item weight.
	Also provides a threshold for free shipping, i.e. Free Shipping over $100
	
**************************************************************/

class Cartthrob_shipping_selectable_by_weight extends Cartthrob_shipping
{
	public $title = 'Customer Selectable - By Weight + Free Shipping Threshold';
	public $note = 'One option is selected per transaction by customer. The top option is the default.<br/><br/>For the free shipping threshold enter the price a user must purchase in order to get free shipping, i.e. spend $20 to get free shipping.<br/>If the free shipping field is left blank it will be ignored.';
	public $settings = array(
		array(
			'name'	=> 'Free Shipping Threshold',
			'short_name'	=> 'free_ship',
			'type'			=> 'text',
		),
		array(
			'name' => 'rates',
			'short_name' => 'rates',
			'type' => 'matrix',
			'settings' => array(
				array(
					'name' => 'Short Name',
					'short_name' => 'short_name',
					'type' => 'text'
				),
				array(
					'name' => 'Descriptive Title',
					'short_name' => 'title',
					'type' => 'text'
				),
				array(
					'name' => 'Base Rate',
					'short_name' => 'baserate',
					'type' => 'text'
				),
				array(
					'name' => 'Cost per Item, rate * weight',
					'short_name' => 'rate',
					'type' => 'text'
				),
			)
		)
	);
 
	protected $rates = array();
	protected $base_rates = array();
	protected $shipping_option = '';
	
	public function initialize()
	{
		if ($this->plugin_settings('free_ship') )
		{
			$this->free_ship = $this->plugin_settings('free_ship'); 
		}
		
		foreach ($this->plugin_settings('rates') as $rate)
		{
			$this->rate_titles[$rate['short_name']] = $rate['title'];
			$this->rates[$rate['short_name']] = $rate['rate'];
			$this->base_rates[$rate['short_name']] = $rate['baserate'];
		}
	}
	
	public function get_shipping()
	{
		if ($this->core->cart->count() <= 0 || $this->core->cart->shippable_subtotal() <= 0 || (isset($this->free_ship) && $this->core->cart->subtotal() >= $this->free_ship))
		{
			return 0;
		}
		
		$weight = $this->core->cart->weight();
		$this->shipping_option = $this->core->cart->shipping_info('shipping_option');
		if ($weight==0) return 0;
	
		if ($this->shipping_option && array_key_exists($this->shipping_option, $this->rates))
		{
			return $this->base_rates[$this->shipping_option] + ($this->rates[$this->shipping_option] * $weight);
		}
		elseif ( ! $this->shipping_option)
		{
			return 0;
		}
		else
		{
			return max($this->rates);
		}
	}

	public function plugin_shipping_options()
	{
		$options = array();
		
		foreach ($this->rates as $rate_short_name => $price)
		{
			$options[] = array(
				'rate_short_name' => $rate_short_name,
				'price' => $price,
				'rate_price' => $price,
				'rate_title' => $this->rate_titles[$rate_short_name],
			);
		}
		
		return $options;
	}
}