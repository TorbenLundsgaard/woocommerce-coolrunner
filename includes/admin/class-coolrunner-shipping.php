<?php
if ( ! defined( 'ABSPATH' ) ) {
exit;
}

function CR_shipping_methods_init() {
	if ( ! class_exists( 'WC_coolrunner_dao_private_droppoint' ) ) {
		class WC_coolrunner_dao_private_droppoint extends WC_Shipping_Method {

			public function __construct( $instance_id = 0 ) {
				$this->id         	= 'coolrunner_dao_private_droppoint';
				$this->instance_id = absint( $instance_id );

				$this->init_form_fields();
	  		$this->init_settings();

				$this->method_title	= __( 'CoolRunner - DAO Pakkeshop' );
				$this->cost			= 0;
		  	$this->title 		= $this->get_option( 'title' );

				$this->supports = array(
					'shipping-zones',
					'instance-settings',
					'instance-settings-modal',
				);

				add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
			}

			public function init_form_fields(){
				$this->instance_form_fields = array(
			    'title' => array(
			      'title' 		=> __( 'Method Title', 'woocommerce' ),
			      'type' 			=> 'text',
			      'description' 	=> __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
			      'default'		=> __( 'CoolRunner - DAO Pakkeshop', 'woocommerce' ),
			    ),
				  'cost' => array(
						'title'     => __( 'Cost', 'coolrunner-shipping-plugin'),
						'type'       => 'text',
						'description'   => __( 'This controls the price of the shipping', 'coolrunner-shipping-plugin'),
						'default'    => __( '0', 'coolrunner-shipping-plugin'),
				  )
				);
			}

			public function calculate_shipping( $package = array() ) {

				global $woocommerce;


				$weight = $woocommerce->cart->cart_contents_weight;	
				$weight = $weight * 1000;

				$shipping_methods = get_option( 'coolrunner_wc_curl_data' );

				$cost=0;

				if( $shipping_methods ){

					foreach($shipping_methods as $shipping_method){

						foreach($shipping_method as $entry){
							if(is_array($entry) && $shipping_method['name'] == 'coolrunner_dao_private_droppoint'){
								if($weight < $entry['weight_to']){
									$cost = $entry['price_excl_tax'];
									break;
								}
							}
						}

					}
				}


			    $this->add_rate( array(
			      'id' 	=> $this->id,
			      'label' => $this->title,
			      'cost' 	=> $cost
			    ));
			}
		}
	}

	if ( ! class_exists( 'WC_coolrunner_dao_private_delivery_letter' ) ) {
		class WC_coolrunner_dao_private_delivery_letter extends WC_Shipping_Method {

			public function __construct( $instance_id = 0 ) {
				$this->id             = 'coolrunner_dao_private_delivery_letter';
				$this->instance_id 	= absint( $instance_id );

				$this->init_form_fields();
	  		$this->init_settings();

				$this->method_title   = __( 'CoolRunner - DAO Hjemmelevering - Mini Pakke' );
				$this->cost						= 0;
		  	$this->title 					= $this->get_option( 'title' );

				$this->supports     = array(
					'shipping-zones',
					'instance-settings',
					'instance-settings-modal',
				);

			add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
			}

			public function init_form_fields(){

				$this->instance_form_fields = array(
					'title' => array(
						'title'     => __( 'Method Title', 'coolrunner-shipping-plugin'),
						'type'       => 'text',
						'description'   => __( 'This controls the title which the user sees during checkout.', 'coolrunner-shipping-plugin'),
						'default'    => __( 'CoolRunner - DAO Hjemmelevering - Mini Pakke', 'coolrunner-shipping-plugin'),
					),
				  'cost' => array(
						'title'     => __( 'Cost', 'coolrunner-shipping-plugin'),
						'type'       => 'text',
						'description'   => __( 'This controls the price of the shipping', 'coolrunner-shipping-plugin'),
						'default'    => __( '0', 'coolrunner-shipping-plugin'),
				  )
				);
			}

			public function calculate_shipping( $package = array() ) {

				global $woocommerce;


				$weight = $woocommerce->cart->cart_contents_weight;	
				$weight = $weight * 1000;

				$shipping_methods = get_option( 'coolrunner_wc_curl_data' );

				$cost=0;

				if( $shipping_methods ){

					foreach($shipping_methods as $shipping_method){

						foreach($shipping_method as $entry){
							if(is_array($entry) && $shipping_method['name'] == 'coolrunner_dao_private_delivery_letter'){
								if($weight < $entry['weight_to']){
									$cost = $entry['price_excl_tax'];
									break;
								}
							}
						}

					}
				}


			    $this->add_rate( array(
			      'id' 	=> $this->id,
			      'label' => $this->title,
			      'cost' 	=> $cost
			    ));
			}
		}
	}

	if ( ! class_exists( 'WC_coolrunner_dao_private_delivery_package' ) ) {
		class WC_coolrunner_dao_private_delivery_package extends WC_Shipping_Method {

			public function __construct( $instance_id = 0 ) {
				$this->id             = 'coolrunner_dao_private_delivery_package';
				$this->instance_id 	= absint( $instance_id );

				$this->init_form_fields();
	  		$this->init_settings();

				$this->method_title   = __( 'CoolRunner - DAO Hjemmelevering - Pakke' );
				$this->cost						= 0;
		  		$this->title 					= $this->get_option( 'title' );

				$this->supports     = array(
					'shipping-zones',
					'instance-settings',
					'instance-settings-modal',
				);

			add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
			}

			public function init_form_fields(){

				$this->instance_form_fields = array(
					'title' => array(
						'title'     => __( 'Method Title', 'coolrunner-shipping-plugin'),
						'type'       => 'text',
						'description'   => __( 'This controls the title which the user sees during checkout.', 'coolrunner-shipping-plugin'),
						'default'    => __( 'CoolRunner - DAO Hjemmelevering - Pakke', 'coolrunner-shipping-plugin'),
					),
				  'cost' => array(
						'title'     => __( 'Cost', 'coolrunner-shipping-plugin'),
						'type'       => 'text',
						'description'   => __( 'This controls the price of the shipping', 'coolrunner-shipping-plugin'),
						'default'    => __( '0', 'coolrunner-shipping-plugin'),
				  )
				);
			}

			public function calculate_shipping( $package = array() ) {

				global $woocommerce;


				$weight = $woocommerce->cart->cart_contents_weight;	
				$weight = $weight * 1000;

				$shipping_methods = get_option( 'coolrunner_wc_curl_data' );

				$cost=0;

				if( $shipping_methods ){

					foreach($shipping_methods as $shipping_method){

						foreach($shipping_method as $entry){
							if(is_array($entry) && $shipping_method['name'] == 'coolrunner_dao_private_delivery_package'){
								if($weight < $entry['weight_to']){
									$cost = $entry['price_excl_tax'];
									break;
								}
							}
						}

					}
				}


			    $this->add_rate( array(
			      'id' 	=> $this->id,
			      'label' => $this->title,
			      'cost' 	=> $cost
			    ));
			}
		}
	}

	if ( ! class_exists( 'WC_coolrunner_pdk_private' ) ) {
		class WC_coolrunner_pdk_private extends WC_Shipping_Method {

			public function __construct( $instance_id = 0 ) {
				$this->id           = 'coolrunner_pdk_private';
				$this->instance_id 	= absint( $instance_id );

				$this->init_form_fields();
	  		$this->init_settings();

				$this->method_title = __( 'CoolRunner - Post Danmark - Uden omdeling' );
				$this->cost					= 0;
		  	$this->title 				= $this->get_option( 'title' );

				$this->supports = array(
					'shipping-zones',
					'instance-settings',
					'instance-settings-modal',
				);

				add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );

			}

			public function init_form_fields(){

				$this->instance_form_fields = array(
					'title' => array(
						'title'     => __( 'Method Title', 'coolrunner-shipping-plugin'),
						'type'       => 'text',
						'description'   => __( 'This controls the title which the user sees during checkout.', 'coolrunner-shipping-plugin'),
						'default'    => __( 'CoolRunner - Post Danmark - Uden omdeling', 'coolrunner-shipping-plugin'),
					),
				  'cost' => array(
						'title'     => __( 'Cost', 'coolrunner-shipping-plugin'),
						'type'       => 'text',
						'description'   => __( 'This controls the price of the shipping', 'coolrunner-shipping-plugin'),
						'default'    => __( '0', 'coolrunner-shipping-plugin'),
				  )
				);
			}

			public function calculate_shipping( $package = array() ) {

				global $woocommerce;


				$weight = $woocommerce->cart->cart_contents_weight;	
				$weight = $weight * 1000;

				$shipping_methods = get_option( 'coolrunner_wc_curl_data' );

				$cost=0;

				if( $shipping_methods ){

					foreach($shipping_methods as $shipping_method){

						foreach($shipping_method as $entry){
							if(is_array($entry) && $shipping_method['name'] == 'coolrunner_pdk_private'){
								if($weight < $entry['weight_to']){
									$cost = $entry['price_excl_tax'];
									break;
								}
							}
						}

					}
				}


			    $this->add_rate( array(
			      'id' 	=> $this->id,
			      'label' => $this->title,
			      'cost' 	=> $cost
			    ));
			}
		}
	}


	if ( ! class_exists( 'WC_coolrunner_pdk_private_droppoint' ) ) {
		class WC_coolrunner_pdk_private_droppoint extends WC_Shipping_Method {

			public function __construct( $instance_id = 0 ) {
				$this->id           = 'coolrunner_pdk_private_droppoint';
				$this->instance_id 	= absint( $instance_id );

				$this->init_form_fields();
	  		$this->init_settings();

				$this->method_title = __( 'CoolRunner - Post Danmark - Afhentningssted' );
				$this->cost					= 0;
		  	$this->title 				= $this->get_option( 'title' );

				$this->supports = array(
					'shipping-zones',
					'instance-settings',
					'instance-settings-modal',
				);

				add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );

			}

			public function init_form_fields(){

				$this->instance_form_fields = array(
					'title' => array(
						'title'     => __( 'Method Title', 'coolrunner-shipping-plugin'),
						'type'       => 'text',
						'description'   => __( 'This controls the title which the user sees during checkout.', 'coolrunner-shipping-plugin'),
						'default'    => __( 'CoolRunner - Post Danmark - Afhentningssted', 'coolrunner-shipping-plugin'),
					),
				  'cost' => array(
						'title'     => __( 'Cost', 'coolrunner-shipping-plugin'),
						'type'       => 'text',
						'description'   => __( 'This controls the price of the shipping', 'coolrunner-shipping-plugin'),
						'default'    => __( '0', 'coolrunner-shipping-plugin'),
				  )
				);
			}

			public function calculate_shipping( $package = array() ) {

				global $woocommerce;


				$weight = $woocommerce->cart->cart_contents_weight;	
				$weight = $weight * 1000;

				$shipping_methods = get_option( 'coolrunner_wc_curl_data' );

				$cost=0;

				if( $shipping_methods ){

					foreach($shipping_methods as $shipping_method){

						foreach($shipping_method as $entry){
							if(is_array($entry) && $shipping_method['name'] == 'coolrunner_pdk_private_droppoint'){
								if($weight < $entry['weight_to']){
									$cost = $entry['price_excl_tax'];
									break;
								}
							}
						}

					}
				}


			    $this->add_rate( array(
			      'id' 	=> $this->id,
			      'label' => $this->title,
			      'cost' 	=> $cost
			    ));
			}
		}
	}

	if ( ! class_exists( 'WC_coolrunner_pdk_private_delivery' ) ) {
		class WC_coolrunner_pdk_private_delivery extends WC_Shipping_Method {

			public function __construct( $instance_id = 0 ) {
				$this->id             = 'coolrunner_pdk_private_delivery';
				$this->instance_id 		= absint( $instance_id );

				$this->init_form_fields();
	  		$this->init_settings();

				$this->method_title   = __( 'CoolRunner - Post Danmark - Med omdeling' );
				$this->cost			= 0;
		  	$this->title 		= $this->get_option( 'title' );

				$this->supports     = array(
					'shipping-zones',
					'instance-settings',
					'instance-settings-modal',
				);

				add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
			}

			public function init_form_fields(){

				$this->instance_form_fields = array(
					'title' => array(
						'title'     => __( 'Method Title', 'coolrunner-shipping-plugin'),
						'type'       => 'text',
						'description'   => __( 'This controls the title which the user sees during checkout.', 'coolrunner-shipping-plugin'),
						'default'    => __( 'CoolRunner - Post Danmark - Med omdeling', 'coolrunner-shipping-plugin'),
					),
				  'cost' => array(
						'title'     => __( 'Cost', 'coolrunner-shipping-plugin'),
						'type'       => 'text',
						'description'   => __( 'This controls the price of the shipping', 'coolrunner-shipping-plugin'),
						'default'    => __( '0', 'coolrunner-shipping-plugin'),
				  )
				);
			}

			public function calculate_shipping( $package = array() ) {

				global $woocommerce;


				$weight = $woocommerce->cart->cart_contents_weight;	
				$weight = $weight * 1000;

				$shipping_methods = get_option( 'coolrunner_wc_curl_data' );

				$cost=0;

				if( $shipping_methods ){

					foreach($shipping_methods as $shipping_method){

						foreach($shipping_method as $entry){
							if(is_array($entry) && $shipping_method['name'] == 'coolrunner_pdk_private_delivery'){
								if($weight < $entry['weight_to']){
									$cost = $entry['price_excl_tax'];
									break;
								}
							}
						}

					}
				}


			    $this->add_rate( array(
			      'id' 	=> $this->id,
			      'label' => $this->title,
			      'cost' 	=> $cost
			    ));
			}
		}
	}

	if ( ! class_exists( 'WC_coolrunner_pdk_business' ) ) {
		class WC_coolrunner_pdk_business extends WC_Shipping_Method {

			public function __construct( $instance_id = 0 ) {
				$this->id             = 'coolrunner_pdk_business';
				$this->instance_id 	= absint( $instance_id );

				$this->init_form_fields();
	  		$this->init_settings();

				$this->method_title   = __( 'CoolRunner - Post Danmark Erhverv' );
				$this->cost			= 0;
		  	$this->title 		= $this->get_option( 'title' );

				$this->supports     = array(
					'shipping-zones',
					'instance-settings',
					'instance-settings-modal',
				);

			add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
			}

			public function init_form_fields(){

				$this->instance_form_fields = array(
					'title' => array(
						'title'     => __( 'Method Title', 'coolrunner-shipping-plugin'),
						'type'       => 'text',
						'description'   => __( 'This controls the title which the user sees during checkout.', 'coolrunner-shipping-plugin'),
						'default'    => __( 'CoolRunner - Post Nord Erhvervslevering', 'coolrunner-shipping-plugin'),
					),
				  'cost' => array(
						'title'     => __( 'Cost', 'coolrunner-shipping-plugin'),
						'type'       => 'text',
						'description'   => __( 'This controls the price of the shipping', 'coolrunner-shipping-plugin'),
						'default'    => __( '0', 'coolrunner-shipping-plugin'),
				  )
				);
			}

			public function calculate_shipping( $package = array() ) {

				global $woocommerce;


				$weight = $woocommerce->cart->cart_contents_weight;	
				$weight = $weight * 1000;

				$shipping_methods = get_option( 'coolrunner_wc_curl_data' );

				$cost=0;

				

				if( $shipping_methods ){

					foreach($shipping_methods as $shipping_method){

						foreach($shipping_method as $entry){
							if(is_array($entry) && $shipping_method['name'] == 'coolrunner_pdk_business'){
								if($weight < $entry['weight_to']){
									$cost = $entry['price_excl_tax'];
									break;
								}
							}
						}

					}
				}


			    $this->add_rate( array(
			      'id' 	=> $this->id,
			      'label' => $this->title,
			      'cost' 	=> $cost
			    ));
			}
		}
	}

	
	if ( ! class_exists( 'WC_coolrunner_coolrunner_private_europe' ) ) {
		class WC_coolrunner_coolrunner_private_europe extends WC_Shipping_Method {

			public function __construct( $instance_id = 0 ) {
				$this->id             = 'coolrunner_coolrunner_private_europe';
				$this->instance_id 	= absint( $instance_id );

				$this->init_form_fields();
	  		$this->init_settings();

				$this->method_title   = __( 'CoolRunner - CoolEurope' );
				$this->cost						= 0;
		  	$this->title 					= $this->get_option( 'title' );

				$this->supports     = array(
					'shipping-zones',
					'instance-settings',
					'instance-settings-modal',
				);

			add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
			}

			public function init_form_fields(){

				$this->instance_form_fields = array(
					'title' => array(
						'title'     => __( 'Method Title', 'coolrunner-shipping-plugin'),
						'type'       => 'text',
						'description'   => __( 'This controls the title which the user sees during checkout.', 'coolrunner-shipping-plugin'),
						'default'    => __( 'CoolRunner - CoolEurope', 'coolrunner-shipping-plugin'),
					),
				  'cost' => array(
						'title'     => __( 'Cost', 'coolrunner-shipping-plugin'),
						'type'       => 'text',
						'description'   => __( 'This controls the price of the shipping', 'coolrunner-shipping-plugin'),
						'default'    => __( '0', 'coolrunner-shipping-plugin'),
				  )
				);
			}

			public function calculate_shipping( $package = array() ) {

				global $woocommerce;


				$weight = $woocommerce->cart->cart_contents_weight;	
				$weight = $weight * 1000;

				$shipping_country = $woocommerce->customer->get_shipping_country();

				$shipping_methods = get_option( 'coolrunner_wc_curl_data' );

				$cost=0;

				if( $shipping_methods ){

					foreach($shipping_methods as $shipping_method){

						foreach($shipping_method as $entry){
							if(is_array($entry) && $shipping_method['name'] == 'coolrunner_coolrunner_private_europe'){
								if($weight < $entry['weight_to'] && $entry['zone_to']==$shipping_country){
									$cost = $entry['price_excl_tax'];
									break;
								}
							}
						}

					}
				}


			    $this->add_rate( array(
			      'id' 	=> $this->id,
			        'label' => $this->title,
			      'cost' 	=> $cost
			    ));
			}
		}
	}

		if ( ! class_exists( 'WC_coolrunner_posti_private_droppoint' ) ) {
		class WC_coolrunner_posti_private_droppoint extends WC_Shipping_Method {

			public function __construct( $instance_id = 0 ) {
				$this->id             = 'coolrunner_posti_private_droppoint';
				$this->instance_id 	= absint( $instance_id );

				$this->init_form_fields();
	  		$this->init_settings();

				$this->method_title   = __( 'CoolRunner - Posti Pakkeshop' );
				$this->cost						= 0;
		  	$this->title 					= $this->get_option( 'title' );

				$this->supports     = array(
					'shipping-zones',
					'instance-settings',
					'instance-settings-modal',
				);

			add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
			}

			public function init_form_fields(){

				$this->instance_form_fields = array(
					'title' => array(
						'title'     => __( 'Method Title', 'coolrunner-shipping-plugin'),
						'type'       => 'text',
						'description'   => __( 'This controls the title which the user sees during checkout.', 'coolrunner-shipping-plugin'),
						'default'    => __( 'CoolRunner -Posti Pakkeshop', 'coolrunner-shipping-plugin'),
					),
				  'cost' => array(
						'title'     => __( 'Cost', 'coolrunner-shipping-plugin'),
						'type'       => 'text',
						'description'   => __( 'This controls the price of the shipping', 'coolrunner-shipping-plugin'),
						'default'    => __( '0', 'coolrunner-shipping-plugin'),
				  )
				);
			}

			public function calculate_shipping( $package = array() ) {

				global $woocommerce;


				$weight = $woocommerce->cart->cart_contents_weight;	
				$weight = $weight * 1000;

				$shipping_country = $woocommerce->customer->get_shipping_country();

				$shipping_methods = get_option( 'coolrunner_wc_curl_data' );

				$cost=0;

				if( $shipping_methods ){

					foreach($shipping_methods as $shipping_method){

						foreach($shipping_method as $entry){
							if(is_array($entry) && $shipping_method['name'] == 'coolrunner_posti_private_droppoint'){
								if($weight < $entry['weight_to'] && $entry['zone_to']==$shipping_country){
									$cost = $entry['price_excl_tax'];
									break;
								}
							}
						}

					}
				}


			    $this->add_rate( array(
			      'id' 	=> $this->id,
			      'label' => $this->title,
			      'cost' 	=> $cost
			    ));
			}
		}
	}

	if ( ! class_exists( 'WC_coolrunner_dhl_private_droppoint' ) ) {
		class WC_coolrunner_dhl_private_droppoint extends WC_Shipping_Method {

			public function __construct( $instance_id = 0 ) {
				$this->id             = 'coolrunner_dhl_private_droppoint';
				$this->instance_id 	= absint( $instance_id );

				$this->init_form_fields();
	  		$this->init_settings();

				$this->method_title   = __( 'CoolRunner - DHL Pakkeshop' );
				$this->cost						= 0;
		  	$this->title 					= $this->get_option( 'title' );

				$this->supports     = array(
					'shipping-zones',
					'instance-settings',
					'instance-settings-modal',
				);

			add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
			}

			public function init_form_fields(){

				$this->instance_form_fields = array(
					'title' => array(
						'title'     => __( 'Method Title', 'coolrunner-shipping-plugin'),
						'type'       => 'text',
						'description'   => __( 'This controls the title which the user sees during checkout.', 'coolrunner-shipping-plugin'),
						'default'    => __( 'CoolRunner - DHL Pakkeshop', 'coolrunner-shipping-plugin'),
					),
				  'cost' => array(
						'title'     => __( 'Cost', 'coolrunner-shipping-plugin'),
						'type'       => 'text',
						'description'   => __( 'This controls the price of the shipping', 'coolrunner-shipping-plugin'),
						'default'    => __( '0', 'coolrunner-shipping-plugin'),
				  )
				);
			}

			public function calculate_shipping( $package = array() ) {

				global $woocommerce;


				$weight = $woocommerce->cart->cart_contents_weight;	
				$weight = $weight * 1000;

				$shipping_country = $woocommerce->customer->get_shipping_country();

				$shipping_methods = get_option( 'coolrunner_wc_curl_data' );

				$cost=0;

				if( $shipping_methods ){

					foreach($shipping_methods as $shipping_method){

						foreach($shipping_method as $entry){
							if(is_array($entry) && $shipping_method['name'] == 'coolrunner_dhl_private_droppoint'){
								if($weight < $entry['weight_to'] && $entry['zone_to']==$shipping_country){
									$cost = $entry['price_excl_tax'];
									break;
								}
							}
						}

					}
				}


			    $this->add_rate( array(
			      'id' 	=> $this->id,
			      'label' => $this->title,
			      'cost' 	=> $cost
			    ));
			}

		}
	}


	if ( ! class_exists( 'WC_coolrunner_pdk_international' ) ) {
		class WC_coolrunner_pdk_international extends WC_Shipping_Method {

			public function __construct( $instance_id = 0 ) {
				$this->id               = 'coolrunner_pdk_international';
				$this->instance_id 	    = absint( $instance_id );

				$this->init_form_fields();
	  		    $this->init_settings();

				$this->method_title     = __( 'CoolRunner - Post Nord International' );
                $this->cost             = 0;
                $this->title            = $this->get_option( 'title' );

				$this->supports     = array(
					'shipping-zones',
					'instance-settings',
					'instance-settings-modal',
				);
				add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );

				}

				public function init_form_fields(){

					$this->instance_form_fields = array(
						'title' => array(
							'title'         => __( 'Method Title', 'coolrunner-shipping-plugin'),
							'type'          => 'text',
							'description'   => __( 'This controls the title which the user sees during checkout.', 'coolrunner-shipping-plugin'),
							'default'       => __( 'CoolRunner - Post Nord International', 'coolrunner-shipping-plugin'),
						),
					  'cost' => array(
							'title'         => __( 'Cost', 'coolrunner-shipping-plugin'),
							'type'          => 'text',
							'description'   => __( 'This controls the priกce of the shipping', 'coolrunner-shipping-plugin'),
							'default'       => __( '0', 'coolrunner-shipping-plugin'),
					  )
					);
				}

			//	add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );

				public function calculate_shipping( $package = array() ) {
			    $this->add_rate( array(
			      'id' 	=> $this->id,
			      'label' => $this->title,
			      'cost' 	=> $this->get_option('cost')
			    ));
				}

			
		}
	}

	if ( ! class_exists( 'WC_coolrunner_pdk_international_private' ) ) {
		class WC_coolrunner_pdk_international_private extends WC_Shipping_Method {

			public function __construct( $instance_id = 0 ) {
				$this->id             = 'coolrunner_pdk_international_private';
				$this->instance_id 	= absint( $instance_id );

				$this->init_form_fields();
	  		$this->init_settings();

				$this->method_title   = __( 'CoolRunner - Post Nord International Privat' );
				$this->cost			= 0;
		  	$this->title 		= $this->get_option( 'title' );

				$this->supports     = array(
					'shipping-zones',
					'instance-settings',
					'instance-settings-modal',
				);

				add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
			}

			public function init_form_fields(){

				$this->instance_form_fields = array(
					'title' => array(
						'title'         => __( 'Method Title', 'coolrunner-shipping-plugin'),
						'type'          => 'text',
						'description'   => __( 'This controls the title which the user sees during checkout.', 'coolrunner-shipping-plugin'),
						'default'       => __( 'CoolRunner - Post Nord International Privat', 'coolrunner-shipping-plugin'),
					),
				    'cost' => array(
						'title'         => __( 'Cost', 'coolrunner-shipping-plugin'),
						'type'          => 'text',
						'description'   => __( 'This controls the price of the shipping', 'coolrunner-shipping-plugin'),
						'default'       => __( '0', 'coolrunner-shipping-plugin'),
				    )
				);
			}

			public function calculate_shipping( $package = array() ) {
		    $this->add_rate( array(
		      'id' 	=> $this->id,
		      'label' => $this->title,
		      'cost' 	=> $this->get_option('cost')
		    ));
			}
		}
	}
}
add_action( 'woocommerce_shipping_init', 'CR_shipping_methods_init' );

function CR_add_shipping_methods( $methods ) {

	$shipping_methods = get_option( 'coolrunner_wc_curl_data' );

	if( $shipping_methods ){
		foreach($shipping_methods as $shipping_method){

			$maximum_weight = 0;

			foreach($shipping_method as $entry){
				if(is_array($entry)){
					if($maximum_weight <= $entry['weight_to']){
						$maximum_weight = $entry['weight_to'];
					}
				}
			}
			$methods[$shipping_method['name']] = 'WC_' . $shipping_method['name'];

			update_option( $shipping_method['name'] . '_max_weight', $maximum_weight );

			//var_dump($shipping_method['name'] . '_max_weight');
		}

		return $methods;
	}

	return $methods;
}

add_filter( 'woocommerce_shipping_methods', 'CR_add_shipping_methods' );