<?php
/**
 * The conflicting plugin watcher.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Admin;

use RankMath\Runner;
use RankMath\Traits\Hooker;
use RankMath\Helper as GlobalHelper;

defined( 'ABSPATH' ) || exit;

/**
 * Watcher class.
 */
class Watcher implements Runner {

	use Hooker;

	/**
	 * Register hooks.
	 */
	public function hooks() {
		$this->action( 'init', 'init' );
		$this->action( 'activated_plugin', 'check_activated_plugin' );
		$this->action( 'deactivated_plugin', 'check_deactivated_plugin' );
	}

	/**
	 * Set/Deactivate conflicting SEO or Sitemap plugins.
	 */
	public function init() {
		if ( isset( $_GET['rank_math_deactivate_seo_plugins'] ) ) {
			$this->deactivate_conflicting_plugins( 'seo' );
			return;
		}

		if ( isset( $_GET['rank_math_deactivate_sitemap_plugins'] ) ) {
			$this->deactivate_conflicting_plugins( 'sitemap' );
			return;
		}
	}

	/**
	 * Function to run when new plugin is activated.
	 */
	public static function check_activated_plugin() {
		$set     = [];
		$plugins = get_option( 'active_plugins', array() );

		foreach ( self::get_conflicting_plugins() as $plugin => $type ) {
			if ( ! isset( $set[ $type ] ) && in_array( $plugin, $plugins ) ) {
				$set[ $type ] = true;
				self::set_notification( $type );
			}
		}
	}

	/**
	 * Function to run when plugin is deactivated.
	 *
	 * @param string $plugin Deactivated plugin path.
	 */
	public function check_deactivated_plugin( $plugin ) {
		$plugins = self::get_conflicting_plugins();
		if ( ! isset( $plugins[ $plugin ] ) ) {
			return;
		}
		$this->remove_notification( $plugins[ $plugin ], $plugin );
	}

	/**
	 * Function to run when Module is enabled/disabled.
	 *
	 * @param string $module Module.
	 * @param string $state  Module state.
	 */
	public static function module_changed( $module, $state ) {
		if ( ! in_array( $module, [ 'sitemap', 'redirections', 'rich-snippet' ] ) ) {
			return;
		}

		if ( 'off' === $state ) {
			$type = 'sitemap' === $module ? 'sitemap' : 'seo';
			GlobalHelper::remove_notification( "conflicting_{$type}_plugins" );
		}

		self::check_activated_plugin();
	}

	/**
	 * Deactivate conflicting plugins.
	 *
	 * @param string $type Plugin type.
	 */
	private function deactivate_conflicting_plugins( $type ) {
		foreach ( self::get_conflicting_plugins() as $plugin => $plugin_type ) {
			if ( $type === $plugin_type && is_plugin_active( $plugin ) ) {
				deactivate_plugins( $plugin );
			}
		}

		wp_redirect( remove_query_arg( "rank_math_deactivate_{$type}_plugins" ) );
	}

	/**
	 * Function to set conflict plugin notification.
	 *
	 * @param string $type Plugin type.
	 */
	private static function set_notification( $type ) {
		$message = sprintf(
			/* translators: deactivation link */
			esc_html__( 'Please keep only one SEO plugin active, otherwise, you might lose your rankings and traffic. %s.', 'rank-math' ),
			'<a href="' . add_query_arg( 'rank_math_deactivate_seo_plugins', '1', admin_url( 'plugins.php' ) ) . '">Click here to Deactivate</a>'
		);

		if ( 'sitemap' === $type ) {
			$message = sprintf(
				/* translators: deactivation link */
				esc_html__( 'Please keep only one Sitemap plugin active, otherwise, you might lose your rankings and traffic. %s.', 'rank-math' ),
				'<a href="' . add_query_arg( 'rank_math_deactivate_sitemap_plugins', '1', admin_url( 'plugins.php' ) ) . '">Click here to Deactivate</a>'
			);
		}

		GlobalHelper::add_notification( $message, [
			'id'   => "conflicting_{$type}_plugins",
			'type' => 'error',
		] );
	}

	/**
	 * Function to remove conflict plugin notification.
	 *
	 * @param string $type   Plugin type.
	 * @param string $plugin Plugin name.
	 */
	private function remove_notification( $type, $plugin ) {
		foreach ( self::get_conflicting_plugins() as $file => $plugin_type ) {
			if ( $plugin !== $file && $type === $plugin_type && is_plugin_active( $file ) ) {
				return;
			}
		}

		GlobalHelper::remove_notification( "conflicting_{$type}_plugins" );
	}

	/**
	 * Function to get all conflicting plugins.
	 *
	 * @return array
	 */
	private static function get_conflicting_plugins() {
		$plugins = array(
			'wordpress-seo/wp-seo.php'                    => 'seo',
			'wordpress-seo-premium/wp-seo-premium.php'    => 'seo',
			'all-in-one-seo-pack/all_in_one_seo_pack.php' => 'seo',
		);

		if ( GlobalHelper::is_module_active( 'redirections' ) ) {
			$plugins['redirection/redirection.php'] = 'seo';
		}
		if ( GlobalHelper::is_module_active( 'sitemap' ) ) {
			$plugins['google-sitemap-generator/sitemap.php'] = 'sitemap';
			$plugins['xml-sitemap-feed/xml-sitemap.php']     = 'sitemap';
		}
		if ( GlobalHelper::is_module_active( 'rich-snippet' ) ) {
			$plugins['wp-schema-pro/wp-schema-pro.php']              = 'seo';
			$plugins['all-in-one-schemaorg-rich-snippets/index.php'] = 'seo';
		}
		return $plugins;
	}
}
