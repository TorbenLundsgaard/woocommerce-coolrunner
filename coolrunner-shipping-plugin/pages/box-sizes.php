<?php
/**
 * @package   woocommerce-coolrunner
 * @author    Morten Harders
 * @copyright 2019
 */

require_once 'nav.php';

$sizes = CoolRunner::getBoxSizes();

if ( isset( $_POST['save'] ) ) {
	$sizes      = [];
	$has_errors = false;
	$clear      = ! isset( $_POST['box-sizes'] );
	if ( ! $clear ) {
		foreach ( $_POST['box-sizes'] as $i => $size ) {
			$size['primary'] = isset( $_POST['primary'] ) && $_POST['primary'] == $i;
			$filled          = false;

			foreach ( $size as $key => $value ) {
				$value        = str_replace( [ '\\"', "\\'" ], [ '"', "'" ], $value );
				$size[ $key ] = $value;
			}

			foreach ( [ 'name', 'height', 'width', 'length', 'weight' ] as $prop ) {
				if ( isset( $size[ $prop ] ) && $size[ $prop ] ) {
					$filled = true;
					break;
				}
			}

			if ( $filled ) {
				$size['errors'] = [];
				foreach ( $size as $key => $item ) {
					if ( $item === '' && $key !== 'primary' ) {
						$size['errors'][ $key ] = true;
						$has_errors             = true;
					}
				}
				$sizes[] = $size;
			}
		}
	}

	if ( ! $has_errors ) {
		CoolRunner::saveBoxSizes( $sizes );
	}
}

if ( empty( $sizes ) ) {
	$sizes[] = [
		'name'    => '',
		'height'  => '',
		'width'   => '',
		'length'  => '',
		'weight'  => '',
		'primary' => true
	];
}

?>
<style>
    #coolrunner-box-sizes-table tbody tr:first-child [data-up] {
        visibility : hidden;
    }

    #coolrunner-box-sizes-table tbody tr:last-child [data-down] {
        visibility : hidden;
    }
</style>
<h2><?php echo __( 'Box Sizes', 'coolrunner-shipping-plugin' ) ?></h2>

<form action="" method="post">
    <table id="coolrunner-box-sizes-table">
        <thead>
        <tr>
            <th><?php echo __( 'Title', 'coolrunner-shipping-plugin' ) ?></th>
            <th><?php echo __( 'Height (cm)', 'coolrunner-shipping-plugin' ) ?></th>
            <th><?php echo __( 'Width (cm)', 'coolrunner-shipping-plugin' ) ?></th>
            <th><?php echo __( 'Length (cm)', 'coolrunner-shipping-plugin' ) ?></th>
            <th><?php echo __( 'Max weight (kg)', 'coolrunner-shipping-plugin' ) ?></th>
            <th><?php echo __( 'Primary', 'coolrunner-shipping-plugin' ) ?></th>
            <th></th>
        </tr>
        </thead>
        <tbody>
		<?php foreach ( $sizes as $i => $boxSize ) : ?>
            <tr>
                <td>
                    <input type="text" name="box-sizes[<?php echo $i ?>][name]" class="<?php echo isset( $boxSize['errors']['name'] ) ? 'invalid' : '' ?>" placeholder="Box title" value="<?php echo $boxSize['name'] ?>">

                </td>
                <td>
                    <input type="number" step="0.01" name="box-sizes[<?php echo $i ?>][height]" placeholder="Box height" value="<?php echo $boxSize['height'] ?>">
                </td>
                <td>
                    <input type="number" step="0.01" name="box-sizes[<?php echo $i ?>][width]" placeholder="Box width" value="<?php echo $boxSize['width'] ?>">
                </td>
                <td>
                    <input type="number" step="0.01" name="box-sizes[<?php echo $i ?>][length]" placeholder="Box length" value="<?php echo $boxSize['length'] ?>">
                </td>
                <td>
                    <input type="number" step="0.01" name="box-sizes[<?php echo $i ?>][weight]" placeholder="Box max weight" value="<?php echo $boxSize['weight'] ?>">
                </td>
                <td class="text-center">
                    <label>
                        <div>
                            <input type="radio" required name="primary" value="<?php echo $i ?>" <?php echo $boxSize['primary'] ? 'checked' : '' ?>>
                        </div>
                    </label>
                </td>
                <td class="text-center">
                    <button type="button" data-up class="button button-primary">↑</button>
                    <button type="button" data-down class="button button-primary">↓</button>
                    <button type="button" data-delete class="button">&times;</button>
                </td>
            </tr>
		<?php endforeach; ?>
        </tbody>
        <tfoot>
        <tr>
            <td colspan="100" class="text-right">
                <button id="coolrunner-add-row" type="button" class="button"><?php echo __( 'Add row', 'coolrunner-shipping-plugin' ) ?> +</button>
                <button name="save" value="1" type="submit" class="button button-primary">
					<?php echo __( 'Save' ) ?>
                </button>
            </td>
        </tr>
        </tfoot>
    </table>
    <script>
        jQuery(function ($) {
            let tmpl = $('#coolrunner-row-template').html(),
                index = <?php echo isset( $i ) ? $i + 1 : 0 ?> +1;

            $('#coolrunner-add-row').on('click', function () {
                $('#coolrunner-box-sizes-table tbody').append($(tmpl.split('{i}').join(index)));
                index++;
            });

            $('body').on('click', '#coolrunner-box-sizes-table button', function () {
                let _ = $(this),
                    parent = _.parents('tr');
                if (_.is('[data-up]')) {
                    parent.insertBefore(parent.prev());
                } else if (_.is('[data-down]')) {
                    parent.insertAfter(parent.next())
                } else if (_.is('[data-delete]')) {
                    if (confirm('<?php echo __( 'Do you want to remove this box size?', 'coolrunner-shipping-plugin' ) ?>')) {
                        parent.remove();
                    }
                }
            });
        })
    </script>
</form>

<template id="coolrunner-row-template">
    <tr>
        <td>
            <input type="text" name="box-sizes[{i}][name]" placeholder="Box title" value="">
        </td>
        <td>
            <input type="number" name="box-sizes[{i}][height]" placeholder="Box height" value="">
        </td>
        <td>
            <input type="number" name="box-sizes[{i}][width]" placeholder="Box width" value="">
        </td>
        <td>
            <input type="number" name="box-sizes[{i}][length]" placeholder="Box length" value="">
        </td>
        <td>
            <input type="number" step="0.01" name="box-sizes[{i}][weight]" placeholder="Box max weight" value="">
        </td>
        <td class="text-center">
            <label>
                <div>
                    <input required type="radio" name="primary" value="{i}">
                </div>
            </label>
        </td>
        <td class="text-center">
            <button type="button" data-up class="button button-primary">↑</button>
            <button type="button" data-down class="button button-primary">↓</button>
            <button type="button" data-delete class="button">&times;</button>
        </td>
    </tr>
</template>