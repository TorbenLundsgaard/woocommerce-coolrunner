<?php
/**
 * @package   woocommerce-coolrunner
 * @author    Morten Harders
 * @copyright 2019
 */

require_once '../../../wp-load.php';

if ( ! current_user_can( 'manage_woocommerce' ) ) {
    http_response_code( 404 );
    die();
}

if ( ! isset( $_GET['order_id'] ) ) {
    die( 'missing parameter order_id' );
}



$order_id = $_GET['order_id'];
$order    = wc_get_order( $order_id );
if ( ! $order ) {
    http_response_code( 404 );
    die();
}
$orderno        = $order->get_order_number();
if(isset($_GET['second'])) {
    $package_number = get_post_meta( $order_id, '_coolrunner_package_number_second', true );
    $pdf            = get_post_meta( $order_id, '_coolrunner_pdf_second', true );
} else {
    $package_number = get_post_meta( $order_id, '_coolrunner_package_number', true );
    $pdf            = get_post_meta( $order_id, '_coolrunner_pdf', true );
}
$download       = isset( $_GET['download'] );


if ( $download ) {
    header( "Content-Disposition: attachment; filename=$orderno-$package_number.pdf" );
}
header( 'Content-Type: application/pdf' );
echo base64_decode( $pdf );
