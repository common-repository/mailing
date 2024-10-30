<?php

    namespace Mailing\Admin;

    require_once( MAILING__PLUGIN_DIR . 'admin/settings.php' );
    require_once( MAILING__PLUGIN_DIR . 'admin/subscribe-form.php' );
    require_once( MAILING__PLUGIN_DIR . 'admin/subscriptions.php' );
    require_once( MAILING__PLUGIN_DIR . 'admin/newsletters.php' );



    // Enqueue common dashboard CSS and JS
    add_action('admin_enqueue_scripts', '\Mailing\Admin\enqueue');
    function enqueue() {

        wp_enqueue_script(
            'mailing-admin',
            plugins_url( 'assets/dashboard.js', __DIR__ ),
            [],
            '0.1.4'
        );

        wp_enqueue_style( 
            'mailing-admin',  
            plugins_url( 'assets/dashboard.css', __DIR__ ),
            [],
            '0.1.4'
        );
        wp_localize_script( 'mailing-admin', 'ajaxurl', [ admin_url( 'admin-ajax.php' ) ] );

    }