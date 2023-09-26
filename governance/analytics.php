<?php
/**
 * Analytics for Block Governance.
 * 
 * @package vip-governance
 */

namespace WPCOMVIP\Governance;

defined( 'ABSPATH' ) || die();

/**
 * Analytics Class that will be used to send data to the WP Pixel.
 */
class Analytics {
	/**
	 * Array of analytics to send to the WP Pixel.
	 * 
	 * @var array
	 */
	private static $analytics_to_send = [];

	/**
	 * Initialize the Analytics class.
	 * 
	 * @access private
	 */
	public static function init() {
		add_action( 'shutdown', [ __CLASS__, 'send_analytics' ] );
	}

	/**
	 * Record the usage of the plugin, for VIP sites only. For non-VIP sites, this is a no-op.
	 * 
	 * @return void
	 */
	public static function record_usage() {
		// Record usage on WPVIP sites only.
		if ( ! self::is_wpvip_site() ) {
			return;
		}

		self::$analytics_to_send[ WPCOMVIP__GOVERNANCE__STAT_NAME___USAGE ] = constant( 'FILES_CLIENT_SITE_ID' );
	}

	/**
	 * Record an error for VIP sites only. For non-VIP sites, this is a no-op.
	 *
	 * @return void
	 */
	public static function record_error() {
		if ( self::is_wpvip_site() ) {
			// Record error data from WPVIP for follow-up.
			self::$analytics_to_send[ WPCOMVIP__GOVERNANCE__STAT_NAME___ERROR ] = constant( 'FILES_CLIENT_SITE_ID' );
		}
	}

	/**
	 * Send the analytics, if present. If an error is present, then usage analytics are not sent. 
	 * 
	 * @return void
	 */
	public static function send_analytics() {
		if ( empty( self::$analytics_to_send ) ) {
			return;
		}

		$has_usage_analytics = isset( self::$analytics_to_send[ WPCOMVIP__GOVERNANCE__STAT_NAME___USAGE ] );
		$has_error_analytics = isset( self::$analytics_to_send[ WPCOMVIP__GOVERNANCE__STAT_NAME___ERROR ] );

		if ( $has_usage_analytics && $has_error_analytics ) {
			// Do not send usage analytics when errors are present.
			unset( self::$analytics_to_send[ WPCOMVIP__GOVERNANCE__STAT_NAME___USAGE ] );
		}

		self::send_pixel( self::$analytics_to_send );
	}

	/**
	 * Send the stats to the WP Pixel.
	 *
	 * @param array $stats Stats to be sent.
	 * @return void
	 */
	private static function send_pixel( $stats ) {
		$query_args = [
			'v' => 'wpcom-no-pv',
		];

		foreach ( $stats as $name => $group ) {
			$query_param = rawurlencode( 'x_' . $name );
			$query_value = rawurlencode( $group );

			$query_args[ $query_param ] = $query_value;
		}

		$pixel = add_query_arg( $query_args, 'http://pixel.wp.com/b.gif' );

		// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_remote_get_wp_remote_get
		wp_remote_get( $pixel, array(
			'blocking' => false,
			'timeout'  => 1,
		) );
	}

	/**
	 * Check if the site is a WPVIP site.
	 * 
	 * @return bool true If it is a WPVIP site, false otherwise
	 */
	private static function is_wpvip_site() {
		return defined( 'WPCOM_IS_VIP_ENV' ) && constant( 'WPCOM_IS_VIP_ENV' ) === true
			&& defined( 'WPCOM_SANDBOXED' ) && constant( 'WPCOM_SANDBOXED' ) === false
			&& defined( 'FILES_CLIENT_SITE_ID' );
	}
}

Analytics::init();
