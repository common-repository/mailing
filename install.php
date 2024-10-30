<?php

    namespace Mailing\Install;
    
    $mailing_db_version = '0.1.4';

    function activation() {

        
        $options = get_option( 'mailing_options' ); 
        $options[ 'mailing_secret' ] = $options[ 'mailing_secret' ] ?? bin2hex( random_bytes( 40 ) );
        $options[ 'default_email' ] = $options[ 'default_email' ] ?? get_option( 'admin_email' );
        $options[ 'block_size' ] = $options[ 'block_size' ] ?? 10;

        
        update_option( 'mailing_options', $options );
        prepareDB();

    }

    function prepareDB() {
        global $wpdb, $mailing_db_version;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = 'CREATE TABLE ' .MAILING__PLUGIN_SUBSCRIPTIONS_TBL . ' (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            time datetime DEFAULT NOW() NOT NULL,
            email tinytext NOT NULL,
            active tinyint DEFAULT 1,
            data JSON,
            group_id INT NOT NULL,
            PRIMARY KEY  (id)
        ) ' . $charset_collate. ';';

        $sql .= 'CREATE TABLE ' .MAILING__PLUGIN_QUEUE_TBL . ' (
            newsletter_id INT UNSIGNED NOT NULL,
            subscriber_id INT UNSIGNED NOT NULL,
            status ENUM (\'waiting\', \'sent\' )
        ) ' . $charset_collate. ';';


    
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        update_option( 'mailing_db_version', $mailing_db_version );
    }
    

    function db_check() {
        global $mailing_db_version;
        if ( get_site_option( 'mailing_db_version' ) != $mailing_db_version ) {
            prepareDB();
        }
    }
    add_action( 'plugins_loaded', '\Mailing\Install\db_check' );