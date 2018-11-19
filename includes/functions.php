<?php

function CR_register_customer_shipping(){

	$username = get_option('coolrunner_settings_username');
	$token = get_option('coolrunner_settings_token');
	$destination = "v2/freight_rates/" . get_option('coolrunner_settings_sender_country');
	$curldata = "";

	$curl = new CR_Curl();
	$response = $curl->sendCurl($destination, $username, $token, $curldata, $header_enabled = false, $json = true);

	if($response->status == "ok"){
		$country_shipping = $response->result;
	//	print_r($country_shipping);
		$data = array();

		foreach($country_shipping as $country){

			foreach($country as $country_shipping_options){

				$country_shipping_options = get_object_vars($country_shipping_options);

				$carrier_option = "coolrunner_{$country_shipping_options["carrier"]}_{$country_shipping_options["carrier_product"]}";

				if( $country_shipping_options["carrier_service"] != "" ){
					$carrier_option .= "_" . $country_shipping_options["carrier_service"];
				}
				$data[$carrier_option]['name'] = $carrier_option;
				$data[$carrier_option]['zone_from'] = $country_shipping_options['zone_from'];
			//	$data[$carrier_option]['zone_to'] = $country_shipping_options['zone_to'];

				$data[$carrier_option][$country_shipping_options['title'].$country_shipping_options['zone_to']] = array(
					'name' => $country_shipping_options['title'],
					'zone_to' => $country_shipping_options['zone_to'],
					'weight_from' => $country_shipping_options['weight_from'],
					'weight_to' => $country_shipping_options['weight_to'],
					'price_excl_tax' => $country_shipping_options['price_excl_tax'],
					'price_incl_tax' => $country_shipping_options['price_incl_tax'],
				);
			}
		}
	}

//	 print_r($data);

	if(! empty($data)){
		return $data;
	}
	return false;
}


function CR_add_coolrunner_pickup_to_checkout() {

	$chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
 	$chosen_shipping = $chosen_methods[0];

	$matches = array();
 	$matches = explode('_', $chosen_shipping);

 //	if($matches[0] == "coolrunner" && end($matches) == "droppoint"){
	?>
		<div class="coolrunner_select_shop" name="coolrunner_select_shop">
			<h3><?php echo __( 'Choose package shop', 'coolrunner-shipping-plugin' ); ?></h3>
			<p><?php echo __( 'Choose where you want your package to be dropped off, if none is chosen we will choose the closest for to your delivery address', 'coolrunner-shipping-plugin' ); ?></p>

			<span>
				<label for="coolrunner_zip_code_search">
					<?php echo __( 'Input Zipcode', 'coolrunner-shipping-plugin' ); ?>
				</label>
				<input type="text" id="coolrunner_zip_code_search" name="coolrunner_zip_code_search">
			</span>

			<button data-fancybox="modal" data-src="#coolrunner-dao-droppoint-wrapper" data-modal="true" data-width="1000" type="button" id="coolrunner_search_droppoints" name="coolrunner_search_droppoints" > <?php echo __( 'Search for package shop', 'coolrunner-shipping-plugin' ); ?></button>
			<div id="coolrunner-dao-droppoint-wrapper">
				<div style="text-align:center"></style><img src="<?php echo plugins_url('assets/images/ajax-loader.gif', dirname(__FILE__) ); ?>" style="display:inline-block"></div>
			</div>
			<div id="coolrunner-address">		
					
			</div>
		</div>
	<?php

//	}


}
add_action('woocommerce_review_order_before_payment','CR_add_coolrunner_pickup_to_checkout');

add_action( 'wp_ajax_nopriv_post_search', 'CR_post_search' );
add_action( 'wp_ajax_post_search', 'CR_post_search' );

function CR_post_search() {

	global $woocommerce;


	$chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
 	$chosen_shipping = $chosen_methods[0];
 	$shipping_country = $woocommerce->customer->get_shipping_country();

	$curl = new CR_Curl();
	$matches = explode('_', $chosen_shipping);

	$number_droppoint = get_option('coolrunner_settings_number_droppoint');

	$curldata = array(
		"carrier"=> $matches[1] ,
		"country_code" => $shipping_country,//get_option('coolrunner_settings_sender_country'),
		"zipcode" => $_REQUEST['post_code'],
		"city" =>"",
		"street" => "",
		"number_of_droppoints" => $number_droppoint
	);

	$chosen_shipping = substr($chosen_shipping, 11, 3);

	$destination = "v2/droppoints/";

	$response = $curl->sendCurl($destination, get_option('coolrunner_settings_username'), get_option('coolrunner_settings_token'), $curldata, $header_enabled = false, $json = false);

	//print_r( $response->result);

	if($response->status == "ok"){
		$list = $response->result;

	//	$html =  '<select name="droppoint_selection" id="droppoint_selection" class="input-text">';
	//	$html .= '<option value="">Choose location</option>';	
		$markers="var markers = [";		
		$html="<ul id=\"coolrunner-dao-addresses\">";	
		$droppoint ="";
		foreach($list as $entry){
			$html .= '<li><input type="radio" name="droppoint_selection" class="coolrunner-dao-radio droppoint_selection"
			id="' . $entry->droppoint_id . '" 
			data-name="'.$entry->name.'" 
			data-street="'.$entry->address->street.'" 
			data-zipcode="'.$entry->address->postal_code.'" 
			data-city="'.$entry->address->city.'" 
			data-country_code="'.$entry->address->country_code.'" 
			value="' . $entry->droppoint_id . '"> <div class="coolrunner-dao-address"><label>' . implode(' ' , array("<h4>".$entry->name."</h4>", $entry->address->street, $entry->address->postal_code, $entry->address->city)) . '</label></div><li>';

			$markers .= "['".addslashes($entry->name)."',".$entry->coordinate->latitude.",".$entry->coordinate->longitude.",".$entry->droppoint_id."],";

			$droppoint .="<div class=\"coolrunner-dao-detail\" id=\"dp".$entry->droppoint_id."\"><div class=\"coolrunner-dao-opening-header\">ADRESSE & ÅBNINGSTIDER</div>";
			$droppoint .="<div class=\"coolrunner-dao-content\"><span>".$entry->name."</span><br />".$entry->address->street."<br>".$entry->address->postal_code." ".$entry->address->city."<br></div>";
			$droppoint .="<span>Åbningstider</span>";
			$droppoint .="<ul>";
			foreach ($entry->opening_hours as $value){
				$droppoint .="<li><div class=\"coolrunner-dao-weekday\">".$value->weekday."</div>
								<div class=\"coolrunner-dao-openinghours\"><span class=\"from\">".$value->from."</span><span class=\"to\">".$value->to."</span></div>
						  	</li>";
			}
			$droppoint .= "</ul></div>";

		}

		$html .="</ul>";

	//	$markers = subster($markers,0,-1);
		$markers .="];";

	//	$html .= '</select>';

	
	}else{
		echo "No Droppoints were found";
	}


	
	echo '	
	<div id="coolrunner-dao-droppoint-header"><h2>Vælg afhentningssted</h2></div>
	<div id="coolrunner-dao-droppoint-result" class="clearfix" style="height: 500px;">	
		<div id="map_wrapper" style="height: 500px;">
			<div id="map_canvas" class="mapping"></div>
		</div>
		<div id="coolrunner-search-results" style="height: 500px;">'.$html.'</div>
		<div id="coolrunner-dao-opening-container" style="height: 500px;"><span id="coolrunner-dao-pre">Vælg et afhentningssted i venstre side, og se åbningstiderne her</span>'.$droppoint.'</div>
	</div>
	<div id="coolrunner-dao-droppoint-buttons-set">			

		<button type="button" id="coolrunner-dao-close-droppoint-selector-remove" data-fancybox-close>				
		Vælg						</button>			
	</div>

	';

echo "<script type='text/javascript'> 
		$markers		
	</script>";


	
/*	
	global $woocommerce;
	$shipping_country = $woocommerce->customer->get_shipping_country();
	echo $shipping_country;
*/
	exit();
}

add_action( 'wp_ajax_nopriv_post_search', 'CR_post_droppoint' );
add_action( 'wp_ajax_post_droppoint', 'CR_post_droppoint' );
function CR_post_droppoint() {
	global $woocommerce;	
	$droppoint_id = $_REQUEST['droppoint_id'];

	$html ="<div class=\"coolrunner-dao-opening-header\"></div>";
	$html .="<div class=\"coolrunner-dao-content\"><span>Kiosk 74</span><br>Sjællandsgade 74<br>9000 Aalborg<br></div>";
	$html .="<span>Åbningstider</span>";
	$html .="<ul>";
	$html .="<li><div class=\"coolrunner-dao-weekday\">Mandag</div><div class=\"coolrunner-dao-openinghours\"><span class=\"from\">10:00</span><span class=\"to\">21:00</span></div></li>";
	$html .= "</ul>";
		echo $html;	
		exit();
	}
	

add_action( 'woocommerce_after_checkout_form', 'CR_add_coolrunner_droppoint_checkout_script' );

//do_action( 'woocommerce_checkout_order_processed', $order_id, $posted_data, $order );
function CR_add_coolrunner_droppoint_checkout_script(){
	
	?>
	<script type="text/javascript" >

jQuery( document ).ready(function() {
    jQuery(document).on('keydown','#coolrunner_zip_code_search', function(e) {
        if(e.keyCode === 13) {
            e.preventDefault();
            jQuery('#coolrunner_search_droppoints').click();
            return false;
        }
    });
	if (jQuery('#ship-to-different-address-checkbox').is(':checked')){
		jQuery( document ).on('keyup','#shipping_postcode',function() {					
			var zipcode=jQuery(this).val();	
			jQuery('#coolrunner_zip_code_search').val(zipcode);
		});
	}else{
		jQuery( document ).on('keyup','#billing_postcode',function() {
			var zipcode=jQuery(this).val();
			jQuery('#coolrunner_zip_code_search').val(zipcode);
		});
	}
});

jQuery( document ).on('click', '#ship-to-different-address-checkbox',function() {
	var zipcode=jQuery('#billing_postcode').val();
	jQuery('#coolrunner_zip_code_search').val(zipcode);	

	if (jQuery('#ship-to-different-address-checkbox').is(':checked')){
		jQuery( document ).on('keyup','#shipping_postcode',function() {					
			var zipcode=jQuery(this).val();	
			jQuery('#coolrunner_zip_code_search').val(zipcode);
		});
	}else{
		jQuery( document ).on('keyup','#billing_postcode',function() {
			var zipcode=jQuery(this).val();
			jQuery('#coolrunner_zip_code_search').val(zipcode);
		});
	}
});

	jQuery(document).on('click' , '.droppoint_selection', function() {
	  var id = -1;
	  var droppoint_name = jQuery(this).data('name');
	  var droppoint_street = jQuery(this).data('street');
	  var droppoint_zipcode = jQuery(this).data('zipcode');
	  var droppoint_city = jQuery(this).data('city');
	  var droppoint_country_code = jQuery(this).data('country_code');
	  var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
//	  alert(ajaxurl);

	  jQuery.ajax({
		  method: "POST",
		  url: ajaxurl,
		  data: {
			  'action': 'coolrunner_save_droppoint',
			  'id': id,
			  'droppoint_name': droppoint_name,
			  'droppoint_street': droppoint_street,
			  'droppoint_zipcode' : droppoint_zipcode,
			  'droppoint_city' : droppoint_city,
			  'droppoint_country_code' : droppoint_country_code
		  }
	  })
	  .done(function( data ) {	
		data = data.substring(0, data.length - 1);
		jQuery('#coolrunner-address').html(data);
	  })
	  .fail(function( data ) {
		  alert('Error sending');
	  });
	  
  })

</script>	
<?php
}

function coolrunner_save_droppoint() {
		if($_POST['id']=='-1'){
			$order_id= 9999;
			$droppoint_name = $_POST['droppoint_name'];				
			$droppoint_street = $_POST['droppoint_street'];			
			$droppoint_zipcode = $_POST['droppoint_zipcode'];			
			$droppoint_city	= $_POST['droppoint_city'];	
			$droppoint_country_code = $_POST['droppoint_country_code'];	
		
			update_post_meta( $order_id, 'coolrunner_customer_droppoint_name', $droppoint_name );
			update_post_meta( $order_id, 'coolrunner_customer_droppoint_street', $droppoint_street );
			update_post_meta( $order_id, 'coolrunner_customer_droppoint_zipcode', $droppoint_zipcode );
			update_post_meta( $order_id, 'coolrunner_customer_droppoint_city', $droppoint_city );
			update_post_meta( $order_id, 'coolrunner_customer_droppoint_country_code', $droppoint_country_code );
			 
		//	echo "complete".$_POST['id'];

			//update droppoint

			$address= "<h4>".$droppoint_name.'</h4>'.$droppoint_street.' '.$droppoint_city.' '.$droppoint_zipcode.' '.$droppoint_country_code;
			echo $address;	

		}else{
			echo "ID was not found";
		}
	
	}
	add_action( 'wp_ajax_nopriv_coolrunner_save_droppoint', 'coolrunner_save_droppoint' );
	add_action( 'wp_ajax_coolrunner_save_droppoint', 'coolrunner_save_droppoint' );

//add_action( 'wp_ajax_coolrunner_save_droppoint', 'coolrunner_save_droppoint' );

add_action( 'woocommerce_checkout_update_order_meta', 'CR_add_order_meta', 10, 2 );
function CR_add_order_meta( $order_id, $posted ) {

	$chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
	$chosen_shipping = $chosen_methods[0];
	$key = 'coolrunner_customer_droppoint';
 	$array = array ('coolrunner_length','coolrunner_width','coolrunner_height','coolrunner_weight','coolrunner_printed');
 	$matches = array();

 	$matches = explode('_', $chosen_shipping);

 	if($matches[0] == "coolrunner" && end($matches) == "droppoint"){

 		if($_POST['droppoint_selection'] == ''){
 			$droppoint_id = 0;
 		}else{
 			$droppoint_id = $_POST['droppoint_selection'];
 		}

 		update_post_meta( $order_id, $key, $droppoint_id );
	 }
	 
	 foreach($array as $key2){
		update_post_meta( $order_id, $key2, 0 );
	 }


}

function CR_my_custom_order_api_fields( $product_data, $product ) {
	$product_data['coolrunner_customer_droppoint'] = get_post_meta( $product->id, 'coolrunner_customer_droppoint', true );
	$product_data['coolrunner_length'] = get_post_meta( $product->id, 'coolrunner_length', true );
	$product_data['coolrunner_width'] = get_post_meta( $product->id, 'coolrunner_width', true );
	$product_data['coolrunner_height'] = get_post_meta( $product->id, 'coolrunner_height', true );
	$product_data['coolrunner_weight'] = get_post_meta( $product->id, 'coolrunner_weight', true );
	
    $product_data['domain'] = home_url();

    return $product_data;
}

add_filter( 'woocommerce_api_order_response', 'CR_my_custom_order_api_fields', 10, 3 );

function CR_custom_checkout_field_display_admin_order_meta($order){

	$shipping_methods = $order->get_items( 'shipping' );
	foreach($shipping_methods as $shipping_method){
		$shipping_method = $shipping_method;
	}

	$shipping_method = $shipping_method['method_id'];
	

	if(strpos($shipping_method, 'coolrunner_') == 'coolrunner_'){
//		echo '<p><strong>'.__('Coolrunner Droppoint ID').':</strong> ' . get_post_meta( $order->id, 'coolrunner_customer_droppoint', true ) . '';

		$matches = explode('_', $shipping_method);

		$droppoint = ($matches[0] == "coolrunner" && end($matches) == "droppoint")?true:false;
		if ($droppoint){

			echo '<p><strong>'.__('Droppoint Address').':</strong> ';
			echo get_post_meta( $order->id, 'coolrunner_customer_droppoint_name', true ) . ' ';
			echo get_post_meta( $order->id, 'coolrunner_customer_droppoint_street', true ) . ' ';
			echo get_post_meta( $order->id, 'coolrunner_customer_droppoint_city', true ) . ' ';
			echo get_post_meta( $order->id, 'coolrunner_customer_droppoint_zipcode', true ) . ' ';
			echo get_post_meta( $order->id, 'coolrunner_customer_droppoint_country_code', true ) . ' ';
		}

		if (get_post_meta( $order->id, 'coolrunner_weight', true )){
			$weight = get_post_meta( $order->id, 'coolrunner_weight', true )/1000;
		}

		echo '<p><strong>'.__('Coolrunner Height').':</strong> ' . get_post_meta( $order->id, 'coolrunner_height', true ) . ' cm';
		echo '<br /><strong>'.__('Coolrunner Length').':</strong> ' . get_post_meta( $order->id, 'coolrunner_length', true ) . ' cm';
		echo '<br /><strong>'.__('Coolrunner Width').':</strong> ' . get_post_meta( $order->id, 'coolrunner_width', true ) . ' cm';
		echo '<br /><strong>'.__('Coolrunner Weight').':</strong> ' .  $weight. ' kg</p>';
  }  
  	
	if(strpos($shipping_method, 'coolrunner_') == 'coolrunner_'){
?>		
	<?php if (get_post_meta(  $order->id, 'coolrunner_printed', true )==0){ ?>
		<a class="coolrunner" id="coolrunner_ajax_resend_call<?php echo $order->id ?>" name="coolrunner_ajax_resend_call" data-order-id="<?php echo $order->id ?>" style="height:2em; cursor: pointer;">	
			<img src="<?php bloginfo('wpurl') ?>/wp-content/plugins/coolrunner-shipping-plugin/assets/images/coolrunner-create.png" />
		</a>
	<?php }else{ 		
		?>
		<a class="coolrunner"  id="coolrunner_ajax_resend_call<?php echo $order->id ?>" name="coolrunner_call_pdf" data-order-id="<?php echo $order->id ?>"style="height:2em; cursor: pointer;" target="_blank">	
			<img src="<?php bloginfo('wpurl') ?>/wp-content/plugins/coolrunner-shipping-plugin/assets/images/coolrunner-printed.png" />
		</a>
	<?php } ?>
		</a>
		
	
<!--		
		<a class="coolrunner" data-order-id="<?php echo $order->id ?>" style="height:2em; cursor: pointer; cursor: pointer;">
			<img src="<?php bloginfo('wpurl') ?>/wp-content/plugins/coolrunner-shipping-plugin/assets/images/coolrunner-create.png">
		</a>
		-->
	<?php
	}
}
add_action( 'woocommerce_admin_order_data_after_shipping_address', 'CR_custom_checkout_field_display_admin_order_meta', 10, 1 );


function CR_admin_order_action_filter($order){
	$shipping_methods = $order->get_items( 'shipping' );
	foreach($shipping_methods as $shipping_method){
		$shipping_method = $shipping_method;
	}

	$shipping_method = $shipping_method['method_id'];

//	$order = new WC_Order($_POST['id']);
	
	if(strpos($shipping_method, 'coolrunner_') == 'coolrunner_'){
		
	?>
	
	<?php if (get_post_meta(  $order->id, 'coolrunner_printed', true )==0){ ?>
		<a class="coolrunner" id="coolrunner_ajax_resend_call<?php echo $order->id ?>" name="coolrunner_ajax_resend_call" data-order-id="<?php echo $order->id ?>" style="height:2em; cursor: pointer;">	
			<img src="<?php bloginfo('wpurl') ?>/wp-content/plugins/coolrunner-shipping-plugin/assets/images/coolrunner-create.png" />
		</a>                                                                        
	<?php }else{ 		
	
		?>
		<a class="coolrunner"  id="coolrunner_ajax_resend_call<?php echo $order->id ?>" name="coolrunner_call_pdf" data-order-id="<?php echo $order->id ?>"style="height:2em; cursor: pointer;" target="_blank">	
			<img src="<?php bloginfo('wpurl') ?>/wp-content/plugins/coolrunner-shipping-plugin/assets/images/coolrunner-printed.png" >
		</a>
	<?php } 
	}
}
add_filter('woocommerce_admin_order_actions_end','CR_admin_order_action_filter',5,2);

function coolrunner_ajax_resend_label_script() {

	?><script type="text/javascript" >
  		jQuery(document).on('click' , '[name="coolrunner_ajax_resend_call"]', function() {
			var id = jQuery(this).data('order-id');

			jQuery.ajax({
				method: "POST",
				url: ajaxurl,
				data: {
					'action': 'coolrunner_resend_label_notification',
					'id': id
				}
			})			
    		.done(function( data ) {
			//	alert(data);
				var obj = jQuery.parseJSON( data );
				jQuery('[name="coolrunner_notification"]').remove();
      		if(obj.status == 'ok'){
			//	  alert(obj.result.pdf_link);
				  
      			jQuery("[id='wpbody-content']").prepend( '<div name="coolrunner_notification" class="notice updated"><p>Create new shipment sucessfully</p></div>' );
				  location.reload();
      		}else{
				alert(obj.message);
      		//	jQuery('name="coolrunner_notification"').remove();
      			jQuery("#wpbody-content").prepend( '<div name="coolrunner_notification" class="notice notice-error"><p> Error create new shipment </p></div>' );
      		}
    		})
    		.fail(function( data ) {
      			jQuery("#wpbody-content").prepend( '<div class="notice notice-error"><p> Error, could not import order</p></div>' );
    		});
			
    	})

  </script><?php
}
add_action( 'admin_footer', 'coolrunner_ajax_resend_label_script' );

function coolrunner_ajax_resend_pdf_script() {
	
		?><script type="text/javascript" >
			  jQuery(document).on('click' , '[name="coolrunner_call_pdf"]', function() {
				var id = jQuery(this).data('order-id');
				
				jQuery.ajax({
					method: "POST",
					url: ajaxurl,
					data: {
						'action': 'coolrunner_resend_label_notification',
						'id': id
					}
				})			
				.done(function( data ) {
				//	alert(data);
					var obj = jQuery.parseJSON( data );
					
					jQuery('[name="coolrunner_notification"]').remove();
					
				  if(obj.status == 'ok'){						
					  jQuery('#coolrunner_ajax_resend_call'+id).attr('href',obj.result.pdf_link);
					  jQuery('#coolrunner_ajax_resend_call'+id).attr('target','_blank');
					  window.location.href = obj.result.pdf_link;
					  
				  }else{
						alert(obj.message);
				  //	jQuery('name="coolrunner_notification"').remove();
					  jQuery("#wpbody-content").prepend( '<div name="coolrunner_notification" class="notice notice-error"><p> Error display pdf shipment </p></div>' );
				  }
				})
				.fail(function( data ) {
					  jQuery("#wpbody-content").prepend( '<div class="notice notice-error"><p> Error, could not display order</p></div>' );
				});
				
			})
	
	  </script><?php
	}
	add_action( 'admin_footer', 'coolrunner_ajax_resend_pdf_script' );


function coolrunner_resend_label_notification() {

	if(!empty($_POST['id'])){

		$order = new WC_Order($_POST['id']);

		$destination = "v2/shipments/";

		$curldata = create_shipment_array($order);

		$curl = new CR_Curl();

		$response = $curl->sendCurl($destination, get_option('coolrunner_settings_username'), get_option('coolrunner_settings_token'), $curldata, $recieve_responsecode = false, $json = true);

		//	$response = $curl->sendCurl($destination, $username, $token, $curldata, $header_enabled = false, $json = true);
		//	print_r($curldata);
		$success=0;
		if ($response->status=='ok'){
			$success=1;	
			//update pdf link
			update_post_meta( $_POST['id'], 'coolrunner_auto_status', get_option('coolrunner_settings_auto_status') );
			update_post_meta( $_POST['id'], 'coolrunner_pdf_link', $response->result->pdf_link );
			update_post_meta(  $_POST['id'], 'coolrunner_package_number', $response->result->package_number );
				
			//Send email
			$get_email = get_post_meta($_POST['id'], 'coolrunner_printed', true );

			if (get_option( 'coolrunner_settings_send_email') == 'yes'){
			//	$tracking_array = coolrunner_get_tracking_data($post_id);
			//	$package_no = $tracking_array->package_number;
				$package_no = get_post_meta(  $_POST['id'], 'coolrunner_package_number', true );			

				$headers = array('Content-Type: text/html; charset=UTF-8','From: My Site Name &lt;support@cm-telecom.dk');
				
				$message="
				<table style='width:100%'>
				<tr><td align='center' style='background-color:#f7f7f7'>
				<table style='width:600px;' p>
				<tr>
					<td style='background-color:#2B97D6; padding: 15px;'>
						<h1 style=\"color:#ffffff;font-family:&quot;Helvetica Neue&quot;,Helvetica,Roboto,Arial,sans-serif;font-size:30px;font-weight:300;line-height:150%;margin:0;text-align:left\">Coolrunner Tracking</h1>
					</td>
				</tr>
				<tr>
					<td style='background-color:#ffffff; padding: 15px;'>
				Hej,<br> 
				Vedrørende din pakke #".$order->ID.", kan du finde den via dette link https://coolrunner.dk/ Klik på fanen spor en pakke og indsæt dette track and trace nummer:". $package_no."
					</td>
				</tr>
				</table>
				</td></tr>
				</table>";

				$url = home_url();
				$url = esc_url( $url );
				$to = $order->billing_email;
				$subject = $url." Coolrunner Tracking";
				$headers = array('Content-Type: text/html; charset=UTF-8');

				 wp_mail( $to, $subject, $message, $headers, $attachments="" );
			}

			update_post_meta( $_POST['id'],'coolrunner_printed',  $success );
		}
		
		
		$array = json_encode($response);
	//	$data = $tracking_array;
	//	echo $data->package_number;
		echo ($array);

		//Returner korrekt værdi udfra respons kode
		//echo $response['http_code'];


	}else{
		echo "ID was not found";
	}

 	exit; // just to be safe
}
add_action( 'wp_ajax_coolrunner_resend_label_notification', 'coolrunner_resend_label_notification' );


function coolrunner_get_tracking_data($order_id) {


	if(!empty($order_id)){

	//	$destination = get_post_meta($order_id,'coolrunner_pdf_link', true );

		$link_pdf = get_post_meta($order_id,'coolrunner_pdf_link', true );
		$unique_id = substr($link_pdf,39,50);
	//	$unique_id = 'MTA3MjQ5LTE5OTA2NTktMjQ4NDQyMy02MDQ3ODE2OTEw';
		$destination = "v2/shipments/".$unique_id."/tracking";

	//	$url = "https://api.coolrunner.dk/";

	//	$response_url = $url.$destination;
	//	$response_url = 'https://api.coolrunner.dk/v2/shipments/MTA3MjQ5LTE5OTA2NTktMjQ4NDQyMy02MDQ3ODE2OTEw/tracking';

		$curldata = array();
		$curl = new CR_Curl();

		$response = $curl->sendCurl($destination, get_option('coolrunner_settings_username'), get_option('coolrunner_settings_token'), $curldata, $recieve_responsecode = false, $json = true);

		return $response;

		//	$response = $curl->sendCurl($destination, $username, $token, $curldata, $header_enabled = false, $json = true);
		//	print_r($curldata);		
	}
	
}


function create_shipment_array($order){

	//$dp = ( isset( $filter['dp'] ) ? intval( $filter['dp'] ) : 2 );
	$order_post = get_post( $order->id );


		//$chosen_methods = WC()->customer->get( 'chosen_shipping_methods' );
		//$chosen_methods = $order->get_items( 'shipping' );
		//$chosen_shipping = $chosen_methods[0];
		$shipping_items  =  $order->get_items('shipping');
		$key = array_keys($shipping_items);
			
		$chosen_shipping = $shipping_items[$key[0]]['method_id'];		
		$matches = explode('_', $chosen_shipping);

		$user_info = get_userdata(1);
		$add_order_note = get_post_meta($order_id, 'add_order_note',true);
		$weight = get_post_meta( $order->id, 'coolrunner_weight', true );
		$height = get_post_meta( $order->id, 'coolrunner_height', true );
		$width = get_post_meta( $order->id, 'coolrunner_width', true );
		$length = get_post_meta( $order->id, 'coolrunner_length', true );

		$droppoint = ($matches[0] == "coolrunner" && end($matches) == "droppoint")?true:false;
		if ($matches[3] == 'droppoint' || $matches[3] == 'delivery'){
			$carrier_service = $matches[3];
		}else if (isset($matches[4])){
			$carrier_service = $matches[3]."_".$matches[4];
		}else{
			$carrier_service = "delivery";
		}

		$droppoint_id=0;
		if ($droppoint){
			$droppoint_id = get_post_meta( $order->id, 'coolrunner_customer_droppoint', true );
			$droppoint_name = get_post_meta( $order->id, 'coolrunner_customer_droppoint_name', true );
			$droppoint_street = get_post_meta( $order->id, 'coolrunner_customer_droppoint_street', true );
			$droppoint_zipcode = get_post_meta( $order->id, 'coolrunner_customer_droppoint_zipcode', true );
			$droppoint_city = get_post_meta( $order->id, 'coolrunner_customer_droppoint_city', true );
			$droppoint_country_code = get_post_meta( $order->id, 'coolrunner_customer_droppoint_country_code', true );			
		}	
	
		$array = array(
			'receiver_name'         => $order->shipping_first_name.' '.$order->shipping_last_name,
			"receiver_attention" 	=> $order->shipping_first_name.' '.$order->shipping_last_name,
			'receiver_street1'      => $order->shipping_address_1,
			'receiver_zipcode'      => $order->shipping_postcode,
			'receiver_city'     	=> $order->shipping_city,
			'receiver_country'  	=> $order->shipping_country,
			'receiver_phone'   		=> $order->billing_phone,
			'receiver_email' 		=> $order->billing_email,			
			"receiver_notify"		=> true,
			"receiver_notify_sms"	=> $order->billing_phone,
			"receiver_notify_email"	=> $order->billing_email,
			"sender_name"			=> $user_info->first_name." ".$user_info->last_name,
			"sender_street1"		=> WC()->countries->get_base_address().' '.WC()->countries->get_base_address_2(),
			"sender_zipcode"		=> WC()->countries->get_base_postcode(),
			"sender_city"			=> WC()->countries->get_base_city(),
			"sender_country"		=> "DK",
			"sender_phone"			=> "",
			"sender_email"			=> $user_info->user_email ,
			"length"				=> $length,
			"width"					=> $width,
			"height" 				=> $height,
			"weight" 				=> $weight,
			"carrier"				=> $matches[1],
			"carrier_product"		=> $matches[2],
			"carrier_service"		=> $carrier_service,
			"reference"				=> "Order no: ".$order->id ,
			"label_format"			=> get_option('coolrunner_settings_label_format'),
			'receiver_attention'    => "",
			'receiver_street2'      => "",
			'sender_attention'      => "",
			'sender_street2'        => "",
			'description'          	=> $add_order_note,
			'comment'             	=> "",
			'droppoint'            	=> $droppoint,
			'droppoint_id'          => $droppoint_id,
			'droppoint_name'        => $droppoint_name,
			'droppoint_street1'     => $droppoint_street,
			'droppoint_zipcode'     => $droppoint_zipcode,
			'droppoint_city'        => $droppoint_city,
			'droppoint_country'     => $droppoint_country_code
		);	

	return $array;
}

// Hook in
add_filter( 'woocommerce_default_address_fields' , 'coolrunner_override_default_address_fields' );

// Our hooked in function - $address_fields is passed via the filter!
function coolrunner_override_default_address_fields( $address_fields ) {
     return $address_fields;
}

add_filter( 'woocommerce_package_rates', 'CR_hide_shipping_rate_if_exceeding', 10, 2 );
function CR_hide_shipping_rate_if_exceeding( $rates, $package ) {

	global $woocommerce;

	$weight = $woocommerce->cart->cart_contents_weight;
	$weight = $weight * 1000;

  	foreach ( $rates as $id => $shipping_object ) {
  		if(strpos($shipping_object->id, 'coolrunner_') == 'coolrunner_'){

			$max_weight = get_option($shipping_object->id . '_max_weight');

			if($weight >= $max_weight){
				unset( $rates[ $shipping_object->id ] );
			}
		}
	}

	return $rates;
}


// Store cart weight in the database
add_action('woocommerce_checkout_update_order_meta', 'woo_add_cart_weight');
function woo_add_cart_weight( $order_id ) {
    global $woocommerce,$wpdb;
    
    $weight = ($woocommerce->cart->cart_contents_weight)*1000;
	update_post_meta( $order_id, 'coolrunner_weight', $weight );

	//update droppoint
	$array = array(
		"coolrunner_customer_droppoint_name",
		"coolrunner_customer_droppoint_street",
		"coolrunner_customer_droppoint_zipcode",
		"coolrunner_customer_droppoint_city",
		"coolrunner_customer_droppoint_country_code",
	);
	$table = $wpdb->postmeta;

	foreach ($array as $value){

		$wpdb->query( $wpdb->prepare( 
			"
			UPDATE $table
			SET  post_id = %d
			WHERE post_id = %d
				AND meta_key = %s
			",
			$order_id, 9999, $value
		) );
	}

}
// Add order new column in administration
add_filter( 'manage_edit-shop_order_columns', 'woo_order_weight_column', 20 );
function woo_order_weight_column( $columns ) {
	$offset = 8;
	$updated_columns = array_slice( $columns, 0, $offset, true) +
	array( 	'total_weight' => esc_html__( 'Weight', 'woocommerce' ),
			'height' => esc_html__( 'Height', 'woocommerce'),
			'length' => esc_html__( 'Length', 'woocommerce') ,
			'width' => esc_html__( 'Width', 'woocommerce'),
			'droppoint_address' => esc_html__( 'Droppoint address', 'woocommerce') ,
			'label_printed' => esc_html__('Label printed','woocommerce'),
			 ) +
	array_slice($columns, $offset, NULL, true);
	return $updated_columns;
}
// Populate weight column
add_action( 'manage_shop_order_posts_custom_column', 'woo_custom_order_weight_column', 2 );
function woo_custom_order_weight_column( $column ) {
	global $post;
 
	if ( $column == 'total_weight' ) {
		$weight = get_post_meta( $post->ID, 'coolrunner_weight', true );
		if ( $weight > 0 )
			print ($weight)/1000 . ' ' . esc_attr( get_option('woocommerce_weight_unit' ) );
		else print 'N/A';
	}

	if ( $column == 'height' ) {
		$height = get_post_meta( $post->ID, 'coolrunner_height', true );
		if ( $height > 0 )
			print $height;
		else print 'N/A';
	}

	if ( $column == 'length' ) {
		$length = get_post_meta( $post->ID, 'coolrunner_length', true );
		if ( $length > 0 )
			print $length;
		else print 'N/A';
	}

	if ( $column == 'width' ) {
		$width = get_post_meta( $post->ID, 'coolrunner_width', true );
		if ( $width > 0 )
			print $width;
		else print 'N/A';
	}

	if ( $column == 'droppoint_address' ) {
		$droppoint_id = get_post_meta( $post->ID, 'coolrunner_customer_droppoint', true );
		if ( $droppoint_id > 0 )		
			print get_post_meta($post->ID, 'coolrunner_customer_droppoint_name',true ).' '.get_post_meta($post->ID, 'coolrunner_customer_droppoint_street',true).' '.get_post_meta($post->ID, 'coolrunner_customer_droppoint_city',true).' '.get_post_meta($post->ID, 'coolrunner_customer_droppoint_zipcode',true).' '.get_post_meta($post->ID, 'coolrunner_customer_droppoint_country_code',true);
		else print '-';
	}

	if ( $column == 'label_printed' ) {
		$label_printed = get_post_meta( $post->ID, 'coolrunner_auto_status', true );

			print $label_printed;

	}
}

add_action( 'admin_menu' , 'remove_post_custom_fields' );
function remove_post_custom_fields() {
	remove_meta_box( 'postcustom' , 'shop_order' , 'normal' ); 
}

// Adding Meta container admin shop_order pages
add_action( 'add_meta_boxes', 'mv_add_meta_boxes' );
if ( ! function_exists( 'mv_add_meta_boxes' ) )
{
    function mv_add_meta_boxes()
    {
        add_meta_box( 'mv_other_fields', __('Package size','woocommerce'), 'mv_add_other_fields_for_packaging', 'shop_order', 'side', 'core' );
    }
}


// Adding Meta field in the meta container admin shop_order pages
if ( ! function_exists( 'mv_add_other_fields_for_packaging' ) )
{
    function mv_add_other_fields_for_packaging()
    {
		global $post, $wpdb; 
		
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
		echo "<select name=\"coolrunner_package_name\" id=\"coolrunner_package_name\" style=\"width:250px;\">";
			echo '<option value="">Select package</option>';
		foreach ( $packages as $key => $package ) 
		{
			$package_name = unserialize($package->option_value);
			echo '<option value="'.$package->option_id.'" data-length="'.$package_name['coolrunner_length'].'" data-width="'.$package_name['coolrunner_width'].'" data-height="'.$package_name['coolrunner_height'].'" data-weight="'.$package_name['coolrunner_weight'].'" >'.$package_name['coolrunner_package_name'].'</option>';		
		}
		echo "</select>";


		echo '<input type="hidden" name="mv_other_meta_field_nonce" value="' . wp_create_nonce() . '">';

		$array = array ('coolrunner_length','coolrunner_width','coolrunner_height');
				
		foreach ($array as $key){
			$meta_field_data = get_post_meta( $post->ID, $key, true ) ? get_post_meta( $post->ID, $key, true ) : '';
			$label = ucfirst(substr($key,11));
        	echo '<p>'.esc_html__( $label, 'woocommerce').' (cm) : 
			<input type="text" style="width:250px;" id="'.$key.'" name="'.$key.'" placeholder="' . $meta_field_data . '" value="' . $meta_field_data . '"></p>';
		}
		$weight = get_post_meta ($post->ID,'coolrunner_weight',true);
		echo '<p>'.esc_html__( 'Weight', 'woocommerce' ).' (kg) : ';
		echo '<select name="coolrunner_weight" id="coolrunner_weight" style="width:250px;">';
				echo '<option value="500" ';   if ($weight==500) echo "selected"; echo '>Under 500 g</option>';		
				echo '<option value="1000" ';  if ($weight==1000) echo "selected"; echo '>500 g - 1 kg</option>';
				echo '<option value="2000" ';  if ($weight==2000) echo "selected"; echo  '>1 - 2 kg</option>';
				echo '<option value="3000" ';  if ($weight==3000) echo "selected"; echo '>2 - 3 kg</option>';
				echo '<option value="5000" ';  if ($weight==5000) echo "selected"; echo '>3 - 5 kg</option>  ';    
				echo '<option value="10000" '; if ($weight==10000) echo "selected"; echo '>5 - 10 kg</option>';
				echo '<option value="15000" '; if ($weight==15000) echo "selected"; echo '>10 - 15 kg</option>';
				echo '<option value="20000" '; if ($weight==20000) echo "selected";  echo  '>15 - 20 kg</option>';
				echo '<option value="30000" '; if ($weight==30000) echo "selected"; echo '>20 - 30 kg</option>';
		echo '		</select></p>';

		submit_button( $text = 'Update', $type = 'primary', $name = 'submit', $wrap = true, $other_attributes = NULL );	

    }
}

// Save the data of the Meta field
add_action( 'save_post', 'mv_save_wc_order_other_fields', 10, 1 );
if ( ! function_exists( 'mv_save_wc_order_other_fields' ) )
{

    function mv_save_wc_order_other_fields( $post_id ) {

        // We need to verify this with the proper authorization (security stuff).

        // Check if our nonce is set.
        if ( ! isset( $_POST[ 'mv_other_meta_field_nonce' ] ) ) {
            return $post_id;
        }
        $nonce = $_REQUEST[ 'mv_other_meta_field_nonce' ];

        //Verify that the nonce is valid.
        if ( ! wp_verify_nonce( $nonce ) ) {
            return $post_id;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }

        // Check the user's permissions.
        if ( 'page' == $_POST[ 'post_type' ] ) {

            if ( ! current_user_can( 'edit_page', $post_id ) ) {
                return $post_id;
            }
        } else {

            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return $post_id;
            }
        }
        // --- Its safe for us to save the data ! --- //

        // Sanitize user input  and update the meta field in the database.
		update_post_meta( $post_id, '_my_field_slug', $_POST[ 'my_field_name' ] );
		
		$array = array ('coolrunner_length','coolrunner_width','coolrunner_height');
		
		foreach($array as $key){
		   update_post_meta( $post_id, $key, $_POST[ $key ] );
		}
		update_post_meta( $post_id, 'coolrunner_weight', $_POST['coolrunner_weight'] );
		
    }
}

// Adding Meta container admin shop_order history
add_action( 'add_meta_boxes', 'history_add_meta_boxes' );
if ( ! function_exists( 'history_add_meta_boxes' ) )
{
    function history_add_meta_boxes()
    {
        add_meta_box( 'mv_history_fields', __('History','woocommerce'), 'mv_show_history', 'shop_order', 'side', 'core' );
    }
}



// Adding Meta field in the meta container admin shop_order pages
if ( ! function_exists( 'mv_show_history' ) )
{
    function mv_show_history()
    {
		global $post; 
		$order_id = $post->ID; 
		$tracking_array = coolrunner_get_tracking_data($order_id);
		echo "<p><strong>Status : </strong>".$tracking_array->tracking->status->header."</p>";
		$history_array = $tracking_array->tracking->history;
		echo "<ul>";
		foreach ($history_array as $value){
			echo "<li><strong>Time :</strong>".$value->time."";
			echo "<strong>message :</strong>".$value->message."</li>";
		}
		echo "</ul>";
    }
}



//checkout validation

add_action('woocommerce_checkout_process', 'is_droppoint');
if ( ! function_exists( 'is_droppoint' ) )
{
	function is_droppoint() 
	{ 
		$chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
		$chosen_shipping = $chosen_methods[0];
   
	   	$matches = array();
		$matches = explode('_', $chosen_shipping);

   
		if($matches[0] == "coolrunner" && end($matches) == "droppoint"){

			$coolrunner_zip_code_search = $_POST['coolrunner_zip_code_search'];
			$droppoint_selection = $_POST['droppoint_selection'];

			// your function's body above, and if error, call this wc_add_notice
			if (empty($coolrunner_zip_code_search) || empty($droppoint_selection)){
				wc_add_notice( __( 'Please input your package shop.' ), 'error' );
			}
		}
		
	}
}


// bulk action
add_action('admin_footer-edit.php', 'custom_bulk_admin_footer');

function custom_bulk_admin_footer() {

  global $post_type;

  if($post_type == 'shop_order') {
    ?>
    <script type="text/javascript">
      jQuery(document).ready(function() {
        jQuery('<option>').val('pdf').text('<?php _e('Print Label (Coolrunner)')?>').appendTo("select[name='action']");
      });
    </script>
    <?php
  }
}


add_action('load-edit.php', 'custom_bulk_action');
function custom_bulk_action() {

     // Make sure that we on "Woocomerce orders list" page
	if ( !isset($_GET['post_type']) || $_GET['post_type'] != 'shop_order' ) {
        return;
    }
    if ( isset($_GET['action']) &&  $_GET['post_type'] == 'shop_order' ) {
		// Check Nonce


        if ( !check_admin_referer("bulk-posts") ) {
            return;
		}

		if ($_GET['action']=='pdf'){
        // Remove 'set-' from action
    //    $new_status =  substr( $_GET['action'], 4 );
			$posts = $_GET['post'];

			foreach ($posts as $post_id) {
				//	$order = new WC_Order( (int)$post_id );
				coolrunner_resend_label_bulk_action($post_id);
			}
		
	
	//		$sendback = 'http://localhost/cmplugin/wp-admin/edit.php?post_type=shop_order';

			$sendback = add_query_arg( 'action', count( $posts ), $sendback );
			return $sendback;

			wp_redirect($sendback);		
		}

	}
  
}

//add_action('admin_footer-edit.php', 'coolrunner_resend_label_bulk_action');
function coolrunner_resend_label_bulk_action($post_id) {
	
		if(!empty($post_id)){

			$order = new WC_Order($post_id);			
			$destination = "v2/shipments/";	
			$curldata = create_shipment_array($order);	
		
			$curl = new CR_Curl();	
			$response = $curl->sendCurl($destination, get_option('coolrunner_settings_username'), get_option('coolrunner_settings_token'), $curldata, $recieve_responsecode = false, $json = true);
	
			//	$response = $curl->sendCurl($destination, $username, $token, $curldata, $header_enabled = false, $json = true);
			//	print_r($curldata);

			$success=0;
			if ($response->status=='ok'){
				$success=1;	
				//update pdf link
				update_post_meta( $post_id, 'coolrunner_auto_status', get_option('coolrunner_settings_auto_status') );
				update_post_meta( $post_id, 'coolrunner_pdf_link', $response->result->pdf_link );
				update_post_meta( $post_id, 'coolrunner_package_number', $response->result->package_number );
				
				//Send email
				$get_email = get_post_meta($_POST['id'], 'coolrunner_printed', true );
				if (get_option( 'coolrunner_settings_send_email') == 'yes' &&  $get_email == 0){
				//	$tracking_array = coolrunner_get_tracking_data($post_id);
				//	$package_no = $tracking_array->package_number;
					$package_no = get_post_meta( $post_id, 'coolrunner_package_number', true );
					$message="Hej, 
					Vedrørende din pakke #".$order->id.", kan du finde den via dette link https://coolrunner.dk/ Klik på fanen spor en pakke og indsæt dette track and trace nummer: $package_no
					 ";
					
					$to = $order->billing_email;
					$subject = get_the_title()." Coolrunner Tracking";
					 wp_mail( $to, $subject, $message, $headers="", $attachments="" );
				}
	
				
			}
			update_post_meta( $post_id,'coolrunner_printed',  $success );
				
			
	
			//Returner korrekt værdi udfra respons kode
			//echo $response['http_code'];			
	
		}else{
			echo "ID was not found";
		}
	
}


// bulk action
add_action('admin_footer', 'custom_js_admin_footer');

function custom_js_admin_footer() {
    ?>
    <script type="text/javascript">
      jQuery(document).ready(function() {
	//	  jQuery('#coolrunner_package_name').on("change",function(){
		jQuery(document).on('change' , '#coolrunner_package_name', function() {
			var length = jQuery("#coolrunner_package_name option:selected").data('length');
			var width = jQuery("#coolrunner_package_name option:selected").data('width');
			var height = jQuery("#coolrunner_package_name option:selected").data('height');
			var weight = jQuery("#coolrunner_package_name option:selected").data('weight');
				

			jQuery('#coolrunner_length').val(length);
			jQuery('#coolrunner_width').val(width);
			jQuery('#coolrunner_height').val(height);
			jQuery('#coolrunner_weight').val(weight);
			jQuery('#coolrunner_weight').trigger("chosen:updated");
		  });
      });
    </script>
    <?php
}

