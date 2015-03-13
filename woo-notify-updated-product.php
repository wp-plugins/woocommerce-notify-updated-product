<?php
/**
 * woo-notify-updated-product.php
 *
 * Copyright (c) 2011,2012 Antonio Blanco http://www.eggemplo.com
 *
 * This code is released under the GNU General Public License.
 * See COPYRIGHT.txt and LICENSE.txt.
 *
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This header and all notices must be kept intact.
 *
 * @author Antonio Blanco (eggemplo)
 * @package woonotifyupdatedproduct
 * @since woonotifyupdatedproduct 1.0.0
 *
 * Plugin Name: Woocommerce Notify Updated Product
 * Plugin URI: http://www.eggemplo.com/plugins/woocommerce-notify-updated-product
 * Description: Notify customers when their products are updated.
 * Version: 1.0.0
 * Author: eggemplo
 * Author URI: http://www.eggemplo.com
 * License: GPLv3
 */

define( 'WOO_NOTIFY_UPDATED_PRODUCT_DOMAIN', 'woonotifyupdatedproduct' );
define( 'WOO_NOTIFY_UPDATED_PRODUCT_PLUGIN_NAME', 'woo-notify-updated-product' );

define( 'WOO_NOTIFY_UPDATED_PRODUCT_FILE', __FILE__ );

if ( !defined( 'WOO_NOTIFY_UPDATED_PRODUCT_CORE_DIR' ) ) {
	define( 'WOO_NOTIFY_UPDATED_PRODUCT_CORE_DIR', WP_PLUGIN_DIR . '/woo-notify-updated-product/core' );
}


class WooNotifyUpdatedProduct_Plugin {
	
	private static $notices = array();
	
	public static function init() {
			
		load_plugin_textdomain( WOO_NOTIFY_UPDATED_PRODUCT_DOMAIN, null, WOO_NOTIFY_UPDATED_PRODUCT_PLUGIN_NAME . '/languages' );
		
		register_activation_hook( WOO_NOTIFY_UPDATED_PRODUCT_FILE, array( __CLASS__, 'activate' ) );
		register_deactivation_hook( WOO_NOTIFY_UPDATED_PRODUCT_FILE, array( __CLASS__, 'deactivate' ) );
		
		register_uninstall_hook( WOO_NOTIFY_UPDATED_PRODUCT_FILE, array( __CLASS__, 'uninstall' ) );
		
		add_action( 'init', array( __CLASS__, 'wp_init' ) );
		add_action( 'admin_notices', array( __CLASS__, 'admin_notices' ) );
		
		
	}
	
	public static function wp_init() {
		
		if ( is_multisite() ) {
			$active_sitewide_plugins = array_keys( get_site_option( 'active_sitewide_plugins', array() ) );
			$active_plugins = array_merge( get_option( 'active_plugins', array() ), $active_sitewide_plugins );
		} else {
			$active_plugins = get_option( 'active_plugins', array() );
		}
		$woo_is_active = in_array( 'woocommerce/woocommerce.php', $active_plugins );
		
		if ( !$woo_is_active ) {
			if ( !$woo_is_active ) {
				self::$notices[] = "<div class='error'>" . __( 'The <strong>Woocommerce Notify Updated Product</strong> plugin requires the <a href="http://wordpress.org/extend/plugins/woocommerce" target="_blank">Woocommerce</a> plugin to be activated.', WOO_NOTIFY_UPDATED_PRODUCT_DOMAIN ) . "</div>";
			} 
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			deactivate_plugins( array( __FILE__ ) );
		} else {
			add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ), 40 );
			//call register settings function
			add_action( 'admin_init', array( __CLASS__, 'register_woonotifyupdatedproduct_settings' ) );
			
			if ( !class_exists( "WooNotifyUpdatedProduct" ) ) {
				include_once 'core/class-woonotifyupdatedproduct.php';
			}
		}
		
	}
	
	/**
	 * Register settings as groups-mailchimp-settings
	 */
	public static function register_woonotifyupdatedproduct_settings() {
		// register_setting( 'woonotifyupdatedproduct', 'wgp-method' );
		// add_option( 'wgp-method','rate' ); // by default rate
	}
	
	public static function admin_notices() { 
		if ( !empty( self::$notices ) ) {
			foreach ( self::$notices as $notice ) {
				echo $notice;
			}
		}
	}
	
	/**
	 * Adds the admin section.
	 */
	public static function admin_menu() {
		$admin_page = add_submenu_page(
				'woocommerce',
				__( 'Notify Updated Product' ),
				__( 'Notify Updated Product' ),
				'manage_options',
				'woonotifyupdatedproduct',
				array( __CLASS__, 'woonotifyupdatedproduct_settings' )
		);
	}
	
	public static function woonotifyupdatedproduct_settings () {
	?>
	<div class="wrap">
	<h2><?php echo __( 'Woocommerce Notify Updated Product', WOO_NOTIFY_UPDATED_PRODUCT_DOMAIN ); ?></h2>
	<?php 
	$alert = "";
	
	if ( isset( $_POST['submit'] ) ) {
		$alert = __("Saved", WOO_NOTIFY_UPDATED_PRODUCT_DOMAIN);
		
		if ( isset( $_POST[ "enable" ] ) ) {
			add_option( "wnup-enable",$_POST[ "enable" ] );
			update_option( "wnup-enable", $_POST[ "enable" ] );
		} else {
			add_option( "wnup-enable", 0 );
			update_option( "wnup-enable", 0 );
		}
		
		$_POST['from'] = stripslashes( $_POST['from'] );
		$_POST['subject'] = stripslashes( $_POST['subject'] );
		$_POST['content'] = stripslashes( $_POST['content'] );
		
		add_option( "wnup-email",$_POST[ "from" ] );
		update_option( "wnup-email", $_POST[ "from" ] );
		
		add_option( "wnup-subject",$_POST[ "subject" ] );
		update_option( "wnup-subject", $_POST[ "subject" ] );
		
		add_option( "wnup-content",$_POST[ "content" ] );
		update_option( "wnup-content", $_POST[ "content" ] );
		
	}
	
	if ($alert != "")
		echo '<div style="background-color: #ffffe0;border: 1px solid #993;padding: 1em;margin-right: 1em;">' . $alert . '</div>';
	
	?>
	<div class="wrap" style="border: 1px solid #ccc; padding:10px;">
	<form method="post" action="">
	    <table class="form-table">
	        <tr valign="top">
	        <th scope="row"><strong><?php echo __( 'Enable:', WOO_NOTIFY_UPDATED_PRODUCT_DOMAIN ); ?></strong></th>
	        <td>
	        	<?php 
				$enable = get_option( "wnup-enable", 1 );
				$checked = "";
				if ( $enable ) {
					$enable = "1";
					$checked = "checked";
				} else {
					$enable = "0";
				}
				?>
				<input type="checkbox" name="enable" value="1" <?php echo $checked; ?> />

			</tr>

			<tr valign="top">
			<th scope="row"><strong><?php echo __( 'From email:', WOO_NOTIFY_UPDATED_PRODUCT_DOMAIN ); ?></strong></th>
			<td>
			<?php 
				$from = get_option( "wnup-email", "" );
				if ( $from == "" ) {
					$from = get_bloginfo('admin_email');
				}
			?>
				<input type="text" name="from" value="<?php echo $from; ?>" />
				<p>
				<span class="description">If empty, admin email will be used.</span>
				</p>
			</tr>

			<tr valign="top">
			<th scope="row"><strong><?php echo __( 'Subject:', WOO_NOTIFY_UPDATED_PRODUCT_DOMAIN ); ?></strong></th>
			<td>
			<?php 
				$subject = get_option( "wnup-subject", "" );
				if ( $subject == "" ) {
					$subject = get_bloginfo('name');
				}
			?>
				<input type="text" name="subject" value="<?php echo $subject; ?>" size="52" />
				<p>
				<span class="description">If empty, blog name will be used.</span>
				</p>
			</tr>

			<tr valign="top">
			<th scope="row"><strong><?php echo __( 'Default email content:', WOO_NOTIFY_UPDATED_PRODUCT_DOMAIN ); ?></strong></th>
			<td>
				<textarea name="content" cols="50" rows="10" ><?php echo get_option( "wnup-content", "" ); ?></textarea>
				<p>
				<span class="description">You can use [product_name] to show the product name.</span>
				</p>
			</tr>

		</table>
	    
	    <?php submit_button( __( "Save", WOO_NOTIFY_UPDATED_PRODUCT_DOMAIN ) ); ?>
	    
	    <?php settings_fields( 'woonotifyupdatedproduct' ); ?>
	    
	</form>
	
	</div>
	</div>
	<?php 
	}
	
	
	/**
	 * Plugin activation work.
	 * 
	 */
	public static function activate() {
	
	}
	
	/**
	 * Plugin deactivation.
	 *
	 */
	public static function deactivate() {
	
	}

	/**
	 * Plugin uninstall. Delete database table.
	 *
	 */
	public static function uninstall() {
	
	}
	
}
WooNotifyUpdatedProduct_Plugin::init();

