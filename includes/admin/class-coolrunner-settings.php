<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WC_Settings_Coolrunner' ) ) :

	function CR_add_coolrunner_settings() {
		/**
		 * Settings class
		 *
		 * @since 1.0.0
		 */
		class WC_Settings_Coolrunner extends WC_Settings_Page {

			/**
			 * Setup settings class
			 *
			 * @since  1.0
			 */
			public function __construct() {

				$this->id    = 'coolrunner';
				$this->label = __( 'Coolrunner', 'coolrunner-shipping-plugin' );

				add_filter( 'woocommerce_settings_tabs_array',        array( $this, 'add_settings_page' ), 20 );
				add_action( 'woocommerce_settings_' . $this->id,      array( $this, 'output' ) );
				add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
				add_action( 'woocommerce_sections_' . $this->id,      array( $this, 'output_sections' ) );
				add_action( 'woocommerce_get_settings_for_' . $this->id, array($this, 'get_option'));
			}


			/**
			 * Get sections
			 *
			 * @return array
			 */
			public function get_sections() {

				$sections = array(
					'General' => __( 'General', 'coolrunner-shipping-plugin' ),
					'Package' => __( 'Package', 'coolrunner-shipping-plugin' ),
				);

				return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );
			}

			/**
			 * Get settings array
			 *
			 * @since 1.0.0
			 * @param string $current_section Optional. Defaults to empty string.
			 * @return array Array of settings
			 */
			public function get_settings( $current_section = '' ) {

				$menu = array(
					array(
						'name' 	=> __( 'CoolRunner General', 'coolrunner-shipping-plugin' ),
						'type' 	=> 'title',
						'desc'	=> '',
						'id'   	=> 'coolrunner_settings',
					),

					array(
                        'name'      => __('CoolRunner Username', 'coolrunner-shipping-plugin'),
						'type' 		=> 'text',
						'id' 		=> 'coolrunner_settings_username',
						'desc_tip' 	=>  true,
						'desc'    	=> __( 'Input your CoolRunner username', 'woocommerce' ),
					),
					array(
						'name'		=> __('CoolRunner Token', 'coolrunner-shipping-plugin'),
						'type'		=> 'text',
						'id' 		=> 'coolrunner_settings_token',
						'desc_tip' 	=>  true,
						'desc'    	=> __( 'Input your CoolRunner token', 'woocommerce' ),
					),
					array(
						'name' 			=> __('Sender country', 'coolrunner-shipping-plugin'),
						'type' 			=> 'text',
						'id' 			=> 'coolrunner_settings_sender_country',
						'desc_tip' 	    =>  true,
						'desc'    	    => __( 'Input your Sender country, remeber to only use 2 characters!', 'woocommerce' ),
					),
					array(
						'name'			=> __('Label format', 'coolrunner-shipping-plugin'),
						'type'			=> 'select',
						'id' 			=> 'coolrunner_settings_label_format',
						'desc_tip' 	    => true,
						'desc'    	    => __( 'Choose which type your label should be in', 'woocommerce' ),
						'default' 	    => 'A4',
						'options' 	    => array(
							'A4' 		   => __('A4', 'coolrunner-shipping-plugin'),
							'LabelPrint'   => __('LabelPrint', 'coolrunner-shipping-plugin'),
							'dao60x100'   => __('dao60x100', 'coolrunner-shipping-plugin'),
						),						
					),
					array(
						'name' 			=> __('Auto Send email', 'coolrunner-shipping-plugin'),
						'type' 			=> 'select',
						'id' 			=> 'coolrunner_settings_send_email',
						'desc_tip' 	    =>  true,
						'desc'    	    => __( 'Choose witch email to send to customer!', 'woocommerce' ),
						'default' 	    => 'Yes',
						'options' 	    => array(
							'yes' 		   => __('Yes', 'coolrunner-shipping-plugin'),
							'no'   => __('No', 'coolrunner-shipping-plugin'),
						),								
					),
					array(
						'name' 			=> __('LabelPrinted auto status', 'coolrunner-shipping-plugin'),
						'type' 			=> 'select',
						'id' 			=> 'coolrunner_settings_auto_status',
						'desc_tip' 	    =>  true,
						'desc'    	    => __( 'Choose witch auto status after create label!', 'woocommerce' ),
						'default' 	    => '',
						'options' 	    => array(
							'' => __('No Status', 'coolrunner-shipping-plugin'),
							'Printed Label'   => __('Printed Label', 'coolrunner-shipping-plugin'),
							'In Packet Store'   => __('In Packet Store', 'coolrunner-shipping-plugin'),
						),								
					),
					array(
						'name' 			=> __('Number of droppoint', 'coolrunner-shipping-plugin'),
						'type' 			=> 'select',
						'id' 			=> 'coolrunner_settings_number_droppoint',
						'desc_tip' 	    =>  true,
						'desc'    	    => __( 'Choose witch number of droppoint!', 'woocommerce' ),
						'default' 	    => '10',
						'options' 	    => array(
							'5' 		   => __('5', 'coolrunner-shipping-plugin'),
							'6'   => __('6', 'coolrunner-shipping-plugin'),
							'7'   => __('7', 'coolrunner-shipping-plugin'),
							'8'   => __('8', 'coolrunner-shipping-plugin'),
							'9'   => __('9', 'coolrunner-shipping-plugin'),
							'10'   => __('10', 'coolrunner-shipping-plugin'),
							'11'   => __('11', 'coolrunner-shipping-plugin'),
							'12'   => __('12', 'coolrunner-shipping-plugin'),
							'13'   => __('13', 'coolrunner-shipping-plugin'),
							'14'   => __('14', 'coolrunner-shipping-plugin'),
							'15'   => __('15', 'coolrunner-shipping-plugin'),
							'16'   => __('16', 'coolrunner-shipping-plugin'),
							'17'   => __('17', 'coolrunner-shipping-plugin'),
							'18'   => __('18', 'coolrunner-shipping-plugin'),
							'19'   => __('19', 'coolrunner-shipping-plugin'),
							'20'   => __('20', 'coolrunner-shipping-plugin'),
						),								
					),
				);
				$menu[] = array(
					'type' => 'secionend',
					'id'   => 'coolrunner_settings'
				);
				
				$menu_size = array(
					array(
					'name' 	=> __( 'Package sizes', 'coolrunner-shipping-plugin' ),
					'type' 	=> 'title',
					'desc'	=> '',
					'id'   	=> 'coolrunner_settings',
					),
					array(
                        'name'      => __('Package name', 'coolrunner-shipping-plugin'),
						'type' 		=> 'text',
						'id' 		=> 'coolrunner_package_name',
						'desc_tip' 	=>  true,
						'desc'    	=> __( 'Input your package name', 'woocommerce' ),
					),
					array(
                        'name'      => __('Weight', 'coolrunner-shipping-plugin'),
						'type' 		=> 'text',
						'id' 		=> 'coolrunner_weight',
						'desc_tip' 	=>  true,
						'desc'    	=> __( 'Input your package weight', 'woocommerce' ),
					),
					array(
                        'name'      => __('Length', 'coolrunner-shipping-plugin'),
						'type' 		=> 'text',
						'id' 		=> 'coolrunner_length',
						'desc_tip' 	=>  true,
						'desc'    	=> __( 'Input your package length', 'woocommerce' ),
					),
					array(
                        'name'      => __('Width', 'coolrunner-shipping-plugin'),
						'type' 		=> 'text',
						'id' 		=> 'coolrunner_width',
						'desc_tip' 	=>  true,
						'desc'    	=> __( 'Input your package width', 'woocommerce' ),
					),
					array(
                        'name'      => __('Height', 'coolrunner-shipping-plugin'),
						'type' 		=> 'text',
						'id' 		=> 'coolrunner_height',
						'desc_tip' 	=>  true,
						'desc'    	=> __( 'Input your package height', 'woocommerce' ),
					),
				);



			

				if ( '' == $current_section || $current_section == 'general' ) {
					$settings = apply_filters( 'coolrunner_settings', $menu );
					
				}else{

					$settings = apply_filters( 'coolrunner_settings', $menu_size );
				}
				return apply_filters( 'woocommerce_get_settings_' . $this->id, $settings, $current_section );
			}

			/**
			 * Output the settings
			 *
			 * @since 1.0
			 */
			public function output() {

				global $current_section, $wpdb;

				if ( '' == $current_section || $current_section == 'general' ) {
					$settings = $this->get_settings( $current_section );		
					WC_Admin_Settings::output_fields( $settings );		
				}else{
					if (isset($_GET['option_id'])){
						// Default usage.
						$option_id = $_GET['option_id'];
						$wpdb->delete( $wpdb->options, array( 'option_id' => $option_id  ) );
					}
					?>
					<h2 class="title_package">Package sizes</h2>
					<p></p>
				<table class="form-table">			
					<tbody><tr valign="top">
									<th scope="row" class="titledesc">
										<label for="coolrunner_package_name">Package name</label>
										<span class="woocommerce-help-tip" data-tip="Input your package name"></span>					
									</th>
									<td class="forminp forminp-text">
										<input name="coolrunner_package_name" id="coolrunner_package_name" type="text" style="" value="" class="" placeholder="" autocomplete="off"> 						</td>
								</tr><tr valign="top">
									<th scope="row" class="titledesc">
										<label for="coolrunner_weight">Weight (kg)</label>
										<span class="woocommerce-help-tip" data-tip="Input your package weight"></span>						
									</th>
									<td class="forminp forminp-text">
									<select name="coolrunner_weight" style="height:auto;">
										<option value="500">Under 500 g</option>		
										<option value="1000">500 g - 1 kg</option>
										<option value="2000">1 - 2 kg</option>
										<option value="3000">2 - 3 kg</option>
										<option value="5000">3 - 5 kg</option>   
										<option value="10000">5 - 10 kg</option>
										<option value="15000">10 - 15 kg</option>
										<option value="20000">15 - 20 kg</option>;
										<option value="30000">20 - 30 kg</option>
									</select>					
		
									</td>
								</tr><tr valign="top">
									<th scope="row" class="titledesc">
										<label for="coolrunner_length">Length (cm)</label>
										<span class="woocommerce-help-tip" data-tip="Input your package length"></span>		
									</th>
									<td class="forminp forminp-text">
										<input name="coolrunner_length" id="coolrunner_length" type="text" style="" value="" class="" placeholder=""> 						</td>
								</tr><tr valign="top">
									<th scope="row" class="titledesc">
										<label for="coolrunner_width">Width (cm)</label>
										<span class="woocommerce-help-tip" data-tip="Input your package width"></span>						</th>
									<td class="forminp forminp-text">
										<input name="coolrunner_width" id="coolrunner_width" type="text" style="" value="" class="" placeholder=""> 						</td>
								</tr><tr valign="top">
									<th scope="row" class="titledesc">
										<label for="coolrunner_height">Height (cm)</label>
										<span class="woocommerce-help-tip" data-tip="Input your package height"></span>							</th>
									<td class="forminp forminp-text">
										<input name="coolrunner_height" id="coolrunner_height" type="text" style="" value="" class="" placeholder=""> 						</td>
								</tr>
					</tbody>
				</table>

			<table class="wp-list-table widefat fixed">
				<thead>	
			<tr>
					<th>Package name</th>
					<th>Weight (kg)</th>
					<th>Length (cm)</th>
					<th>Width (cm)</th>
					<th>Height (cm)</th>
					<th>Actions</th>
			</tr>
					</thead>
			<?php
				$sql= "
				SELECT 
					option_id, 
					option_name, 
					option_value 
				FROM $wpdb->options
				WHERE option_name LIKE 'coolrunner_package%'
				";

				$packages = $wpdb->get_results($sql);

				$url = admin_url('admin.php?page=wc-settings&tab=coolrunner&section=package');
				foreach ( $packages as $key => $package ) 
				{
					echo "<tr>";
					$package_name = unserialize($package->option_value);
						echo "<td>".$package_name['coolrunner_package_name']."</td>";
						echo "<td>".($package_name['coolrunner_weight']/1000)."</td>";
						echo "<td>".$package_name['coolrunner_length']."</td>";
						echo "<td>".$package_name['coolrunner_width']."</td>";
						echo "<td>".$package_name['coolrunner_height']."</td>";
						echo "<td><a href=\"".$url."&option_id=".$package->option_id."\">Delete</a></td>";
					echo "</tr>";
				}
			?>
			</table>
			<script type= text/javascript>
				jQuery( document ).ready(function() {
					jQuery('h2.title_package').after(jQuery(".woocommerce-save-button"));
				});
			</script>
				<?php
				}			

			}

			/**
		 	 * Save settings
		 	 *
		 	 * @since 1.0
			 */
			public function save() {

				global $current_section;
				$data = CR_register_customer_shipping();

                if($data) {
                    update_option('coolrunner_wc_curl_data', $data);
                }
				$settings = $this->get_settings( $current_section );
				
				if ( '' == $current_section || $current_section == 'general' ) {
					WC_Admin_Settings::save_fields( $settings );					
				}else{
					$coolrunner_package_name = $_POST['coolrunner_package_name'];
					$coolrunner_weight = $_POST['coolrunner_weight'];
					$coolrunner_length = $_POST['coolrunner_length'];
					$coolrunner_width = $_POST['coolrunner_width'];
					$coolrunner_height = $_POST['coolrunner_height'];
					
					$coolrunner_package_name = array(
													"coolrunner_package_name" => $coolrunner_package_name,
													"coolrunner_weight" => $coolrunner_weight,
													"coolrunner_length" => $coolrunner_length,
													"coolrunner_width" => $coolrunner_width,
													"coolrunner_height" => $coolrunner_height,
												);

					update_option('coolrunner_package_'.$coolrunner_length.'_'.$coolrunner_width.'_'.$coolrunner_height.'_'.$coolrunner_weight  , $coolrunner_package_name);
				}

			}
		}
		return new WC_Settings_Coolrunner();
	}

add_filter( 'woocommerce_get_settings_pages', 'CR_add_coolrunner_settings', 15 );

endif;

