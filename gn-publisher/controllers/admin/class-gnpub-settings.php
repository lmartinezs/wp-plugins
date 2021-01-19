<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This controller handles settings related actions.
 * 
 * @since 1.0.0
 */
class GNPUB_Settings {

	/**
	 * @var GNPUB_Notices
	 */
	protected $notices;

	public function __construct( $notices ) {
		$this->notices = $notices;

		add_action( 'admin_init', array( $this, 'save_settings' ) );
	}

	/**
	 * Save the settings form when it has been submitted.
	 * 
	 * @since 1.0.0
	 */
	public function save_settings() {
		if ( isset( $_POST['save_gnpub_settings'] ) && current_user_can( 'manage_options' ) ) {
			$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_key( $_POST['_wpnonce'] ) : null;
			
			if ( ! wp_verify_nonce( $nonce, 'save_gnpub_settings' ) ) {
				$this->notices->add_notice( __( 'GN Publisher settings were not saved because the form has expired. Try again.', 'gn-publisher' ), 'error' );
				return;
			}

			if ( isset( $_POST['gnpub_include_featured_image'] ) ) {
				update_option( 'gnpub_include_featured_image', true );
			} else {
				update_option( 'gnpub_include_featured_image', false );
			}

			if ( isset( $_POST['gnpub_is_default_feed'] ) ) {
				update_option( 'gnpub_is_default_feed', true );
			} else {
				update_option( 'gnpub_is_default_feed', false );
			}

			$this->notices->add_notice( __( 'GN Publisher settings saved.', 'gn-publisher' ) );
		}
	}

}