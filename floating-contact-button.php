<?php

/**
 * @package floating_contact_button
 * @version 0.1.0
 */
/*
Plugin Name: Floating Contact Button
Plugin URI: http://wordpress.org/plugins/floating-contact-button/
Description: This plugin is used for generate a call to action with your clients, ¡let's to make a lot of leads!
Author: Juan Castaño
Version: 0.1.0
Author URI: https://jcastanodev.github.io/
*/

include 'includes/lead-controller.php';
include 'admin/admin.php';
include 'public/user.php';

if (! class_exists('FCBJC_Plugin')) {
	class FCBJC_Plugin
	{
		public static function activate()
		{
			set_transient('fcbjc-active-admin-notice', true, 5);
		}

		public static function active_admin_notice()
		{
			if (get_transient('fcbjc-active-admin-notice')) {
?>
				<div class="updated notice is-dismissible">
					<p>¡Gracias por activar nuestro plugin! Esperamos puedas generar muchos leads con nosotros.</p>
				</div>
<?php
				delete_transient('fcbjc-active-admin-notice');
			}
		}

		public static function deactivate()
		{
			// delete_option('fcbjc_settings');
			// unregister_setting('fcbjc_option_group', 'fcbjc_settings');
		}

		public static function init()
		{
			// Hook for adding plugin active admin notice
			add_action('admin_notices', array('FCBJC_Plugin', 'active_admin_notice'));

			// Load resources
			FCBJC_Lead_Controller::instance();
			FCBJC_Admin::instance();
			if (!is_admin()) {
				FCBJC_User::instance();
			}
		}
	}

	FCBJC_Plugin::init();
}

// Hook for register activation and deactivation plugin hooks
register_activation_hook(__FILE__, array('FCBJC_Plugin', 'activate'));
register_deactivation_hook(
	__FILE__,
	array('FCBJC_Plugin', 'deactivate')
);
