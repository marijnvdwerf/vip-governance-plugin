<?php
/**
 * Utilities for Block Governance.
 * 
 * @package vip-governance
 */

namespace WPCOMVIP\Governance;

defined( 'ABSPATH' ) || die();

use WP_Error;

/**
 * Utilities class that has helper functions for processing the governance rules.
 */
class GovernanceUtilities {

	/**
	 * Retrieve parsed governance rules from the private directory, or the plugin directory if not found.
	 *
	 * @return array|WP_Error
	 * 
	 * @access private
	 */
	public static function get_parsed_governance_rules() {
		$governance_rules_json = self::get_governance_rules_json();
		return RulesParser::parse( $governance_rules_json );
	}

	/**
	 * Get raw governance rules content from the private directory, or the plugin directory if not found.
	 *
	 * @return string|WP_Error
	 * 
	 * @access private
	 */
	public static function get_governance_rules_json() {
		$governance_file_path = WPCOM_VIP_PRIVATE_DIR . '/' . WPCOMVIP_GOVERNANCE_RULES_FILENAME;

		if ( ! file_exists( $governance_file_path ) ) {
			$governance_file_path = WPCOMVIP_GOVERNANCE_ROOT_PLUGIN_DIR . '/' . WPCOMVIP_GOVERNANCE_RULES_FILENAME;

			if ( ! file_exists( $governance_file_path ) ) {
				/* translators: %s: governance file name */
				return new WP_Error( 'governance-file-not-found', sprintf( __( 'Governance rules (%s) could not be found in private or plugin folders.', 'vip-governance' ), WPCOMVIP_GOVERNANCE_RULES_FILENAME ) );
			}
		}

		// phpcs:ignore WordPressVIPMinimum.Performance.FetchingRemoteData.FileGetContentsUnknown
		$governance_rules_json = file_get_contents( $governance_file_path );

		if ( false === $governance_rules_json ) {
			/* translators: %s: governance file name */
			return new WP_Error( 'governance-file-not-readable', sprintf( __( 'Governance rules (%s) could not be read from private folder.', 'vip-governance' ), WPCOMVIP_GOVERNANCE_RULES_FILENAME ) );
		}

		return $governance_rules_json;
	}

	/**
	 * Get the rules using the provided type.
	 * 
	 * The default rule is the base upon which the other rules are built. Currently, that's postType and role.
	 *
	 * @param array $governance_rules Governance rules, not filtered based on the user role.
	 * @param array $user_roles User roles for the current WP site.
	 * @param array $post_type Post type for the current post.
	 * 
	 * @return array Governance rules, filtered by the matching user role or post type.
	 * 
	 * @access private
	 */
	public static function get_rules_by_type( $governance_rules, $user_roles = [], $post_type = '' ) {
		if ( empty( $governance_rules ) ) {
			return array();
		}

		if ( empty( $user_roles ) ) {
			$current_user = wp_get_current_user();
			$user_roles   = $current_user->roles;
		}

		if ( empty( $post_type ) ) {
			$post_type = get_post_type();
		}

		$allowed_features = array();
		$allowed_blocks   = array();
		$block_settings   = array();

		// Because PHP doesn't allow passing this in directly.
		$type_to_rules_map = RulesParser::TYPE_TO_RULES_MAP;

		// Assumption is that it's been ordered by priority, so it will process those rules first followed by default last.
		foreach ( RulesParser::RULE_TYPES as $priority ) {
			// look up the rule in $governance_rules where the field type matches priority.
			$governance_rules_for_priority = array_filter( $governance_rules, function( $rule ) use ( $priority, $user_roles, $post_type, $type_to_rules_map ) {
				if ( isset( $rule['type'] ) && $priority === $rule['type'] && ( 'default' === $priority || isset( $rule[ $type_to_rules_map[ $priority ] ] ) ) ) {
					if ( 'default' === $priority ) {
						return true;
					} elseif ( 'role' === $priority ) {
						return array_intersect( $user_roles, $rule['roles'] );
					} elseif ( 'postType' === $priority ) {
						return in_array( $post_type, $rule['postTypes'], true );
					}
				}
				
				return false;
			} );

			if ( ! empty( $governance_rules_for_priority ) ) {
				$governance_rules_for_priority = array_values( $governance_rules_for_priority );
				$allowed_blocks                = isset( $governance_rules_for_priority[0]['allowedBlocks'] ) ? ( 'default' === $priority ? [ ...$allowed_blocks, ...$governance_rules_for_priority[0]['allowedBlocks'] ] : $governance_rules_for_priority[0]['allowedBlocks'] ) : $allowed_blocks;
				$block_settings                = isset( $governance_rules_for_priority[0]['blockSettings'] ) ? ( 'default' === $priority ? array_merge_recursive( $block_settings, $governance_rules_for_priority[0]['blockSettings'] ) : $governance_rules_for_priority[0]['blockSettings'] ) : $block_settings;
				$allowed_features              = isset( $governance_rules_for_priority[0]['allowedFeatures'] ) ? ( 'default' === $priority ? [ ...$allowed_features, ...$governance_rules_for_priority[0]['allowedFeatures'] ] : $governance_rules_for_priority[0]['allowedFeatures'] ) : $allowed_features;
			}
		}

		// return array of allowed_blocks and block_settings.
		return array(
			'allowedBlocks'   => $allowed_blocks,
			'blockSettings'   => $block_settings,
			'allowedFeatures' => $allowed_features,
		);
	}

}
