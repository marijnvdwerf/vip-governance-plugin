<?php

namespace WPCOMVIP\Governance;

defined( 'ABSPATH' ) || die();

use Exception;

class InitGovernance {
	public static function init() {
		// Assets for block editor UI
		add_action( 'enqueue_block_editor_assets', [ __CLASS__, 'load_settings' ] );

		// Assets for iframed block editor and editor UI
		add_action( 'enqueue_block_assets', [ __CLASS__, 'load_css' ] );
	}

	#region Block filters

	public static function load_settings() {
		if ( ! Settings::is_enabled() ) {
			return;
		}

		$asset_file = include WPCOMVIP_GOVERNANCE_ROOT_PLUGIN_DIR . '/build/index.asset.php';

		wp_register_script(
			'wpcomvip-governance',
			WPCOMVIP_GOVERNANCE_ROOT_PLUGIN_DIR . '/build/index.js',
			$asset_file['dependencies'],
			$asset_file['version'],
			true /* in_footer */
		);

		$governance_error          = false;
		$governance_rules_for_user = array();
		$nested_settings_and_css   = array();

		try {
			$parsed_governance_rules   = GovernanceUtilities::get_parsed_governance_rules();
			$governance_rules_for_user = GovernanceUtilities::get_rules_for_user( $parsed_governance_rules );
			$block_settings_for_user   = $governance_rules_for_user['blockSettings'];
			$nested_settings_and_css   = NestedGovernanceProcessing::get_nested_settings_and_css( $block_settings_for_user );
			BlockLocking::init( $governance_rules_for_user['allowedFeatures'] );
			Analytics::record_usage();
		} catch ( Exception $e ) {
			$governance_error = __( 'Governance rules could not be loaded.' );
		}

		wp_localize_script('wpcomvip-governance', 'VIP_GOVERNANCE', [
			'error'           => $governance_error,
			'governanceRules' => $governance_rules_for_user,
			'nestedSettings'  => isset( $nested_settings_and_css['settings'] ) ? $nested_settings_and_css['settings'] : array(),
			'urlSettingsPage' => menu_page_url( Settings::MENU_SLUG, /* display */ false ),
		]);

		wp_enqueue_script( 'wpcomvip-governance' );
	}

	public static function load_css() {
		if ( ! Settings::is_enabled() ) {
			return;
		}

		try {
			$parsed_governance_rules   = GovernanceUtilities::get_parsed_governance_rules();
			$governance_rules_for_user = GovernanceUtilities::get_rules_for_user( $parsed_governance_rules );
			$block_settings_for_user   = $governance_rules_for_user['blockSettings'];
			$nested_settings_and_css   = NestedGovernanceProcessing::get_nested_settings_and_css( $block_settings_for_user );
			wp_register_style(
				'wpcomvip-governance',
				WPCOMVIP_GOVERNANCE_ROOT_PLUGIN_DIR . '/css/vip-governance.css',
				/* dependencies */ array(),
				WPCOMVIP__GOVERNANCE__PLUGIN_VERSION
			);
			wp_add_inline_style( 'wpcomvip-governance', $nested_settings_and_css['css'] );
			wp_enqueue_style( 'wpcomvip-governance' );
			Analytics::record_usage();
		} catch ( Exception $e ) {
			// ToDo: Revamp the exception handling system
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( $e->getMessage() );
		}
	}

	#endregion Block filters
}

InitGovernance::init();
