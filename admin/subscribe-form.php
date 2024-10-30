<?php

	// defined( 'ABSPATH' ) || exit;

	
	function mailing_subscribe() {

		register_block_type( MAILING__PLUGIN_DIR . 'includes/gutenberg-subscribe-block/' );

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'mailing-subscribe', 'mailing-plugin' );
		}

	}
	add_action( 'init', 'mailing_subscribe' );
