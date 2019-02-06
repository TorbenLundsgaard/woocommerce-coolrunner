<?php
/**
 * @package   woocommerce-coolrunner
 * @author    Morten Harders
 * @copyright 2019
 */
?>


<h1>CoolRunner</h1>
<ul id="coolrunner-navigation">
    <li class="<?php echo ( isset( $_GET['section'] ) ? $_GET['section'] : 'settings' ) === 'settings' ? 'active' : '' ?>"><a href="?<?php echo CoolRunner::formatUrl( [ 'section' => 'settings' ] ) ?>">Settings</a></li>
    <li class="<?php echo ( isset( $_GET['section'] ) ? $_GET['section'] : 'settings' ) === 'box-sizes' ? 'active' : '' ?>"><a href="?<?php echo CoolRunner::formatUrl( [ 'section' => 'box-sizes' ] ) ?>">Box Sizes</a></li>
</ul>
