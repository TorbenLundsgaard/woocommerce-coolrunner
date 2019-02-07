<?php
/**
 * @package   woocommerce-coolrunner
 * @author    Morten Harders
 * @copyright 2019
 */

require_once 'nav.php';

if ( ! empty( $_POST ) ) {
	foreach ( $_POST as $key => $value ) {
		$value = str_replace( [ '\\"', "\\'" ], [ '"', "'" ], $value );
		if ( $key === 'coolrunner_tracking_email' ) {
			CoolRunner::updateEmail( $value );
		} elseif ( strpos( $key, 'coolrunner' ) !== false ) {
			update_option( $key, $value );
		}
	}
	crship_register_customer_shipping( true );
}

?>
<h2>Options</h2>
<form method="post" action="" enctype="multipart/form-data">
    <div class="row">
        <div class="column">
            <table class="form-table">
                <tbody>
                <tr>
                    <th>
                        <label for="coolrunner_store_name">
							<?php echo __( 'Store Name', 'coolrunner-shipping-plugin' ) ?>
                        </label>
                    </th>
                    <td>
                        <input required id="coolrunner_store_name" type="text" name="coolrunner_store_name" value="<?php echo get_option( 'coolrunner_store_name' ) ?>" placeholder="MyAwesomeStuff">
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="coolrunner_integration_username">
							<?php echo __( 'Integration Username', 'coolrunner-shipping-plugin' ) ?>
                        </label>
                    </th>
                    <td>
                        <input required id="coolrunner_integration_username" type="text" name="coolrunner_integration_username" value="<?php echo get_option( 'coolrunner_integration_username' ) ?>" placeholder="my@email.com">
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="coolrunner_integration_token">
							<?php echo __( 'Integration Token', 'coolrunner-shipping-plugin' ) ?>
                        </label>
                    </th>
                    <td>
                        <input required id="coolrunner_integration_token" type="text" name="coolrunner_integration_token" value="<?php echo get_option( 'coolrunner_integration_token' ) ?>" placeholder="lasdkhgfo745lkjsdt7o">
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="coolrunner_label_format">
							<?php echo __( 'Label Format', 'coolrunner-shipping-plugin' ) ?>
                        </label>
                    </th>
                    <td>
                        <select id="coolrunner_label_format" name="coolrunner_label_format">
							<?php foreach ( [ 'A4', 'LabelPrint' ] as $label_format ) : ?>
                                <option value="<?php echo $label_format ?>" <?php echo get_option( 'coolrunner_label_format', 'A4' ) == $label_format ? 'selected' : '' ?>><?php echo $label_format ?></option>
							<?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="coolrunner_product_weight">
			                <?php echo __( 'Label Format', 'coolrunner-shipping-plugin' ) ?>
                        </label>
                    </th>
                    <td>
                        <select id="coolrunner_product_weight" name="coolrunner_product_weight">
			                <?php foreach ( [ 1 => 'Gram', 1000 => 'Kilogram', 1000000 => 'Ton' ] as $multiplier => $weight_unit ) : ?>
                                <option value="<?php echo $multiplier ?>" <?php echo get_option( 'coolrunner_product_weight', 1000 ) == $multiplier ? 'selected' : '' ?>><?php echo $weight_unit ?></option>
			                <?php endforeach; ?>
                        </select>
                        <p class="description"><?php echo __( 'Weight unit used by WooCommerce (used for automatic conversion of weight for shipments)', 'coolrunner-shipping-plugin' ) ?></p>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="coolrunner_servicepoints">
							<?php echo __( 'Servicepoints', 'coolrunner-shipping-plugin' ) ?>
                        </label>
                    </th>
                    <td>
                        <select id="coolrunner_servicepoints" name="coolrunner_servicepoints">
							<?php for ( $i = 5; $i <= 20; $i ++ ) : ?>
                                <option value="<?php echo $i ?>" <?php echo get_option( 'coolrunner_servicepoints', 5 ) == $i ? 'selected' : '' ?>><?php echo $i ?></option>
							<?php endfor; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="coolrunner_automatic_tracking">
							<?php echo __( 'Enable Tracking Email', 'coolrunner-shipping-plugin' ) ?>
                        </label>
                    </th>
                    <td>
                        <select name="coolrunner_automatic_tracking" id="coolrunner_automatic_tracking">
                            <option value="1" <?php echo get_option( 'coolrunner_automatic_tracking', 0 ) == 1 ? 'selected' : '' ?>><?php echo __( 'Yes' ) ?></option>
                            <option value="0" <?php echo get_option( 'coolrunner_automatic_tracking', 0 ) == 0 ? 'selected' : '' ?>><?php echo __( 'No' ) ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="coolrunner_debug_mode">
							<?php echo __( 'Debug Mode', 'coolrunner-shipping-plugin' ) ?>
                        </label>
                    </th>
                    <td>
                        <select name="coolrunner_debug_mode" id="coolrunner_debug_mode">
                            <option value="1" <?php echo get_option( 'coolrunner_debug_mode', 0 ) == 1 ? 'selected' : '' ?>><?php echo __( 'Yes' ) ?></option>
                            <option value="0" <?php echo get_option( 'coolrunner_debug_mode', 0 ) == 0 ? 'selected' : '' ?>><?php echo __( 'No' ) ?></option>
                        </select>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="column">
            <h3><?php echo __( 'Tracking', 'coolrunner-shipping-plugin' ) ?></h3>
            <table class="form-table">
                <tbody>
                <tr>
                    <th>
                        <label for="coolrunner_tracking_email">
							<?php echo __( 'Tracking email', 'coolrunner-shipping-plugin' ) ?>
                        </label>
                    </th>
                    <td>
                        <p style="margin-top: 0px; display: block; height: 21px; padding-top: 0px; padding-bottom: 0px; margin-bottom: 0px;">
                            Placeholders: <b>{first_name}</b>, <b>{last_name}</b>, <b>{email}</b>, <b>{order_no}</b>, <b>{package_number}</b>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="padding: 0;">
                        <div class="row">
                            <textarea required id="coolrunner_tracking_email" name="coolrunner_tracking_email" placeholder=""><?php echo CoolRunner::getEmail() ?></textarea>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <button type="submit" class="button button-primary"><?php echo __( 'Save' ) ?></button>
</form>