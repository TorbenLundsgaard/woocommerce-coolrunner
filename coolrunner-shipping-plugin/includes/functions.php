<?php

define( 'COOLRUNNER_NAME', get_plugin_data( __DIR__ . '/../woocommerce_coolrunner.php' )['Name'] );
define( 'COOLRUNNER_VERSION', get_plugin_data( __DIR__ . '/../woocommerce_coolrunner.php' )['Version'] );

add_action( 'admin_menu', function () {
    add_menu_page( 'CoolRunner', 'CoolRunner', 'manage_options', 'coolrunner-options', function () {
        $section = isset( $_GET['section'] ) ? $_GET['section'] : 'settings';
        if ( in_array( $section, [ 'settings', 'box-sizes' ] ) ) {
            echo '<div class="wrap coolrunner">';
            require_once plugin_dir_path( __FILE__ ) . "../pages/$section.php";
            echo '</div>';
        }
    }, plugins_url( 'assets/images/coolrunner-logo.png', COOLRUNNER_PLUGIN_FILE ) );
} );

add_action( 'wp_ajax_coolrunner_create_shipment', function () {
    if ( isset( $_POST['order_id'] ) && $order_id = $_POST['order_id'] ) {
        $size = [
            'height' => $_POST['height'],
            'width'  => $_POST['width'],
            'length' => $_POST['length'],
            'weight' => $_POST['weight'],
        ];

        update_post_meta( $order_id, '_coolrunner_package_size', $size );

        $response = coolrunner_create_shipment( $order_id );

        $response['new_content'] = crship_get_metabox_content( $order_id );

        header( 'Content-Type: application/json' );
        echo json_encode( $response );
    }

    wp_die();
} );

add_action( 'wp_ajax_coolrunner_create_shipment_second', function () {
    error_log('wp_ajax_coolrunner_create_shipment_second runned - ' . $_POST['order_id']);
    if ( isset( $_POST['order_id'] ) && $order_id = $_POST['order_id'] ) {
        $size = [
            'height' => $_POST['height_second'],
            'width'  => $_POST['width_second'],
            'length' => $_POST['length_second'],
            'weight' => $_POST['weight_second'],
        ];

        update_post_meta( $order_id, '_coolrunner_package_size_second', $size );

        $response = coolrunner_create_shipment_second( $order_id );

        $response['new_content'] = crship_get_metabox_content( $order_id );

        header( 'Content-Type: application/json' );
        echo json_encode( $response );
    }

    wp_die();
} );

add_action( 'wp_ajax_coolrunner_send_tracking', function () {
    if ( isset( $_POST['order_id'] ) && $order_id = $_POST['order_id'] ) {
        $sent = coolrunner_send_tracking_email( $order_id );

        if ( ! $sent ) {
            http_response_code( 400 );
            echo false;
            die();
        }

        $response                = array();
        $response['new_content'] = crship_get_metabox_content( $order_id );

        header( 'Content-Type: application/json' );
        echo json_encode( $response );
    }

    wp_die();
} );

add_action( 'wp_ajax_coolrunner_delete_shipment', function () {
    if ( isset( $_POST['order_id'] ) && $order_id = $_POST['order_id'] ) {
        coolrunner_delete_shipment( $order_id );

        $response                = array();
        $response['new_content'] = crship_get_metabox_content( $order_id );

        header( 'Content-Type: application/json' );
        echo json_encode( $response );
    }

    wp_die();
} );

add_action( 'wp_ajax_coolrunner_delete_shipment_second', function () {
    if ( isset( $_POST['order_id'] ) && $order_id = $_POST['order_id'] ) {
        coolrunner_delete_shipment_second( $order_id );

        $response                = array();
        $response['new_content'] = crship_get_metabox_content( $order_id );

        header( 'Content-Type: application/json' );
        echo json_encode( $response );
    }

    wp_die();
} );

add_action( 'add_meta_boxes', function () {
    global $post, $post_type;
    if ( $post_type !== 'shop_order' ) {
        return;
    }

    $order = wc_get_order( $post->ID );

    $is_coolrunner = false;
    foreach ( $order->get_shipping_methods() as $method ) {
        if ( $method->get_method_id() === 'coolrunner' ) {
            $is_coolrunner = true;
        }
    }

    if ( ! $is_coolrunner ) {
        return;
    }
    add_meta_box( 'coolrunner-meta-box', __( 'CoolRunner', 'coolrunner-shipping-plugin' ), function () {
        echo crship_get_metabox_content();
    }, 'shop_order', 'side', 'core' );

    if ( ! get_post_meta( $post->ID, '_coolrunner_package_number', true ) ) {
        return;
    }
    add_meta_box( 'crship_history_fields', __( 'Tracking History', 'coolrunner-shipping-plugin' ), function () {
        global $post;
        $order       = new WC_Order( $post->ID );
        $config_name = '';
        foreach ( $order->get_shipping_methods() as $shippingMethod ) {
            if ( $shippingMethod->get_method_id() === 'coolrunner' ) {
                $config_name = implode( '_', array(
                    'woocommerce',
                    'coolrunner',
                    $shippingMethod->get_instance_id(),
                    'settings'
                ) );
            }
        }
        if ( $config_name ) {
            $order_id       = $post->ID;
            $tracking_array = coolrunner_get_tracking_data( $order_id );
            if ( $tracking_array &&
                isset( $tracking_array->tracking->history ) &&
                count( $tracking_array->tracking->history ) > 0 ) {
                echo "<p><strong>Status : </strong>" . $tracking_array->tracking->status->header . "</p>";
                $history_array = $tracking_array->tracking->history;
                echo "<ul>";
                foreach ( $history_array as $value ) {
                    echo '<li>';
                    echo "<div><strong>Time : </strong>$value->time</div>";
                    echo "<div><strong>Message : </strong>$value->message</div>";
                    echo '</li>';
                }
                echo "</ul>";
            } else {
                ?>
                <p><?php echo __( 'No tracking data available for order no.', 'coolrunner-shipping-plugin' ) ?><?php echo $post->ID ?></p>
                <?php
            }
        }
    }, 'shop_order', 'side', 'core' );
}, 2 );

function crship_get_metabox_content( $id = null ) {
    global $post;
    $post_id      = $id ? $id : $post->ID;
    $order        = wc_get_order( $id ? $id : $post->ID );
    $has_shipping = get_post_meta( $id ? $id : $post->ID, '_coolrunner_package_number', true );
    $has_shipping_second = get_post_meta( $id ? $id : $post->ID, '_coolrunner_package_number_second', true );

    ob_start();

    $weight = 0;
    foreach ( $order->get_items() as $item ) {
        /** @var WC_Order_Item_Product */
        $prod = wc_get_product( $item->get_data()['product_id'] );
        if ( ! $prod->is_virtual() && $prod->has_weight() ) {
            $weight += ( $prod->get_weight() * $item->get_quantity() );
        }
    }

    $weight *= get_option( 'coolrunner_product_weight', 1000 );

    $primary = [ 'name' => '', 'height' => '', 'width' => '', 'length' => '', 'weight' => '' ];
    ?>
    <div class="coolrunner">
        <input type="hidden" name="order_id" value="<?php echo $post_id ?>">
        <?php if ( $servicepoint = get_post_meta( $post_id, '_coolrunner_droppoint', true ) ) : ?>
            <label>
                <?php echo __( 'Servicepoint', 'coolrunner-shipping-plugin' ) ?>:
            </label>
            <address><?php echo sprintf(
                    "%s<br>%s<br>%s %s",
                    $servicepoint['name'],
                    $servicepoint['address']['street'],
                    $servicepoint['address']['postal_code'],
                    $servicepoint['address']['city'] ) ?></address>
        <?php endif; ?>
        <?php if ( $has_shipping ) : ?>
            <?php $size = $order->get_meta( '_coolrunner_package_size' ) ?>
            <label>
                <?php echo __( 'Shipping Method', 'coolrunner-shipping-plugin' ) ?>:
            </label>
        <input type="text" disabled value="<?php echo $order->get_shipping_method() ?>">
            <label>
                <?php echo __( 'Size', 'coolrunner-shipping-plugin' ) ?>:
            </label>
        <input type="text" disabled value="<?php printf( '%s x %s x %s | %skg', $size['height'], $size['width'], $size['length'], number_format( $size['weight'] / 1000, 2 ) ) ?>">
            <label>
                <?php echo __( 'Package Number', 'coolrunner-shipping-plugin' ) ?>:
            </label>
        <input type="text" disabled value="<?php echo $order->get_meta( '_coolrunner_package_number' ) ?>">
            <div class="row">
                <div class="column">
                    <label>
                        <?php echo __( 'Cost incl. tax', 'coolrunner-shipping-plugin' ) ?>:
                    </label>
                    <input type="text" disabled value="DKK<?php echo number_format( floatval( $order->get_meta( '_coolrunner_price_incl_tax' ) ), 2 ) ?>">
                </div>
                <div class="column">
                    <label>
                        <?php echo __( 'Cost excl. tax', 'coolrunner-shipping-plugin' ) ?>:
                    </label>
                    <input type="text" disabled value="DKK<?php echo number_format( floatval( $order->get_meta( '_coolrunner_price_excl_tax' ) ), 2 ) ?>">
                </div>
            </div>
        <?php if ( get_option( 'coolrunner_automatic_tracking' ) ) : ?>
            <label>
                <?php echo __( 'Tracking Sent', 'coolrunner-shipping-plugin' ) ?>:
            </label>
        <input type="text" disabled value="<?php echo $order->get_meta( '_coolrunner_email_sent' ) ? __( 'Yes' ) : __( 'No' ) ?>">
            <button id="coolrunner_send_tracking_email" type="button" class="button button-primary"><?php echo __( 'Send Tracking Email', 'coolrunner-shipping-plugin' ) ?></button>
        <?php endif; ?>
            <button id="coolrunner_delete_shipment" type="button" class="button button-danger"><?php echo __( 'Delete Shipment', 'coolrunner-shipping-plugin' ) ?></button>
            <button data-href="<?php echo COOLRUNNER_PLUGIN_URL . '/pdf.php?download=1&order_id=' . $post_id ?>"
                    id="coolrunner_download_label" type="button" class="button"><?php echo __( 'Download Label', 'coolrunner-shipping-plugin' ) ?></button>
            <button data-href="<?php echo COOLRUNNER_PLUGIN_URL . '/pdf.php?order_id=' . $post_id ?>"
                    id="coolrunner_show_label" type="button" class="button"><?php echo __( 'Show Label', 'coolrunner-shipping-plugin' ) ?></button>
            <button id="coolrunner_print_label" type="button" class="button"><?php echo __( 'Print Label', 'coolrunner-shipping-plugin' ) ?></button>
            <iframe style="display: none;" src="<?php echo COOLRUNNER_PLUGIN_URL . '/pdf.php?order_id=' . $post_id ?>"></iframe>
            <script>
                jQuery(function ($) {
                    let crmeta = $('#coolrunner-meta-box'),
                        trackingBtn = crmeta.find('#coolrunner_send_tracking_email'),
                        deleteBtn = crmeta.find('#coolrunner_delete_shipment'),
                        labelBtns = crmeta.find('#coolrunner_download_label, #coolrunner_show_label'),
                        printBtn = crmeta.find('#coolrunner_print_label');


                    trackingBtn.on('click', function () {

                        $('#coolrunner-meta-overlay').fadeIn();
                        $.ajax({
                            method: 'post',
                            url: ajaxurl,
                            data: {
                                action: 'coolrunner_send_tracking',
                                order_id: '<?php echo $post_id ?>'
                            },
                            success: function (data) {
                                alert('<?php echo __( 'Tracking email sent', 'coolrunner-shipping-plugin' ) ?>');
                                crmeta.find('.inside').html(data.new_content);
                                $('#coolrunner-meta-overlay').fadeOut();
                            },
                            error: function (data) {
                                console.log(data);
                                alert('<?php echo __( 'Failed to send tracking email', 'coolrunner-shipping-plugin' ) ?>');
                                $('#coolrunner-meta-overlay').fadeOut();
                            }
                        });
                    });

                    deleteBtn.on('click', function () {
                        if (confirm('<?php echo __( 'Do you wish to remove the shipment from this order?\nCannot be undone!\nDoes not cancel the shipment!', 'coolrunner-shipping-plugin' ) ?>')) {
                            $.ajax({
                                method: 'post',
                                url: ajaxurl,
                                data: {
                                    action: 'coolrunner_delete_shipment',
                                    order_id: '<?php echo $post_id ?>'
                                },
                                success: function (data) {
                                    alert('<?php echo __( 'Shipment removed', 'coolrunner-shipping-plugin' ) ?>');
                                    crmeta.find('.inside').html(data.new_content);
                                },
                                error: function (data) {
                                    console.log(data);
                                    alert('<?php echo __( 'Failed to remove shipment', 'coolrunner-shipping-plugin' ) ?>');
                                }
                            });
                        }
                    });

                    labelBtns.on('click', function () {
                        window.open($(this).attr('data-href'));
                    });

                    printBtn.on('click', function () {
                        crmeta.find('iframe').get(0).contentWindow.print()
                    })
                });
            </script>
            <!-- ANOTHER SHIPMENT -->

        <?php if(!$has_shipping_second): ?>
            <label for="coolrunner_height_second">
                <?php echo __( 'Height (cm)', 'coolrunner-shipping-plugin' ) ?>:
            </label>
        <input type="number" name="coolrunner_height_second" min="1" id="coolrunner_height_second" value="<?php echo $primary['height'] ?>">
            <label for="coolrunner_width_second">
                <?php echo __( 'Width (cm)', 'coolrunner-shipping-plugin' ) ?>:
            </label>
        <input type="number" name="coolrunner_width_second" min="1" id="coolrunner_width_second" value="<?php echo $primary['width'] ?>">
            <label for="coolrunner_length_second">
                <?php echo __( 'Length (cm)', 'coolrunner-shipping-plugin' ) ?>:
            </label>
        <input type="number" name="coolrunner_length_second" min="1" id="coolrunner_length_second" value="<?php echo $primary['length'] ?>">
            <label for="coolrunner_weight_second">
                <?php echo __( 'Weight (g)', 'coolrunner-shipping-plugin' ) ?>:
            </label>
        <input type="number" name="coolrunner_weight_second" min="0" step="0.01" id="coolrunner_weight_second" value="<?php echo $weight * 1000 ?>">
            <button id="coolrunner_create_second" class="button button-primary" type="button" style="width: 100%;">
                <?php echo __( 'Create Shipment', 'coolrunner-shipping-plugin' ) ?>
            </button>
            <script>
                jQuery(function ($) {
                    let crmeta = $('#coolrunner-meta-box'),
                        inputsSecond = crmeta.find('select[name], input[name]'),
                        createBtnSecond = crmeta.find('#coolrunner_create_second'),
                        sizeSelectSecond = crmeta.find('select');

                    sizeSelectSecond.on('change input', function () {
                        let option = $(this).find(':selected'),
                            props = ['height_second', 'length_second', 'width_second'];

                        props.forEach(function (e) {
                            // console.log(e);
                            crmeta.find('[name=coolrunner_' + e + ']').val(option.attr('data-' + e));
                        })
                    });

                    createBtnSecond.on('click', function () {
                        if (confirm('Vil du oprette forsendelse på denne ordre?')) {
                            let data = {
                                action: 'coolrunner_create_shipment_second',
                                order_id: '<?php echo $post_id ?>',
                                height_second: $("#coolrunner_height_second").val(),
                                width_second: $("#coolrunner_width_second").val(),
                                length_second: $("#coolrunner_length_second").val(),
                                weight_second: $("#coolrunner_weight_second").val()
                            };

                            $('#coolrunner-meta-overlay').fadeIn();

                            $.ajax({
                                method: 'post',
                                url: ajaxurl,
                                data: data,
                                success: function (data) {
                                    if (data.created && data.exists) {
                                        alert('<?php echo __( 'Shipment Created', 'coolrunner-shipping-plugin' ) ?>');
                                    } else if (!data.created && data.exists) {
                                        alert('<?php echo __( 'Shipment Exists', 'coolrunner-shipping-plugin' ) ?>');
                                    } else {
                                        alert('errors:' + data.errors);
                                    }
                                    crmeta.find('.inside').html(data.new_content);

                                    $('#coolrunner-meta-overlay').fadeOut();
                                },
                                error: function (data) {
                                    console.log(data);
                                    alert('failed' + data);
                                    $('#coolrunner-meta-overlay').fadeOut();
                                }
                            })
                        }
                    });
                })
            </script>
        <?php else: ?>
            <b><?php echo __('Package number 2:', 'coolrunner-shipping-plugin'); ?></b><br>
        <?php $sizesecond = $order->get_meta( '_coolrunner_package_size_second' ) ?>
            <label>
                <?php echo __( 'Shipping Method', 'coolrunner-shipping-plugin' ) ?>:
            </label>
        <input type="text" disabled value="<?php echo $order->get_shipping_method() ?>">
            <label>
                <?php echo __( 'Size', 'coolrunner-shipping-plugin' ) ?>:
            </label>
        <input type="text" disabled value="<?php printf( '%s x %s x %s | %skg', $sizesecond['height'], $sizesecond['width'], $sizesecond['length'], number_format( $sizesecond['weight'] / 1000, 2 ) ) ?>">
            <label>
                <?php echo __( 'Package Number', 'coolrunner-shipping-plugin' ) ?>:
            </label>
        <input type="text" disabled value="<?php echo $order->get_meta( '_coolrunner_package_number_second' ) ?>">
            <div class="row">
                <div class="column">
                    <label>
                        <?php echo __( 'Cost incl. tax', 'coolrunner-shipping-plugin' ) ?>:
                    </label>
                    <input type="text" disabled value="DKK<?php echo number_format( floatval( $order->get_meta( '_coolrunner_price_incl_tax_second' ) ), 2 ) ?>">
                </div>
                <div class="column">
                    <label>
                        <?php echo __( 'Cost excl. tax', 'coolrunner-shipping-plugin' ) ?>:
                    </label>
                    <input type="text" disabled value="DKK<?php echo number_format( floatval( $order->get_meta( '_coolrunner_price_excl_tax_second' ) ), 2 ) ?>">
                </div>
            </div>
            <button id="coolrunner_delete_shipment_second" type="button" class="button button-danger"><?php echo __( 'Delete Shipment', 'coolrunner-shipping-plugin' ) ?></button>
            <button data-href="<?php echo COOLRUNNER_PLUGIN_URL . '/pdf.php?download=1&second=1&order_id=' . $post_id ?>" id="coolrunner_download_label_second" type="button" class="button"><?php echo __( 'Download Label', 'coolrunner-shipping-plugin' ) ?></button>
            <button data-href="<?php echo COOLRUNNER_PLUGIN_URL . '/pdf.php?second=1&order_id=' . $post_id ?>" id="coolrunner_show_label_second" type="button" class="button"><?php echo __( 'Show Label', 'coolrunner-shipping-plugin' ) ?></button>
            <script>
                jQuery(function ($) {
                    let crmeta = $('#coolrunner-meta-box'),
                        deleteBtnSecond = crmeta.find('#coolrunner_delete_shipment_second'),
                        labelBtnsSecond = crmeta.find('#coolrunner_download_label_second, #coolrunner_show_label_second');

                    deleteBtnSecond.on('click', function () {
                        if (confirm('<?php echo __( 'Do you wish to remove the shipment from this order?\nCannot be undone!\nDoes not cancel the shipment!', 'coolrunner-shipping-plugin' ) ?>')) {
                            $.ajax({
                                method: 'post',
                                url: ajaxurl,
                                data: {
                                    action: 'coolrunner_delete_shipment_second',
                                    order_id: '<?php echo $post_id ?>'
                                },
                                success: function (data) {
                                    alert('<?php echo __( 'Shipment removed', 'coolrunner-shipping-plugin' ) ?>');
                                    crmeta.find('.inside').html(data.new_content);
                                },
                                error: function (data) {
                                    console.log(data);
                                    alert('<?php echo __( 'Failed to remove shipment', 'coolrunner-shipping-plugin' ) ?>');
                                }
                            });
                        }
                    });

                    labelBtnsSecond.on('click', function () {
                        window.open($(this).attr('data-href'));
                    });
                });
            </script>

        <?php endif; ?>
            <!-- ANOTHER SHIPMENT END -->
        <?php else : ?>
            <?php if ( ! empty( CoolRunner::getBoxSizes() ) ) : ?>
            <label for="coolrunner_box_size">
                <?php echo __( 'Box Size', 'coolrunner-shipping-plugin' ) ?>:
            </label>
            <select id="coolrunner_box_size">
                <?php foreach ( CoolRunner::getBoxSizes() as $size ) : ?>
                    <option data-height="<?php echo $size['height'] ?>"
                            data-width="<?php echo $size['width'] ?>"
                            data-length="<?php echo $size['length'] ?>"
                            data-weight="<?php echo $size['weight'] ?>"
                        <?php echo $size['primary'] ? 'selected' : '' ?>>
                        <?php echo $size['name'] ?>
                    </option>
                    <?php $primary = $size['primary'] ? $size : $primary; ?>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>
            <label for="coolrunner_height">
                <?php echo __( 'Height (cm)', 'coolrunner-shipping-plugin' ) ?>:
            </label>
        <input type="number" name="coolrunner_height" min="1" id="coolrunner_height" value="<?php echo $primary['height'] ?>">
            <label for="coolrunner_width">
                <?php echo __( 'Width (cm)', 'coolrunner-shipping-plugin' ) ?>:
            </label>
        <input type="number" name="coolrunner_width" min="1" id="coolrunner_width" value="<?php echo $primary['width'] ?>">
            <label for="coolrunner_length">
                <?php echo __( 'Length (cm)', 'coolrunner-shipping-plugin' ) ?>:
            </label>
        <input type="number" name="coolrunner_length" min="1" id="coolrunner_length" value="<?php echo $primary['length'] ?>">
            <label for="coolrunner_weight">
                <?php echo __( 'Weight (g)', 'coolrunner-shipping-plugin' ) ?>:
            </label>
        <input type="number" name="coolrunner_weight" min="0" step="0.01" id="coolrunner_weight" value="<?php echo $weight * 1000 ?>">
            <button id="coolrunner_create" class="button button-primary" type="button" style="width: 100%;">
                <?php echo __( 'Create Shipment', 'coolrunner-shipping-plugin' ) ?>
            </button>
            <script>
                jQuery(function ($) {
                    let crmeta = $('#coolrunner-meta-box'),
                        inputs = crmeta.find('select[name], input[name]'),
                        createBtn = crmeta.find('#coolrunner_create'),
                        sizeSelect = crmeta.find('select');

                    sizeSelect.on('change input', function () {
                        let option = $(this).find(':selected'),
                            props = ['height', 'length', 'width'];

                        props.forEach(function (e) {
                            // console.log(e);
                            crmeta.find('[name=coolrunner_' + e + ']').val(option.attr('data-' + e));
                        })
                    });

                    createBtn.on('click', function () {
                        if (confirm('Vil du oprette forsendelse på denne ordre?')) {
                            let data = {
                                action: 'coolrunner_create_shipment'
                            };

                            let errors = [];

                            inputs.each(function () {
                                data[$(this).attr('name').replace('coolrunner_', '')] = $(this).val();
                                if ($(this).val() === '') {
                                    errors.push('Missing field: ' + $(this).attr('name').replace('coolrunner_', ''));
                                } else if ($(this).attr('type') === 'number' && $(this).val() < 1) {
                                    errors.push('Field is too small: ' + $(this).attr('name').replace('coolrunner_', ''));
                                }
                            });

                            if (errors.length) {
                                let errStr = errors.join("\n");
                                alert(errStr);
                                return;
                            }

                            $('#coolrunner-meta-overlay').fadeIn();

                            $.ajax({
                                method: 'post',
                                url: ajaxurl,
                                data: data,
                                success: function (data) {
                                    if (data.created && data.exists) {
                                        alert('<?php echo __( 'Shipment Created', 'coolrunner-shipping-plugin' ) ?>');
                                    } else if (!data.created && data.exists) {
                                        alert('<?php echo __( 'Shipment Exists', 'coolrunner-shipping-plugin' ) ?>');
                                    } else {
                                        alert(data.errors);
                                    }
                                    crmeta.find('.inside').html(data.new_content);

                                    $('#coolrunner-meta-overlay').fadeOut();
                                },
                                error: function (data) {
                                    alert(data);
                                    $('#coolrunner-meta-overlay').fadeOut();
                                }
                            })
                        }
                    });
                })
            </script>
        <?php endif; ?>
    </div>
    <div id="coolrunner-meta-overlay">
        <img src="<?php echo COOLRUNNER_PLUGIN_URL . '/assets/images/ajax-loader.gif' ?>">
    </div>
    <?php

    return ob_get_clean();
}

function crship_register_customer_shipping( $force = false ) {
    $phone = ! ! get_option( 'woocommerce_store_phone' );
    $email = ! ! get_option( 'woocommerce_store_email' );
    if ( ! $phone || ! $email ) {
        CoolRunner::showNotice(
            [
                __( 'Store phone and/or email not set', 'coolrunner-shipping-plugin' ),
                sprintf( __( 'Please set this in <a href="%s">WooCommerce Settings</a>', 'coolrunner-shipping-plugin' ), get_site_url( null, '/wp-admin/admin.php?page=wc-settings' ) )
            ], 'error', false );
    }

    $last_run = get_option( 'coolrunner_last_sync', 0 );

    // Piggy back on any request once per hour to update product lists
    if ( $last_run < time() - 3600 || $force ) {
        update_option( 'coolrunner_last_sync', time() );
        $username    = get_option( 'coolrunner_integration_username' );
        $token       = get_option( 'coolrunner_integration_token' );
        $destination = "v2/freight_rates/" . explode( ':', get_option( 'woocommerce_default_country' ) )[0];
        $curldata    = "";

        $curl     = new CR_Curl();
        $response = $curl->sendCurl( $destination, $username, $token, $curldata, $header_enabled = false, $json = true );
        if ( (int) ! $response ) {
            update_option( 'coolrunner_last_sync', time() - 10000 );
            CoolRunner::showNotice(
                [
                    __( 'CoolRunner could not retrieve information about your account.', 'coolrunner-shipping-plugin' ),
                    __( 'Please check your username and/or token:', 'coolrunner-shipping-plugin' )
                ]
            );

            return false;
        }

        if ( $response->status == "ok" ) {
            $data = json_decode( json_encode( $response->result ), true );
        }

        CoolRunner::showDebugNotice( 'Fetching CoolRunner product information: <pre>' . print_r( $data, true ) . '</pre>' );

        if ( $data ) {
            update_option( 'coolrunner_wc_curl_data', $data );
        }

        if ( ! empty( $data ) ) {
            return $data;
        }

        return false;
    }

    return get_option( 'coolrunner_wc_curl_data' );
}

crship_register_customer_shipping();


function crship_add_coolrunner_pickup_to_checkout() {
    ?>
    <div class="coolrunner_select_shop" data-carrier="" name="coolrunner_select_shop">
        <h3><?php echo __( 'Choose package shop', 'coolrunner-shipping-plugin' ); ?></h3>
        <p><?php echo __( 'Choose where you want your package to be dropped off', 'coolrunner-shipping-plugin' ); ?></p>

        <input type="hidden" name="coolrunner_carrier" id="coolrunner_carrier">
        <label for="coolrunner_zip_code_search" class=""><?php echo __( 'Input Zip Code', 'coolrunner-shipping-plugin' ); ?></label>
        <div class="zip-row">
            <div>
                <input class="input-text" type="text" id="coolrunner_zip_code_search" name="coolrunner_zip_code_search">
            </div>
            <div>
                <button style="width: 100%;" type="button" id="coolrunner_search_droppoints"
                        name="coolrunner_search_droppoints">
                    <?php echo __( 'Search for package shop', 'coolrunner-shipping-plugin' ); ?>
                </button>
            </div>
        </div>
        <div class="clear"></div>
        <div class="coolrunner-droppoints">

        </div>

    </div>
    <?php
}

add_action( 'woocommerce_review_order_before_payment', 'crship_add_coolrunner_pickup_to_checkout' );

add_action( 'wp_ajax_nopriv_coolrunner_droppoint_search', 'crship_coolrunner_droppoint_search' );
add_action( 'wp_ajax_coolrunner_droppoint_search', 'crship_coolrunner_droppoint_search' );

function crship_coolrunner_droppoint_search() {

    global $woocommerce;

    $curl = new CR_Curl();

    $curldata = array(
        "carrier"              => $_POST['carrier'],
        "country_code"         => $_POST['country'],//get_option('coolrunner_settings_sender_country'),
        "zipcode"              => $_POST['zip_code'],
        "city"                 => isset( $_POST['city'] ) ? $_POST['city'] : null,
        "street"               => isset( $_POST['street'] ) ? $_POST['street'] : null,
        "number_of_droppoints" => get_option( 'coolrunner_settings_number_droppoint' )
    );

    $destination = "v2/droppoints/";

    $response = $curl->sendCurl( $destination, get_option( 'coolrunner_integration_username' ), get_option( 'coolrunner_integration_token' ), $curldata, $header_enabled = false, $json = false );
    $response = json_decode( json_encode( $response ), true );

    $radios = array();

    if ( $response['status'] == "ok" && ! empty( $response['result'] ) ) {
        $list = $response['result'];
        $list = array_splice( $list, 0, get_option( 'coolrunner_servicepoints' ) );


        foreach ( $list as $entry ) {
            ob_start();

            $props = array(
                'id'      => $entry['droppoint_id'],
                'name'    => $entry['name'],
                'address' => $entry['address']
            );

            ?>
            <label>
                <input required type="radio" name="coolrunner_droppoint" value='<?php echo base64_encode( json_encode( $props ) ) ?>'>
                <table style="margin: 0;">
                    <colgroup>
                        <col width="1">
                        <col>
                    </colgroup>
                    <tr>
                        <td>
                            <div class="cr-check"></div>
                        </td>
                        <td>
                            <b><?php echo $entry['name'] ?></b>
                            <div>
                                <?php printf( '%s, %s-%s %s', $entry['address']['street'], $entry['address']['country_code'], $entry['address']['postal_code'], $entry['address']['city'] ) ?>
                            </div>
                            <?php if ( $curldata['city'] && $curldata['street'] ) : ?>
                                <div>
                                    <?php echo __( 'Distance', 'coolrunner-shipping-plugin' ) ?>: <?php echo number_format( intval( $entry['distance'] ) / 1000, 2 ) ?>km
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </label>
            <?php
            $radios[] = ob_get_clean();
        }

        echo implode( $radios );
    } else {
        echo print_r( $response, true );
        echo "No Droppoints were found";
    }
    exit();
}

//add_action( 'wp_ajax_coolrunner_save_droppoint', 'coolrunner_save_droppoint' );

add_action( 'woocommerce_checkout_update_order_meta', 'crship_add_order_meta', 10, 2 );
function crship_add_order_meta( $order_id, $posted ) {
    if ( isset( $_POST['coolrunner_droppoint'] ) ) {
        update_post_meta( $order_id, '_coolrunner_droppoint', json_decode( base64_decode( $_POST['coolrunner_droppoint'] ), true ) );
    }
}

function coolrunner_ajax_resend_pdf_script() {

    ?>
    <script type="text/javascript">
        jQuery(function ($) {
            $(document).on('click', '[name="coolrunner_ajax_resend_call"]', function () {
                var id = jQuery(this).data('order-id');

                $.ajax({
                    method: "POST",
                    url: ajaxurl,
                    data: {
                        'action': 'coolrunner_resend_label_notification',
                        'id': id
                    }
                })
                    .done(function (data) {
                        if (data.errors.length !== 0) {
                            alert(data.errors.join(' | '));
                            return;
                        } else if (data.sent && data.created) {
                            alert('Shipment sent to PCN and notification sent');
                        } else if (data.sent) {
                            alert('Notification sent');
                        } else if (data.created) {
                            alert('Shipment sent to PCN');
                        }
                        $('#coolrunner-shipment-information').replaceWith(data.new_content);
                        // location.reload(true);
                    })
                    .fail(function (data) {
                        jQuery('.coolrunner-notification').remove();
                        jQuery("#wpbody-content .wrap").prepend('<div id="cr-notif" class="coolrunner-notification notice notice-error is-dismissible"><p> Error, could not display order</p></div>');
                    });

            })
        })

    </script><?php
}

add_action( 'admin_footer', 'coolrunner_ajax_resend_pdf_script' );

function coolrunner_create_shipment( $post_id = null ) {

    if ( ! empty( $_POST['id'] ) || ! is_null( $post_id ) ) {

        $order_id = $post_id ? $post_id : $_POST['id'];

        $order = new WC_Order( $order_id );

        $destination = "v1/shipment/create";

        $created = false;
        $errors  = [];

        if ( ! get_post_meta( $order->get_id(), '_coolrunner_package_number', true ) ) {
            $curldata = create_shipment_array( $order );

            if ( ! $curldata ) {
                return;
            }

            $curl = new CR_Curl();

            $response = $curl->sendCurl( $destination, get_option( 'coolrunner_integration_username' ), get_option( 'coolrunner_integration_token' ), $curldata, $recieve_responsecode = false, $json = true );

            if ($response->result->status == 'ok' || $response->status == 'ok') {
                update_post_meta( $order_id, '_coolrunner_package_number', $response->result->package_number );
                update_post_meta( $order_id, '_coolrunner_pdf_link', $response->result->pdf_link );
                update_post_meta( $order_id, '_coolrunner_price_incl_tax', $response->result->price_incl_tax );
                update_post_meta( $order_id, '_coolrunner_price_excl_tax', $response->result->price_excl_tax );
                update_post_meta( $order_id, '_coolrunner_pdf', $response->result->pdf_base64 );
            } else {
                if ( isset( $response->result->message ) ) {
                    $errors = $response->result->message;
                } else {
                    $errors[] = $response->result->emessage;
                }
            }


            $created = isset($response) && ($response->result->status === 'ok' || $response->status == 'ok');
        }

        $content = crship_get_metabox_content( $order_id );


        if ( $created ) {
            $order->add_order_note( sprintf( __( 'Created shipment: %s', 'coolrunner-shipping-plugin' ), $response->result->package_number ) );
        }

        $return = array(
            'created'     => $created,
            'exists'      => ! ! get_post_meta( $order->get_id(), '_coolrunner_package_number', true ),
            'new_content' => $content,
            'errors'      => implode( "\n", $errors )
        );
        //	$data = $tracking_array;
        //	echo $data->package_number;
        if ( ! $post_id ) {
            header( 'Content-Type: application/json' );
            echo json_encode( $return );
            exit();
        } else {
            return $return;
        }

        //Returner korrekt værdi udfra respons kode
        //echo $response['http_code'];


    } else {
        echo "ID was not found";
    }

    exit; // just to be safe
}

function coolrunner_create_shipment_second( $post_id = null ) {

    error_log('runned coolrunner_create_shipment_second');

    if ( ! empty( $_POST['id'] ) || ! is_null( $post_id ) ) {

        $order_id = $post_id ? $post_id : $_POST['id'];

        $order = new WC_Order( $order_id );

        $destination = "v1/shipment/create";

        $created = false;
        $errors  = [];

        if ( ! get_post_meta( $order->get_id(), '_coolrunner_package_number_second', true ) ) {
            $curldata = create_shipment_array_second( $order );

            if ( ! $curldata ) {
                return;
            }

            $curl = new CR_Curl();

            $response = $curl->sendCurl( $destination, get_option( 'coolrunner_integration_username' ), get_option( 'coolrunner_integration_token' ), $curldata, $recieve_responsecode = false, $json = true );

            if ($response->result->status == 'ok' || $response->status == 'ok') {
                update_post_meta( $order_id, '_coolrunner_package_number_second', $response->result->package_number );
                update_post_meta( $order_id, '_coolrunner_pdf_link_second', $response->result->pdf_link );
                update_post_meta( $order_id, '_coolrunner_price_incl_tax_second', $response->result->price_incl_tax );
                update_post_meta( $order_id, '_coolrunner_price_excl_tax_second', $response->result->price_excl_tax );
                update_post_meta( $order_id, '_coolrunner_pdf_second', $response->result->pdf_base64 );
            } else {
                if ( isset( $response->result->message ) ) {
                    $errors = $response->result->message;
                } else {
                    $errors[] = $response->result->emessage;
                }
            }


            $created = isset($response) && ($response->result->status === 'ok' || $response->status == 'ok');
        }

        $content = crship_get_metabox_content( $order_id );


        if ( $created ) {
            $order->add_order_note( sprintf( __( 'Created shipment: %s', 'coolrunner-shipping-plugin' ), $response->result->package_number ) );
        }

        $return = array(
            'created'     => $created,
            'exists'      => ! ! get_post_meta( $order->get_id(), '_coolrunner_package_number_second', true ),
            'new_content' => $content,
            'errors'      => implode( "\n", $errors )
        );
        //	$data = $tracking_array;
        //	echo $data->package_number;
        if ( ! $post_id ) {
            header( 'Content-Type: application/json' );
            echo json_encode( $return );
            exit();
        } else {
            return $return;
        }

        //Returner korrekt værdi udfra respons kode
        //echo $response['http_code'];


    } else {
        echo "ID was not found";
    }

    exit; // just to be safe
}


function coolrunner_delete_shipment( $order_id ) {
    $order      = new WC_Order( $order_id );
    $package_no = get_post_meta( $order_id, '_coolrunner_package_number', true );

    $order->add_order_note( sprintf( __( 'Removed shipment: %s', 'coolrunner-shipping-plugin' ), $package_no ) );
    $keys = [
        '_coolrunner_package_number',
        '_coolrunner_pdf_link',
        '_coolrunner_price_incl_tax',
        '_coolrunner_price_excl_tax',
        '_coolrunner_pdf'
    ];
    foreach ( $keys as $key ) {
        delete_post_meta( $order_id, $key );
    }

    return true;
}

function coolrunner_delete_shipment_second( $order_id ) {
    $order      = new WC_Order( $order_id );
    $package_no = get_post_meta( $order_id, '_coolrunner_package_number_second', true );

    $order->add_order_note( sprintf( __( 'Removed shipment: %s', 'coolrunner-shipping-plugin' ), $package_no ) );
    $keys = [
        '_coolrunner_package_number_second',
        '_coolrunner_pdf_link_second',
        '_coolrunner_price_incl_tax_second',
        '_coolrunner_price_excl_tax_second',
        '_coolrunner_pdf_second'
    ];
    foreach ( $keys as $key ) {
        delete_post_meta( $order_id, $key );
    }

    return true;
}

function coolrunner_send_tracking_email( $order_id ) {
    $order      = new WC_Order( $order_id );
    $package_no = get_post_meta( $order_id, '_coolrunner_package_number', true );

    $customer = new WC_Customer( $order->get_customer_id() );

    $placeholders = array(
        '{first_name}'     => $customer->get_first_name(),
        '{last_name}'      => $customer->get_last_name(),
        '{email}'          => $customer->get_email(),
        '{order_no}'       => $order->get_id(),
        '{package_number}' => $package_no
    );

    $text = get_option( 'coolrunner_tracking_email' );

    foreach ( $placeholders as $placeholder => $value ) {
        $text = str_replace( $placeholder, $value, $text );
    }

    ob_start();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>CoolRunner Tracking</title>
    </head>
    <body style="margin: 0; padding: 0;">
    <?php echo $text ?>
    </body>
    </html>
    <?php
    $message = ob_get_clean();

    $to      = $order->get_billing_email();
    $subject = implode( ' | ', array( get_bloginfo( 'name' ), 'CoolRunner Tracking', "Order no. #{$order->get_order_number()}" ) );
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        sprintf( 'From: %s <%s>', get_bloginfo( 'name' ), get_option( 'woocommerce_store_email' ) )
    );

    $sent = wp_mail( $to, $subject, $message, $headers, $attachments = "" );
    if ( $sent ) {
        $order->add_order_note( sprintf( __( 'Tracking information sent to: %s', 'coolrunner-shipping-plugin' ), $to ) );
    }
    update_post_meta( $order_id, '_coolrunner_email_sent', (int) $sent );

    return $sent;
}


function coolrunner_get_tracking_data( $order_id ) {


    if ( ! empty( $order_id ) ) {

        //	$destination = get_post_meta($order_id,'coolrunner_pdf_link', true );

        $package_number = get_post_meta( $order_id, '_coolrunner_package_number', true );
        if ( $package_number ) {
            $destination = "v1/tracking/" . $package_number;

            $curldata = array();
            $curl     = new CR_Curl();

            $response = $curl->sendCurl( $destination, get_option( 'coolrunner_integration_username' ), get_option( 'coolrunner_integration_token' ), $curldata, $recieve_responsecode = false, $json = true );

            return $response;
        } else {
            return null;
        }
    }

}

add_filter( 'woocommerce_general_settings', function ( $arr ) {
    $offset = 0;
    foreach ( $arr as $i => $setting ) {
        if ( $setting['id'] === 'woocommerce_store_postcode' ) {
            $offset = $i + 1;
            break;
        }
    }
    $chopped = array_splice( $arr, $offset );
    $arr[]   = array(
        'title'    => __( 'Phone', 'coolrunner-shipping-plugin' ),
        'desc'     => __( 'The phone number for your business location.', 'coolrunner-shipping-plugin' ),
        'id'       => 'woocommerce_store_phone',
        'default'  => '',
        'type'     => 'text',
        'desc_tip' => true,
    );
    $arr[]   = array(
        'title'    => __( 'Email', 'coolrunner-shipping-plugin' ),
        'desc'     => __( 'The email address for your business location.', 'coolrunner-shipping-plugin' ),
        'id'       => 'woocommerce_store_email',
        'default'  => '',
        'type'     => 'text',
        'desc_tip' => true,
    );
    foreach ( $chopped as $setting ) {
        $arr[] = $setting;
    }

    return $arr;
} );

/**
 * @param WC_Order $order
 *
 * @return array
 */
function create_shipment_array( $order ) {

    //$dp = ( isset( $filter['dp'] ) ? intval( $filter['dp'] ) : 2 );
    $order_post = get_post( $order->get_id() );


    //$chosen_methods = WC()->customer->get( 'chosen_shipping_methods' );
    //$chosen_methods = $order->get_items( 'shipping' );
    //$chosen_shipping = $chosen_methods[0];
    $shipping_items = $order->get_items( 'shipping' );
    $key            = array_keys( $shipping_items );

    $chosen_shipping = $shipping_items[ $key[0] ]['method_id'];
    $matches         = explode( '_', $chosen_shipping );

    $user_info      = get_userdata( 1 );
    $add_order_note = get_post_meta( $order->get_id(), 'add_order_note', true );

    $droppoint = get_post_meta( $order->get_id(), '_coolrunner_droppoint', true );

    $shipping_method = CoolRunner::getCoolRunnerShippingMethod( $order->get_id() );

    if ( $shipping_method ) {
        if ( $droppoint ) {
            $drop_id      = $droppoint['id'];
            $drop_name    = $droppoint['name'];
            $drop_street  = $droppoint['address']['street'];
            $drop_zip     = $droppoint['address']['postal_code'];
            $drop_city    = $droppoint['address']['city'];
            $drop_country = $droppoint['address']['country_code'];
        } else {
            $drop_id      = 0;
            $drop_name    = '';
            $drop_street  = '';
            $drop_zip     = '';
            $drop_city    = '';
            $drop_country = '';
        }

        $size = get_post_meta( $order->get_id(), '_coolrunner_package_size', true );

        $array = array(
            'order_number'          => $order->get_order_number(),
            'receiver_name'         => $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name(),
            "receiver_attention"    => $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name(),
            'receiver_street1'      => $order->get_shipping_address_1(),
            'receiver_street2'      => $order->get_shipping_address_2(),
            'receiver_zipcode'      => $order->get_shipping_postcode(),
            'receiver_city'         => $order->get_shipping_city(),
            'receiver_country'      => $order->get_shipping_country(),
            'receiver_phone'        => $order->get_billing_phone(),
            'receiver_email'        => $order->get_billing_email(),
            "receiver_notify"       => true,
            "receiver_notify_sms"   => $order->get_billing_phone(),
            "receiver_notify_email" => $order->get_billing_email(),
            "sender_name"           => get_option( 'coolrunner_store_name' ),
            'sender_attention'      => "",
            "sender_street1"        => WC()->countries->get_base_address(),
            'sender_street2'        => WC()->countries->get_base_address_2(),
            "sender_zipcode"        => WC()->countries->get_base_postcode(),
            "sender_city"           => WC()->countries->get_base_city(),
            "sender_country"        => get_option( 'woocommerce_default_country' ),
            "sender_phone"          => get_option( 'woocommerce_store_phone' ),
            "sender_email"          => get_option( 'woocommerce_store_email' ),
            "carrier"               => $shipping_method->getCarrier(),
            "carrier_product"       => $shipping_method->getProduct(),
            "carrier_service"       => $shipping_method->getService(),
            "length"                => $size['length'],
            "width"                 => $size['width'],
            "height"                => $size['height'],
            "weight"                => $size['weight'],
            "reference"             => "Order no: " . $order->get_order_number(),
            "label_format"          => get_option( 'coolrunner_label_format', 'A4' ),
            'description'           => $add_order_note,
            'comment'               => "",
            'droppoint_id'          => $drop_id,
            'droppoint_name'        => $drop_name,
            'droppoint_street1'     => $drop_street,
            'droppoint_zipcode'     => $drop_zip,
            'droppoint_city'        => $drop_city,
            'droppoint_country'     => $drop_country
        );

        return $array;
    }

    return false;
}

function create_shipment_array_second( $order ) {

    //$dp = ( isset( $filter['dp'] ) ? intval( $filter['dp'] ) : 2 );
    $order_post = get_post( $order->get_id() );


    //$chosen_methods = WC()->customer->get( 'chosen_shipping_methods' );
    //$chosen_methods = $order->get_items( 'shipping' );
    //$chosen_shipping = $chosen_methods[0];
    $shipping_items = $order->get_items( 'shipping' );
    $key            = array_keys( $shipping_items );

    $chosen_shipping = $shipping_items[ $key[0] ]['method_id'];
    $matches         = explode( '_', $chosen_shipping );

    $user_info      = get_userdata( 1 );
    $add_order_note = get_post_meta( $order->get_id(), 'add_order_note', true );

    $droppoint = get_post_meta( $order->get_id(), '_coolrunner_droppoint', true );

    $shipping_method = CoolRunner::getCoolRunnerShippingMethod( $order->get_id() );

    if ( $shipping_method ) {
        if ( $droppoint ) {
            $drop_id      = $droppoint['id'];
            $drop_name    = $droppoint['name'];
            $drop_street  = $droppoint['address']['street'];
            $drop_zip     = $droppoint['address']['postal_code'];
            $drop_city    = $droppoint['address']['city'];
            $drop_country = $droppoint['address']['country_code'];
        } else {
            $drop_id      = 0;
            $drop_name    = '';
            $drop_street  = '';
            $drop_zip     = '';
            $drop_city    = '';
            $drop_country = '';
        }

        $size = get_post_meta( $order->get_id(), '_coolrunner_package_size_second', true );

        error_log('sizes array: ' . print_r($size, 1));

        $array = array(
            'order_number'          => $order->get_order_number(),
            'receiver_name'         => $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name(),
            "receiver_attention"    => $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name(),
            'receiver_street1'      => $order->get_shipping_address_1(),
            'receiver_street2'      => $order->get_shipping_address_2(),
            'receiver_zipcode'      => $order->get_shipping_postcode(),
            'receiver_city'         => $order->get_shipping_city(),
            'receiver_country'      => $order->get_shipping_country(),
            'receiver_phone'        => $order->get_billing_phone(),
            'receiver_email'        => $order->get_billing_email(),
            "receiver_notify"       => true,
            "receiver_notify_sms"   => $order->get_billing_phone(),
            "receiver_notify_email" => $order->get_billing_email(),
            "sender_name"           => get_option( 'coolrunner_store_name' ),
            'sender_attention'      => "",
            "sender_street1"        => WC()->countries->get_base_address(),
            'sender_street2'        => WC()->countries->get_base_address_2(),
            "sender_zipcode"        => WC()->countries->get_base_postcode(),
            "sender_city"           => WC()->countries->get_base_city(),
            "sender_country"        => get_option( 'woocommerce_default_country' ),
            "sender_phone"          => get_option( 'woocommerce_store_phone' ),
            "sender_email"          => get_option( 'woocommerce_store_email' ),
            "carrier"               => $shipping_method->getCarrier(),
            "carrier_product"       => $shipping_method->getProduct(),
            "carrier_service"       => $shipping_method->getService(),
            "length"                => $size['length'],
            "width"                 => $size['width'],
            "height"                => $size['height'],
            "weight"                => $size['weight'],
            "reference"             => "Order no: " . $order->get_order_number(),
            "label_format"          => get_option( 'coolrunner_label_format', 'A4' ),
            'description'           => $add_order_note,
            'comment'               => "",
            'droppoint_id'          => $drop_id,
            'droppoint_name'        => $drop_name,
            'droppoint_street1'     => $drop_street,
            'droppoint_zipcode'     => $drop_zip,
            'droppoint_city'        => $drop_city,
            'droppoint_country'     => $drop_country
        );

        error_log(print_r($array, 1));

        return $array;
    }

    return false;
}

// Hook in
add_filter( 'woocommerce_default_address_fields', 'coolrunner_override_default_address_fields' );

// Our hooked in function - $address_fields is passed via the filter!
function coolrunner_override_default_address_fields( $address_fields ) {
    return $address_fields;
}

// Add order new column in administration
add_filter( 'manage_edit-shop_order_columns', 'crship_status_custom_column', 20 );
function crship_status_custom_column( $columns ) {
    $offset = 8;
    foreach ( array_keys( $columns ) as $key => $value ) {
        if ( $value === 'order_total' ) {
            $offset = $key;
            break;
        }
    }
    $updated_columns = array_slice( $columns, 0, $offset, true ) +
        array(
            'cr_pack_num' => '<div style="text-align:center;">' . esc_html__( 'CoolRunner', 'coolrunner-shipping-plugin' ) . '</div>'
        ) +
        array_slice( $columns, $offset, null, true );

    return $updated_columns;
}

// Populate weight column
add_action( 'manage_shop_order_posts_custom_column', 'crship_status_column', 2 );
function crship_status_column( $column ) {
    global $post;
    $package_number = get_post_meta( $post->ID, '_coolrunner_package_number', true );
    if ( $column == 'cr_pack_num' ) {
        if ( $package_number ) {
            ?>
            <div class="button-group">
                <a href="<?php echo COOLRUNNER_PLUGIN_URL . '/pdf.php?download=1&order_id=' . $post->ID ?>" class="button button-small button-primary">
                    <?php echo __( 'Download Label', 'coolrunner-shipping-plugin' ) ?>
                </a>
                <a target="_blank" title="<?php echo __( 'Show Label', 'coolrunner-shipping-plugin' ) ?>" href="<?php echo COOLRUNNER_PLUGIN_URL . '/pdf.php?order_id=' . $post->ID ?>" class="button button-small">
                    <?php echo $package_number ?>
                </a>
            </div>
            <?php
            echo "";
        } else {
            echo '';
        }
    }
}

//checkout validation

add_action( 'woocommerce_checkout_process', 'crship_is_droppoint' );
if ( ! function_exists( 'crship_is_droppoint' ) ) {
    function crship_is_droppoint() {
        $chosen_methods  = WC()->session->get( 'chosen_shipping_methods' );
        $chosen_shipping = $chosen_methods[0];

        $matches = array();
        $matches = explode( '_', $chosen_shipping );


        if ( $matches[0] == "coolrunner" && end( $matches ) == "droppoint" ) {
            $droppoint_selection = isset( $_POST['coolrunner_droppoint'] ) ? str_replace( '\"', '"', base64_decode( $_POST['coolrunner_droppoint'] ) ) : false;
            $droppoint_selection = json_decode( $droppoint_selection, true );
            if ( ! is_array( $droppoint_selection ) ) {
                wc_add_notice( __( 'Please select your package shop.', 'coolrunner-shipping-plugin' ), 'error' );
            }
        }
    }
}

// bulk action
add_action( 'admin_footer-edit.php', 'crship_custom_bulk_admin_footer' );

function crship_custom_bulk_admin_footer() {
    return;
    global $post_type;

    if ( $post_type == 'shop_order' ) {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function () {
                jQuery('<option>').val('coolrunner-pdf').text('<?php _e( 'Send to PCN / Re-send tracking' )?>').appendTo("select[name='action']");
            });
        </script>
        <?php
    }
}


add_action( 'load-edit.php', 'crship_custom_bulk_action' );
function crship_custom_bulk_action() {
    return;
    // Make sure that we on "Woocomerce orders list" page
    if ( ! isset( $_GET['post_type'] ) || $_GET['post_type'] != 'shop_order' ) {
        return;
    }
    if ( isset( $_GET['action'] ) && $_GET['post_type'] == 'shop_order' ) {
        // Check Nonce


        if ( ! check_admin_referer( "bulk-posts" ) ) {
            return;
        }

        if ( $_GET['action'] == 'coolrunner-pdf' ) {
            // Remove 'set-' from action
            //    $new_status =  substr( $_GET['action'], 4 );
            $posts = $_GET['post'];

            foreach ( $posts as $post_id ) {
                $notif = '';
                $class = 'success';

                //	$order = new WC_Order( (int)$post_id );
                $result = coolrunner_create_shipment( $post_id );
                if ( $result['created'] || $result['sent'] ) {
                    if ( $result['created'] ) {
                        $notif .= "<p>Order no. $post_id sent to PCN</p>";
                    }
                    if ( $result['sent'] ) {
                        $notif .= "<p>Tracking email for order no. $post_id sent to customer</p>";
                    }
                }

                if ( ! $result['created'] && ! $result['sent'] ) {
                    $notif = __( 'Unable to create or re-send email for order: ', 'coolrunner-shipping-plugin' ) . " $post_id";
                    $class = 'error';
                }
            }
        }

    }

}


// bulk action
add_action( 'admin_footer', 'crship_custom_js_admin_footer' );

function crship_custom_js_admin_footer() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function () {
            //	  jQuery('#coolrunner_package_name').on("change",function(){
            jQuery(document).on('change', '#coolrunner_package_name', function () {
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

/*
 * Add bulk action to handle orders
 * Added by Kevin Hansen 12/02/2020
 * */


// Adding bulk action to list
add_filter( 'bulk_actions-edit-shop_order', 'bulk_action_sent_selected_orders', 20, 1 );
function bulk_action_sent_selected_orders( $actions ) {
    $actions['sent_all_orders'] = __( 'Sent all marked orders', 'coolrunner-shipping-plugin' );
    return $actions;
}

// Make the action from selected orders
add_filter( 'handle_bulk_actions-edit-shop_order', 'sent_handle_bulk_action_edit_shop_order', 10, 3 );
function sent_handle_bulk_action_edit_shop_order( $redirect_to, $action, $post_ids ) {
    if ( $action !== 'sent_all_orders' )
        return $redirect_to; // Exit

    $processed_ids = array();

    foreach ( $post_ids as $post_id ) {
        $order = wc_get_order( $post_id );
        $order_data = $order->get_data();

        coolrunner_create_shipment($post_id);

        $processed_ids[] = $post_id;
    }

    return $redirect_to = add_query_arg( array(
        'download_marked_orders' => '1',
        'processed_count' => count( $processed_ids ),
        'processed_ids' => implode( ',', $processed_ids ),
    ), $redirect_to );
}

// The results notice from bulk action on orders
add_action( 'admin_notices', 'sent_bulk_action_admin_notice' );
function sent_bulk_action_admin_notice() {
    if ( empty( $_REQUEST['download_marked_orders'] ) ) return; // Exit

    $count = intval( $_REQUEST['processed_count'] );

    printf( '<div id="message" class="updated fade"><p>' .
        _n( __('Processed %s Order for printing.', 'coolrunner-shipping-plugin'),
            __('Processed %s Orders for printing.', 'coolrunner-shipping-plugin'),
            $count,
            'download_marked_orders'
        ) . '</p></div>', $count );
}
