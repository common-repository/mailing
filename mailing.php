<?php

    /**
     * Plugin Name:       Mailing
     * Plugin URI:        https://mailing.webomus.ru/
     * Description:       Handle the email subscriptions and mailing.
     * Version:           0.1.4
     * Requires at least: 5.2
     * Requires PHP:      7.4
     * Author:            Igor Parchinsky
     * Author URI:        https://webomus.ru/
     * License:           GPL v2
     * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
     * Text Domain:       mailing-plugin
     * Domain Path:       /languages
     */
    /*
    "Mailing" is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 2 of the License, or
    any later version.
    
    "Mailing" is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.
    
    You should have received a copy of the GNU General Public License
    along with "Mailing". If not, see https://www.gnu.org/licenses/gpl-2.0.html.
    */

    

    global $wpdb;
    define( 'MAILING__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
    define( 'MAILING__BASENAME', plugin_basename( __FILE__ ) );
    define( 'MAILING__PLUGIN_SUBSCRIPTIONS_TBL', $wpdb->prefix . 'mailing_subscribers' );
    define( 'MAILING__PLUGIN_QUEUE_TBL', $wpdb->prefix . 'mailing_queue' );


    require_once( MAILING__PLUGIN_DIR . 'includes/groups.php' );
    require_once( MAILING__PLUGIN_DIR . 'includes/newsletters.php' );

    require_once( MAILING__PLUGIN_DIR . 'install.php' );
    
    require_once( MAILING__PLUGIN_DIR . 'includes/errors.php' );
    
    require_once( MAILING__PLUGIN_DIR . 'includes/mailer.php' );

    require_once( MAILING__PLUGIN_DIR . 'includes/handlers.php' );

    require_once( MAILING__PLUGIN_DIR . 'includes/subscriber.php' );
    
    
    if ( is_admin() || ( isset( $_REQUEST[ 'rest_route' ] ) ) ) {
        require_once( MAILING__PLUGIN_DIR . 'admin/admin.php' );
    }
    

    register_activation_hook( __FILE__, '\Mailing\Install\activation' );


    function mailing_enqueue() {
        wp_register_style( 'mailing-styles',  plugin_dir_url( __FILE__ ) . 'assets/style.css', [], '0.1.4' );
        wp_enqueue_style( 'mailing-styles' );

        wp_enqueue_script(
			'mailing',
			plugin_dir_url( __FILE__ ) . 'assets/app.js',
			[],
			'0.1.4',
			true
		);
        wp_localize_script( 'mailing', 'ajaxurl', [ admin_url( 'admin-ajax.php' ) ] );
    }
    add_action( 'wp_enqueue_scripts', 'mailing_enqueue' );