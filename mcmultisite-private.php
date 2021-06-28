<?php
/**
 * Plugin Name:       MC Multisite Distributor
 * Description:       This Plugins Dedicated for Sync/Copy/Centralize products, post and pages. Add Centralize Tags and TAXONOMIES. (Private Use Only Contains Specific Compilation Plugins)
 * Version:           1.0.9
 * Author:            mcfaith9
 * Author URI:        https://github.com/mcfaith9
 * License:           GPLv2 or later
 * Text Domain:       MC Multisite Distributor
 * Domain Path:       /lang/
 * GitHub Plugin URI: https://github.com/mcfaith9/wp-plugin-btsbiot/tree/master
 *
 * @package MC Multisite Distributor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'DT_VERSION', '1.6.4' );
define( 'DT_PLUGIN_FILE', preg_replace( '#^.*plugins/(.*)$#i', '$1', __FILE__ ) );
define( 'DT_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

// Define a constant if we're network activated to allow plugin to respond accordingly.
$active_plugins = get_site_option( 'active_sitewide_plugins' );

if ( is_multisite() && isset( $active_plugins[ plugin_basename( __FILE__ ) ] ) ) {
	define( 'DT_IS_NETWORK', true );
} else {
	define( 'DT_IS_NETWORK', false );
}

/**
 * PSR-4 autoloading
 */
spl_autoload_register(
	function( $class ) {
			// Project-specific namespace prefix.
			$prefix = 'Distributor\\';
			// Base directory for the namespace prefix.
			$base_dir = __DIR__ . '/includes/classes/';
			// Does the class use the namespace prefix?
			$len = strlen( $prefix );
		if ( strncmp( $prefix, $class, $len ) !== 0 ) {
			return;
		}
			$relative_class = substr( $class, $len );
			$file           = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';
			// If the file exists, require it.
		if ( file_exists( $file ) ) {
			require $file;
		}
	}
);

/**
 * Require PHP version 5.6 - throw an error if the plugin is activated on an older version.
 */
register_activation_hook(
	__FILE__,
	function() {
		if ( version_compare( PHP_VERSION, '5.6.0', '<' ) ) {
			wp_die(
				esc_html__( 'Distributor requires PHP version 5.6.', 'distributor' ),
				esc_html__( 'Error Activating', 'distributor' )
			);
		}
	}
);

/**
 * Tell the world this site supports Distributor. We need this for external connections.
 */
add_action(
	'send_headers',
	function() {
		if ( ! headers_sent() ) {
			header( 'X-Distributor: yes' );
		}
	}
);

/**
 * Set Distributor header in all API responses.
 */
add_filter(
	'rest_post_dispatch',
	function( $response ) {
		$response->header( 'X-Distributor', 'yes' );

		return $response;
	}
);

\Distributor\Connections::factory();

// Include in case we have composer issues.
require_once __DIR__ . '/vendor/yahnis-elsts/plugin-update-checker/plugin-update-checker.php';

require_once __DIR__ . '/includes/utils.php';
require_once __DIR__ . '/includes/external-connection-cpt.php';
require_once __DIR__ . '/includes/push-ui.php';
require_once __DIR__ . '/includes/pull-ui.php';
require_once __DIR__ . '/includes/rest-api.php';
require_once __DIR__ . '/includes/subscriptions.php';
require_once __DIR__ . '/includes/syndicated-post-ui.php';
require_once __DIR__ . '/includes/distributed-post-ui.php';
require_once __DIR__ . '/includes/settings.php';
require_once __DIR__ . '/includes/template-tags.php';
require_once __DIR__ . '/includes/debug-info.php';

// Load required core plugin files
require_once __DIR__ .'/wpm-user-sync/core/wpmus-variables.php';
require_once __DIR__ .'/wpm-user-sync/core/wpmus-functions.php';

// Load required network-admin files
require_once __DIR__ .'/wpm-user-sync/network-admin/wpmus-network-sections.php';
require_once __DIR__ .'/wpm-user-sync/network-admin/wpmus-network-common.php';
require_once __DIR__ .'/wpm-user-sync/network-admin/wpmus-network-home.php';
require_once __DIR__ .'/wpm-user-sync/network-admin/wpmus-network-syncoptions.php';
require_once __DIR__ .'/wpm-user-sync/network-admin/wpmus-network-syncactions.php';

// Load required site-admin files
require_once __DIR__ .'/wpm-user-sync/site-admin/wpmus-site-sections.php';
require_once __DIR__ .'/wpm-user-sync/site-admin/wpmus-site-home.php';
require_once __DIR__ .'/wpm-user-sync/site-admin/wpmus-site-syncactions.php';

global $wpmus_newUserSync;
global $wpmus_newSiteSync;
global $wpmus_setUserRoleSync;


// Administrative triggers
register_activation_hook( __FILE__, 'wpmus_plugin_activate' );
add_action( 'init', 'wpmus_init' );
add_action( 'network_admin_menu', 'wpmus_networkmenu_items' );
add_action( 'admin_menu', 'wpmus_sitemenu_items' );
add_action( 'admin_init', 'wpmus_check_requirements' );

// Load tabs feature at network-level for home
add_action( 'wpmus_network_home_tabs', 'wpmus_network_home_welcome_tab', 1 );
add_action( 'wpmus_network_home_tabs', 'wpmus_network_home_concepts_tab', 2 );
add_action( 'wpmus_network_home_tabs', 'wpmus_network_home_about_tab', 3 );
add_action( 'wpmus_network_home_contents', 'wpmus_network_home_welcome_content' );
add_action( 'wpmus_network_home_contents', 'wpmus_network_home_concepts_content' );
add_action( 'wpmus_network_home_contents', 'wpmus_network_home_about_content' );

// Load tabs feature at site-level for home
add_action( 'wpmus_site_home_tabs', 'wpmus_site_home_welcome_tab', 1 );
add_action( 'wpmus_site_home_tabs', 'wpmus_site_home_concepts_tab', 2 );
add_action( 'wpmus_site_home_tabs', 'wpmus_site_home_about_tab', 3 );
add_action( 'wpmus_site_home_contents', 'wpmus_site_home_welcome_content' );
add_action( 'wpmus_site_home_contents', 'wpmus_site_home_concepts_content' );
add_action( 'wpmus_site_home_contents', 'wpmus_site_home_about_content' );

// Triggers fro actions
if ($wpmus_newSiteSync == 'yes') {
    add_action( 'wpmu_new_blog', 'wpmus_sync_newsite' );
}
if ($wpmus_newUserSync == 'yes') {
    add_action( 'wpmu_new_user', 'wpmus_sync_newuser' );
    add_action( 'wp_login', 'wpmus_maybesync_newuser', 10, 1 );
    add_action( 'social_connect_login', 'wpmus_maybesync_newuser', 10, 1 );
}
if ($wpmus_setUserRoleSync == 'yes') {
    add_action( 'set_user_role', 'wpmus_sync_newrole', 10, 2 );
}

// Save configuration actions
add_action( 'network_admin_edit_wpmusSaveGlobalConfig', 'wpmus_save_GlobalConfig' );
add_action( 'network_admin_edit_wpmusSyncNetworkFromScratch', 'wpmus_sync_NetworkFromScratch' );
add_action( 'admin_action_wpmusSyncNetworkSiteFromScratch', 'wpmus_sync_NetworkSiteFromScratch' );
add_action( 'admin_action_wpmusSyncSiteSiteFromScratch', 'wpmus_sync_SiteSiteFromScratch' );

// Admin notices
add_action( 'network_admin_notices', 'wpmus_notice_updated' );
add_action( 'admin_notices', 'wpmus_notice_updated' );

// Include application passwords.
add_action(
	'plugins_loaded',
	function() {
		if ( function_exists( 'wp_is_application_passwords_available' ) ) {
			if ( ! wp_is_application_passwords_available() ) {
				add_action(
					'admin_notices',
					function() {
						if ( get_current_screen()->id !== 'toplevel_page_distributor' ) {
							return;
						}
						?>
						<div class="notice notice-warning">
							<p>
								<?php
								echo wp_kses_post(
									sprintf(
										/* translators: %s is the URL to the guide to enable Application Passwords for non HTTPS sites. */
										__( 'Your site is not using HTTPS or is a local environment. Follow this <a href="%s">guide</a> to enable Application Passwords.', 'distributor' ),
										'https://github.com/10up/distributor#application-passwords-and-wordpress-56'
									)
								);
								?>
							</p>
						</div>
						<?php
					}
				);
			}
		} elseif ( ! class_exists( 'Application_Passwords' ) ) {
			require_once __DIR__ . '/vendor/georgestephanis/application-passwords/application-passwords.php';
		}
	}
);

// Override some styles for application passwords until we can get these changes upstream.
add_action(
	'admin_enqueue_scripts',
	function() {
		wp_enqueue_style( 'dt-admin-settings', plugins_url( '/dist/css/admin.min.css', __FILE__ ), array(), DT_VERSION );
	}
);

if ( class_exists( 'Puc_v4_Factory' ) ) {
	/**
	 * Enable updates if we have a valid license
	 */
	$valid_license = false;

	if ( ! DT_IS_NETWORK ) {
		$valid_license = \Distributor\Utils\get_settings()['valid_license'];
	} else {
		$valid_license = \Distributor\Utils\get_network_settings()['valid_license'];
	}

	if ( $valid_license ) {
		// @codingStandardsIgnoreStart
		$updateChecker = Puc_v4_Factory::buildUpdateChecker(
			'https://github.com/10up/distributor/',
			__FILE__,
			'distributor'
		);

		$updateChecker->getVcsApi()->enableReleaseAssets();

		$updateChecker->addResultFilter(
			function( $plugin_info, $http_response = null ) {
				$plugin_info->icons = array(
					'svg' => plugins_url( '/assets/img/icon.svg', __FILE__ ),
				);
				return $plugin_info;
			}
		);
		// @codingStandardsIgnoreEnd
	}
}

/**
 * Register connections
 */
add_action(
	'init',
	function() {
		\Distributor\Connections::factory()->register( '\Distributor\ExternalConnections\WordPressExternalConnection' );
		\Distributor\Connections::factory()->register( '\Distributor\ExternalConnections\WordPressDotcomExternalConnection' );
		if (
			/**
			 * Filter whether the network connection type is enabled. Enabled by default, return false to disable.
			 *
			 * @since 1.0.0
			 * @hook dt_network_site_connection_enabled
			 *
			 * @param {bool} true Whether the network connection should be enabled.
			 *
			 * @return {bool} Whether the network connection should be enabled.
			 */
			apply_filters( 'dt_network_site_connection_enabled', true )
		) {
			\Distributor\Connections::factory()->register( '\Distributor\InternalConnections\NetworkSiteConnection' );
		}
	},
	1
);

/**
 * We use setup functions to avoid unit testing WP_Mock strict mode errors.
 */
\Distributor\ExternalConnectionCPT\setup();
\Distributor\PushUI\setup();
\Distributor\PullUI\setup();
\Distributor\RestApi\setup();
\Distributor\Subscriptions\setup();
\Distributor\SyndicatedPostUI\setup();
\Distributor\DistributedPostUI\setup();
\Distributor\Settings\setup();
\Distributor\DebugInfo\setup();
