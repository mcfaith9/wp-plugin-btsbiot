<?php
/**
 * Admin settings screen
 *
 * @package  distributor
 */

namespace Distributor\Settings;

use Distributor\Utils;

/**
 * Setup settings
 *
 * @since 1.0
 */
function setup() {
	add_action(
		'plugins_loaded',
		function() {
			add_action( 'admin_menu', __NAMESPACE__ . '\admin_menu', 20 );

			if ( DT_IS_NETWORK ) {
				add_action( 'network_admin_menu', __NAMESPACE__ . '\network_admin_menu' );
				add_action( 'admin_init', __NAMESPACE__ . '\handle_network_settings' );
			}

			add_action( 'admin_init', __NAMESPACE__ . '\setup_fields_sections' );
			add_action( 'admin_init', __NAMESPACE__ . '\register_settings' );
			add_action( 'admin_notices', __NAMESPACE__ . '\maybe_notice' );
			add_action( 'network_admin_notices', __NAMESPACE__ . '\maybe_notice' );
			add_action( 'after_plugin_row', __NAMESPACE__ . '\update_notice', 10, 3 );
			add_action( 'admin_print_styles', __NAMESPACE__ . '\plugin_update_styles' );
			add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\admin_enqueue_scripts' );
		}
	);
}

/**
 * Properly style plugin update row for Distributor
 *
 * @since 1.2
 */
function plugin_update_styles() {
	global $pagenow;

	if ( 'plugins.php' !== $pagenow ) {
		return;
	}

	if ( DT_IS_NETWORK ) {
		$settings = Utils\get_network_settings();
	} else {
		$settings = Utils\get_settings();
	}

	if ( true === $settings['valid_license'] ) {
		return;
	}
	?>
	<style type="text/css">
		#wpbody tr[data-slug="distributor"] td,
		#wpbody tr[data-slug="distributor"] th {
			box-shadow: none;
			border-bottom: 0;
		}

		#distributor-update .update-message {
			margin-top: 0;
		}
	</style>
	<?php
}

/**
 * Under plugin row update notice
 *
 * @param  string $plugin_file Plugin file path.
 * @param  string $plugin_data Plugin data.
 * @param  string $status Plugin status.
 * @since  1.2
 */
function update_notice( $plugin_file, $plugin_data, $status ) {
	$enable = 0;
	if($enable == 1){
		if ( DT_PLUGIN_FILE !== $plugin_file ) {
			return;
		}

		if ( DT_IS_NETWORK ) {
			$settings   = Utils\get_network_settings();
			$notice_url = network_admin_url( 'admin.php?page=distributor-settings' );
		} else {
			$notice_url = admin_url( 'admin.php?page=distributor-settings' );
			$settings   = Utils\get_settings();
		}

		if ( true === $settings['valid_license'] ) {
			return;
		}

		if ( is_network_admin() ) {
			$active = DT_IS_NETWORK;
		} else {
			$active = true;
		}
		?>

		<tr class="plugin-update-tr <?php if ( $active ) : ?>active<?php endif; ?>" id="distributor-update" >
			<td colspan="3" class="plugin-update colspanchange">
				<div class="update-message notice inline notice-warning notice-alt">
					<p>
						<?php /* translators: %s: distributor notice url */ ?>
						<?php echo wp_kses_post( sprintf( __( '<a href="%s">Register</a> for a free Distributor key to receive updates.', 'distributor' ), esc_url( $notice_url ) ) ); ?>
					</p>
				</div>
			</td>
		</tr>
		<?php
	}
}

/**
 * Maybe show license or dev version notice
 *
 * @since 1.2
 */
function maybe_notice() {}

/**
 * Enqueue admin scripts/styles for settings
 *
 * @param  string $hook WP hook.
 * @since  1.2
 */
function admin_enqueue_scripts( $hook ) {
	if ( ! empty( $_GET['page'] ) && 'distributor-settings' === $_GET['page'] ) { // @codingStandardsIgnoreLine Nonce not required.
		wp_enqueue_style( 'dt-admin-settings', plugins_url( '/dist/css/admin-settings.min.css', __DIR__ ), array(), DT_VERSION );
	}
}

/**
 * Register setting fields and sections
 *
 * @since  1.0
 */
function setup_fields_sections() {
	add_settings_section( 'dt-section-1', '', '', 'distributor' );

	add_settings_field( 'override_author_byline', esc_html__( 'Override Author Byline', 'distributor' ), __NAMESPACE__ . '\override_author_byline_callback', 'distributor', 'dt-section-1' );

	add_settings_field( 'media_handling', esc_html__( 'Media Handling', 'distributor' ), __NAMESPACE__ . '\media_handling_callback', 'distributor', 'dt-section-1' );

	if ( false === DT_IS_NETWORK ) {
		add_settings_field( 'registation_key', esc_html__( 'Registration Key', 'distributor' ), __NAMESPACE__ . '\license_key_callback', 'distributor', 'dt-section-1' );
	}
}

/**
 * Output replace distributed author settings field
 *
 * @since 1.0
 */
function override_author_byline_callback() {

	$settings = Utils\get_settings();

	$value = true;
	if ( isset( $settings['override_author_byline'] ) && false === $settings['override_author_byline'] ) {
		$value = false;
	}

	?>
	<label><input <?php checked( $value, true ); ?> type="checkbox" value="1" name="dt_settings[override_author_byline]">
	<?php esc_html_e( 'For linked distributed posts, replace the author name and link with the original site name and link.', 'distributor' ); ?>
	</label>
	<?php
}

/**
 * Output license key field and check current key
 *
 * @since 1.2
 */
function license_key_callback() {

	$settings = Utils\get_settings();

	$license_key = ( ! empty( $settings['license_key'] ) ) ? $settings['license_key'] : '';
	$email       = ( ! empty( $settings['email'] ) ) ? $settings['email'] : '';
	?>

	<?php if ( true === $settings['valid_license'] ) : ?>
		<div class="registered">
			<?php /* translators: %s is registered email. */ ?>
			<p><?php echo esc_html( sprintf( __( 'Distributor is registered to %s.', 'distributor' ), $email ) ); ?></p>
			<a href="#" onclick="this.parentNode.remove(); return false;"><?php esc_html_e( 'Update registration', 'distributor' ); ?></a>
		</div>
	<?php endif; ?>

	<div class="license-wrap <?php if ( true === $settings['valid_license'] ) : ?>valid<?php elseif ( false === $settings['valid_license'] ) : ?>invalid<?php endif; ?>">
		<label class="screen-reader-text" for="dt_settings_email"><?php esc_html_e( 'Email', 'distributor' ); ?></label>
		<input name="dt_settings[email]" type="email" placeholder="<?php esc_html_e( 'Email', 'distributor' ); ?>" value="<?php echo esc_attr( $email ); ?>" id="dt_settings_email">

		<label class="screen-reader-text" for="dt_settings_license_key"><?php esc_html_e( 'Registration Key', 'distributor' ); ?></label>
		<input name="dt_settings[license_key]" type="text" placeholder="<?php esc_html_e( 'Registration Key', 'distributor' ); ?>" value="<?php echo esc_attr( $license_key ); ?>" id="dt_settings_license_key">
	</div>

	<?php if ( true !== $settings['valid_license'] ) : ?>
		<p class="description">
			<?php echo wp_kses_post( __( 'Registration is 100% free and provides update notifications and upgrades inside the dashboard. <a href="https://distributorplugin.com/#cta">Register for your key</a>.', 'distributor' ) ); ?>
		</p>
		<?php
	endif;
}

/**
 * Output media handling options.
 *
 * @since 1.3.0
 */
function media_handling_callback() {
	$settings = Utils\get_settings();
	?>

	<ul class="media-handling">
		<li>
			<label><input <?php checked( $settings['media_handling'], 'featured' ); ?> type="radio" value="featured" name="dt_settings[media_handling]">
			<?php esc_html_e( 'Process the featured image only (default).', 'distributor' ); ?>
			</label>
		</li>
		<li>
			<label><input <?php checked( $settings['media_handling'], 'attached' ); ?> type="radio" value="attached" name="dt_settings[media_handling]">
			<?php esc_html_e( 'Process the featured image and any attached images.', 'distributor' ); ?>
			</label>
		</li>
	</ul>

	<?php
}

/**
 * Register settings for options table
 *
 * @since  1.0
 */
function register_settings() {
	register_setting( 'dt_settings', 'dt_settings', __NAMESPACE__ . '\sanitize_settings' );
}

/**
 * Output setting menu option
 *
 * @since  1.0
 */
function admin_menu() {
	add_submenu_page( 'distributor', esc_html__( 'Settings', 'distributor' ), esc_html__( 'Settings', 'distributor' ), 'manage_options', 'distributor-settings', __NAMESPACE__ . '\settings_screen' );
}

/**
 * Output network setting menu option
 *
 * @since  1.2
 */
function network_admin_menu() {
	add_menu_page( 'MCMultisite', 'MCMultisite', 'manage_options', 'distributor-settings', __NAMESPACE__ . '\network_settings_screen', 'data:image/png;base64,AAABAAEAFBQAAAEAIAC4BgAAFgAAACgAAAAUAAAAKAAAAAEAIAAAAAAAQAYAAAAAAAAAAAAAAAAAAAAAAADrw3j/7MV6/62KTP+IUgv/ZDwR/1QzEf9lPRT/Zj4U/2Y+FP9mPhT/Zj4U/2Y+FP9mPhT/WTYR/1o2Ev+ATA3/jloS/8ShYf/uxXr/68N4/+vDeP/sxXr/rYpM/4VRC/9rQRP/VzUR/1s3Ev9nPhT/Zj4U/2Y+FP9mPhT/Zz8U/2I7E/9VMxD/YjsU/4FNDf+OWhL/w6Bh/+7Fef/rw3j/68N4/+3Fev+ui03/hVEM/2xCE/9iPBT/VjQR/1w4Ev9lPRP/YzsR/2Q8Ev9hOxL/VjQR/1s3Ev9kPRX/g04M/41aEv/FomP/7sZ6/+vDeP/rw3j/7cV5/7+cXf+BUxT/fUsN/3dID/9ZNg//SSwO/1s8HP9URTv/V0Aq/00wE/9GKw3/aT8P/3lJD/+MUgf/imAh/9aybv/txHn/68N4/+vDeP/sw3j/4710/6aDSP98UhT/bUEG/0krC/9NPzT/W2F2/2Nzmf9fa4v/Wlxp/0UwG/9aNQj/d0gF/4BXGv+0klb/6sJ4/+vDeP/rw3j/68N4/+vDeP/sxHj/58B3/5FxO/9aNQn/RzAY/1pfaf9sgqf/X3CR/26Cp/9vhKX/UEtJ/0kuEf9eOgz/pIZO/+rEef/rw3j/68N4/+vDeP/rw3j/68N4/+vDeP/txXr/rYtQ/1hELP9zgJL/la/W/4ui0P9vgar/hZrI/5ex4P+Sq8//Zmx0/19GJP/GomH/7cV6/+vDeP/rw3j/68N4/+vDeP/rw3j/7MN4/+jAdf+7n2j/hJOj/5676P99kbv/b4Cp/3WIsv91h7H/doiy/5Oq2v+buOD/hIeE/8+sav/qwnf/7MR4/+vDeP/rw3j/68N4/+zEeP/PrW//jYJr/3l/gf+fvOL/mrPl/4WZyP9vgKf/b4Co/3iLtf+Ak8D/kabZ/5656/+TrM3/d3Zw/6GPa//gunP/7MR4/+vDeP/sxHj/4Lpz/310Yv9RYXr/fZa4/6G97P+XreL/mK/j/3uOuv9zhbD/kqjc/5et4f+XreH/mLDk/5255f9ofZr/U15w/6OOZf/rwnf/68N4/+3FeP/Epm7/bniN/1dohf+Oqs7/o7/w/5it3/+ar+D/kKbX/4KXx/+Uqt7/m7Di/5it3v+aseL/pcLu/3WMqv9bbI7/hIKA/924cf/sxHn/7sV5/7Wbaf9kcYv/UWF6/4qlx/+xxuL/c3mI/4+Wp/+muOL/lKvg/5mv4/+mss//bXKA/5qktf+wzO//bYKf/1NjgP9zdnv/1rJv/+3Eef/txHn/z65v/2xpX/8iJi3/an6a/4eZsv9CR1P/Y2t//5Km0/+XruL/ma/j/32Mrf9CR1b/Z299/5mz0P9LWm3/JCkw/4Z6Yf/jvHT/7MR4/+vDeP/sxXn/qo9a/xoYE/9JV2z/dYuw/0hVcf9HU3D/e4+7/5iu4v+SqNr/YXCV/0ZSbv9ZaIr/gpvA/zQ/Tf8cGBP/r5Jc/+7Fef/rw3j/68N4/+zEeP+VflL/ERAP/yMpM/95jrP/cIGo/3uNt/+Rptn/ma/j/5et4f+EmMb/b3+m/3iLs/9rf53/DxIX/x8eG/+ljFv/7cV5/+vDeP/rw3j/6cF3/3dlRP8GBgb/ERQY/3aLrv+No9P/hZnI/4KUwf9wgKf/aXic/3yOuf+WrOD/nbfp/01ccP8GBgf/Kioo/5+HWv/txHj/68N4/+vDeP/pwnf/dGJA/wcHCP8EBQb/HCEq/yQqNv8dIiz/HCAq/xYZIf8SFBv/GR0m/1Vif/+PqND/MTpH/w0NDP9CQT7/p45d/+3Fef/rw3j/68N4/+rCd/+DcEz/MTEx/xQUFP8BAAD/DQ0M/w8ODv8FBQX/AAAA/wYGBv8YFxf/JCUp/0ZSY/8bICf/Hx8f/0hHRP+kiln/7sV5/+vDeP/rw3j/7cR5/6uQX/9SUE3/Nzc3/xkZGf8VFRX/FRUV/w0NDf8DAwP/DAwM/ywsLP9AQED/Kisr/xAQEf8sLC3/XVZJ/8emaP/txXn/68N4/+vDeP/txHn/1LFu/2tgTf9RUlP/Tk5O/zU1Nf8cHBz/CwsL/wICAv8CAgL/CQkJ/xYWFv8sKyv/JiYm/zs3Mf+fh1n/6cF2/+vDeP/rw3j/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=' );
}

/**
 * Output setting screen
 *
 * @since  1.0
 */
function settings_screen() {
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'MCMultisite Distributor Settings', 'distributor' ); ?></h1>

		<form action="options.php" method="post">

		<?php settings_fields( 'dt_settings' ); ?>
		<?php do_settings_sections( 'distributor' ); ?>

		<?php submit_button(); ?>

		</form>
	</div>
	<?php
}

/**
 * Output network settings
 *
 * @since  1.2
 */
function network_settings_screen() {
	$settings = Utils\get_network_settings();

	$network_taxonomy = ( ! empty( $settings['network_taxonomy'] ) ) ? $settings['network_taxonomy'] : '';
	?>	

	<div class="wrap">
		<h1><?php esc_html_e( 'MCMultisite Distributor Global Settings', 'distributor' ); ?></h1>

		<form action="" method="post">
		<?php settings_fields( 'dt-settings' ); ?>
		<?php settings_errors(); ?>

		<input type="hidden" name="dt_network_settings" value="1">

		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><?php esc_html_e( 'Enable Multisite use one taxonomy db table.', 'distributor' ); ?></th>					
				</tr>
				<tr>
					<td>
						<ul class="media-handling">
							<li>
								<label><input <?php checked( $settings['network_taxonomy'], 'enable' ); ?> type="radio" value="enable" name="dt_settings[network_taxonomy]">
								<?php esc_html_e( 'Enable (default).', 'distributor' ); ?>
								</label>
							</li>
							<li>
								<label><input <?php checked( $settings['network_taxonomy'], 'disable' ); ?> type="radio" value="disable" name="dt_settings[network_taxonomy]">
								<?php esc_html_e( 'Disable', 'distributor' ); ?>
								</label>
							</li>
						</ul>
					</td>
				</tr>
			</tbody>
		</table>

		<?php submit_button(); ?>

		</form>		
	</div>	
	<?php
}

/**
 * Save network settings
 *
 * @since 1.2
 */
function handle_network_settings() {
	if ( empty( $_POST['dt_network_settings'] ) ) {
		return;
	}

	if ( ! check_admin_referer( 'dt-settings-options' ) ) {
		die( esc_html__( 'Security error!', 'distributor' ) );
	}

	$new_settings = Utils\get_network_settings();

	// if ( isset( $_POST['dt_settings']['license_key'] ) ) {
	// 	$new_settings['license_key'] = sanitize_text_field( $_POST['dt_settings']['license_key'] );
	// }

	// if ( isset( $_POST['dt_settings']['email'] ) ) {
	// 	$new_settings['email'] = sanitize_text_field( $_POST['dt_settings']['email'] );
	// }

	if ( ! isset( $settings['network_taxonomy'] ) || ! in_array( $settings['network_taxonomy'], array( 'enable', 'disable' ), true ) ) {
		$new_settings['network_taxonomy'] = 'enable';
	} else {
		$new_settings['network_taxonomy'] = sanitize_text_field( $settings['network_taxonomy'] );
	}

	if ( ! empty( $_POST['dt_settings']['email'] ) && ! empty( $_POST['dt_settings']['license_key'] ) ) {
		$new_settings['valid_license'] = (bool) Utils\check_license_key( $_POST['dt_settings']['email'], $_POST['dt_settings']['license_key'] );
	} else {
		$new_settings['valid_license'] = null;
	}

	update_site_option( 'dt_settings', $new_settings );
}


/**
 * Sanitize settings for DB
 *
 * @param  array $settings Array of settings.
 * @since  1.0
 */
function sanitize_settings( $settings ) {
	$new_settings = Utils\get_settings();

	if ( ! isset( $settings['override_author_byline'] ) ) {
		$new_settings['override_author_byline'] = false;
	} else {
		$new_settings['override_author_byline'] = true;
	}

	if ( ! isset( $settings['media_handling'] ) || ! in_array( $settings['media_handling'], array( 'featured', 'attached' ), true ) ) {
		$new_settings['media_handling'] = 'featured';
	} else {
		$new_settings['media_handling'] = sanitize_text_field( $settings['media_handling'] );
	}

	if ( isset( $settings['license_key'] ) ) {
		$new_settings['license_key'] = sanitize_text_field( $settings['license_key'] );
	}

	if ( isset( $settings['email'] ) ) {
		$new_settings['email'] = sanitize_text_field( $settings['email'] );
	}

	if ( ! DT_IS_NETWORK && ! empty( $settings['email'] ) && ! empty( $settings['license_key'] ) ) {
		$new_settings['valid_license'] = (bool) Utils\check_license_key( $settings['email'], $settings['license_key'] );
	} else {
		$new_settings['valid_license'] = null;
	}

	return $new_settings;
}
