<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly




function church_admin_install_premium_plugin() {
	if(file_exists(plugin_dir_path(dirname(__FILE__)) .'church-admin/uninstall.php')){
        church_admin_premium_debug('**** Making delete church admin free version safe ****');
        unlink(plugin_dir_path(dirname(__FILE__)) .'church-admin/uninstall.php');
    }
	$plugins = array(
		
		array(
			'name'   => esc_html__( 'Church Admin Premium', 'church-admin' ),
			'slug'   => 'church-admin-premium',
			'plugin' => 'church-admin-premium/index.php',
			'source' => 'https://church-admin-premium.s3.eu-north-1.amazonaws.com/church-admin-premium.zip',
		),
	);
	foreach ( $plugins as $plugin_info ) {
		$result = church_admin_install_plugin( $plugin_info );

		if ( $result['success'] ) {
			echo '<div class="notice notice-success"><h2>'.__('Success','church-admin').'</h2><p>' . esc_html($result['message']).'</p>';
            echo'<p>'.esc_html(__('You have  installed & activated the premium version. Please deactivate this free version of "Church Admin" to use the premium version.','church-admin') ).'</p>';
            echo'<p><a class="button-primary" href="'.esc_url(wp_nonce_url(admin_url().'plugins.php?action=deactivate&plugin=church-admin/church-admin.php','deactivate-plugin_church-admin/church-admin.php')).'">'.esc_html(__('Deactivate now','church-admin')).'</a></p>';    
            echo '</div>';
            
           
		} else {
			echo '<div class="notice notice-warning"><h2>'.__('Error','church-admin').'</h2><p>' . esc_html($result['message']).'</p></div>';
		}
	}
	exit;
}



/**
 * Programmatically install and activate WordPress plugins.
 * Supports installation of plugins from the WordPress plugin repository and external sources via ZIP files.
 */

if ( ! function_exists( 'church_admin_is_plugin_active' ) ) {
	/**
	 * Checks if a given plugin is active.
	 *
	 * @since 1.0.0
	 *
	 * @param string $plugin Plugin folder with main file e.g., my-plugin/my-plugin.php.
	 * @return bool True if the plugin is active, otherwise false.
	 */
	function church_admin_is_plugin_active( $plugin ) {
		return is_plugin_active_for_network( $plugin ) || is_plugin_active( $plugin );
	}
}

if ( ! function_exists( 'church_admin_install_plugin' ) ) {
	/**
	 * Install and activate a WordPress plugin.
	 *
	 * @param array $plugin_info Plugin information array containing 'name', 'slug', 'plugin', and 'source'(optional).
	 * @return array Associative array with 'success' boolean and 'message' string.
	 */
	function church_admin_install_plugin( $plugin_info ) {
		$name   = sanitize_text_field( $plugin_info['name'] );
		$slug   = sanitize_key( $plugin_info['slug'] );
		$plugin = sanitize_text_field( $plugin_info['plugin'] );
		$source = isset( $plugin_info['source'] ) ? esc_url_raw( $plugin_info['source'] ) : '';

		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		if ( church_admin_is_plugin_active( $plugin ) ) {
			// Plugin is already active.
			return array(
				'success' => true,
				'message' => sprintf(
					/* translators: %s is the plugin name */
					esc_html__( 'Plugin "%s" is already active.', 'church-admin' ),
					$name
				),
			);
		}

		// The plugin is installed, but not active.
		if ( file_exists( WP_PLUGIN_DIR . '/' . $slug ) ) {
			$plugin_data          = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );

			if ( church_admin_is_plugin_active( $plugin ) ) {
				// Plugin is already active.
				return array(
					'success' => true,
					'message' => sprintf(
						/* translators: %s is the plugin name */
						esc_html__( 'Plugin "%s" is already active.', 'church-admin' ),
						$name
					),
				);
			}
			if ( current_user_can( 'activate_plugin', $plugin ) ) {
				$result = activate_plugin( $plugin );

				if ( is_wp_error( $result ) ) {
					// Plugin is already active.
					return array(
						'success' => false,
						'message' => sprintf(
							/* translators: %1$s is the plugin name, %2$s is error message */
							esc_html__( 'Error activating plugin "%1$s": %2$s', 'church-admin' ),
							$name,
							$result->get_error_message()
						),
					);
				}

				return array(
					'success' => true,
					'message' => sprintf(
						/* translators: %s is the plugin name.*/
						esc_html__( 'Plugin "%s" activated successfully.', 'church-admin' ),
						$name,
					),
				);
			} else {
				return array(
					'success' => false,
					'message' => sprintf(
						/* translators: %s is the plugin name.*/
						esc_html__( 'You don\'t have permission to activate the plugin "%s".', 'church-admin' ),
						$name,
					),
				);
			}
		}

		if ( $source ) {
			// Install plugin from external source.
			$download_link = $source;
		} else {
			// Install plugin from WordPress repository.
			$api = plugins_api(
				'plugin_information',
				array(
					'slug'   => $slug,
					'fields' => array( 'sections' => false ),
				)
			);

			if ( is_wp_error( $api ) ) {
				return array(
					'success' => false,
					'message' => sprintf(
						/* translators: %1$s is the plugin name, %2$s is error message */
						esc_html__( 'Error retrieving information for plugin "%1$s": %2$s', 'church-admin' ),
						$name,
						$result->get_error_message()
					),
				);
			}

			$download_link        = $api->download_link;
		}

		$skin     = new WP_Ajax_Upgrader_Skin();
		$upgrader = new Plugin_Upgrader( $skin );
		$result   = $upgrader->install( $download_link );

		if ( is_wp_error( $result ) ) {
			return array(
				'success' => false,
				'message' => sprintf(
					/* translators: %1$s is the plugin name, %2$s is error message */
					esc_html__( 'Error installing plugin "%1$s": %2$s', 'church-admin' ),
					$name,
					$result->get_error_message()
				),
			);
		} elseif ( is_wp_error( $skin->result ) ) {
			return array(
				'success' => false,
				'message' => sprintf(
					/* translators: %1$s is the plugin name, %2$s is error message */
					esc_html__( 'Error installing plugin "%1$s": %2$s', 'church-admin' ),
					$name,
					$skin->result->get_error_message()
				),
			);
		} elseif ( $skin->get_errors()->get_error_code() ) {
			return array(
				'success' => false,
				'message' => sprintf(
					/* translators: %1$s is the plugin name, %2$s is error message */
					esc_html__( 'Error installing plugin "%1$s": %2$s', 'church-admin' ),
					$name,
					$skin->get_error_messages()
				),
			);
		} elseif ( is_null( $result ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
			global $wp_filesystem;

			$error_message = __( 'Unable to connect to the filesystem. Please confirm your credentials.', 'church-admin' );

			if ( $wp_filesystem instanceof WP_Filesystem_Base && is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->get_error_code() ) {
				$error_message = esc_html( $wp_filesystem->errors->get_error_message() );
			}

			return array(
				'success' => false,
				'message' => sprintf(
					/* translators: %1$s is the plugin name, %2$s is error message */
					esc_html__( 'Error installing plugin "%1$s": %2$s', 'church-admin' ),
					$name,
					$error_message
				),
			);
		}

		if ( church_admin_is_plugin_active( $plugin ) ) {
			// Plugin is already active.
			return array(
				'success' => true,
				'message' => sprintf(
					/* translators: %s is the plugin name.*/
					esc_html__( 'Plugin "%s" activated successfully.', 'church-admin' ),
					$name,
				),
			);
		}

		if ( current_user_can( 'activate_plugin', $plugin ) ) {
			$result = activate_plugin( $plugin );

			if ( is_wp_error( $result ) ) {
				return array(
					'success' => false,
					'message' => sprintf(
					/* translators: %1$s is the plugin name, %2$s is error message */
						esc_html__( 'Error activating plugin "%1$s": %2$s', 'church-admin' ),
						$name,
						$result->get_error_message()
					),
				);
			}
		} else {
			return array(
				'success' => false,
				'message' => sprintf(
					/* translators: %s is the plugin name.*/
					esc_html__( 'You don\'t have permission to activate the plugin "%s".', 'church-admin' ),
					$name,
				),
			);
		}

		return array(
			'success' => true,
			'message' => sprintf(
				/* translators: %s is the plugin name.*/
				esc_html__( 'Plugin "%s" installed and activated successfully.', 'church-admin' ),
				$name,
			),
		);
	}
}