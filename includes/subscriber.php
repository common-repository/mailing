<?php

    namespace Mailing\Subscriber;


    /**
     * Generate challenge for email confirmation
     * @param string $email user email
     * @return Object
     */
    function generateChallenge( $email ) {

        $options = get_option( 'mailing_options' );
        $secret = $options[ 'mailing_secret' ];

        $email = trim( $email );

        $code = rand( 100000, 999999 );
        $hash = implode( '.', [ $email, $code, $secret ] );

        return [ 
            'code' => $code,
            'challenge' => password_hash( $hash, PASSWORD_BCRYPT )
        ];
    }


    /**
     * Check comnfirmation 
     * @param string $email subscribed Email
     * @param string $code sent code
     * @param string $challenge generated challenge
     * @return boolean
     */
    function checkChallenge( $email, $code, $challenge ) {

        $options = get_option( 'mailing_options' );
        $secret = $options[ 'mailing_secret' ];

        $email = trim( $email );

        $hash = implode( '.', [ $email, $code, $secret ] );

        return password_verify( $hash, $challenge );

    }


    /**
     * Add email to the subscribers
     * @param string $email Email
     * @param int $group Subscriptions group
     * @return void
     */
    function add( $email, $group ) {
        global $wpdb;

        $check = $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . MAILING__PLUGIN_SUBSCRIPTIONS_TBL . ' WHERE email = %s AND group_id = %d', [ $email, $group ] ) );

        if( $check ) return;

        $wpdb->insert( MAILING__PLUGIN_SUBSCRIPTIONS_TBL, [
            'email' => $email,
            'group_id' => $group
        ] );

    }
            

    /**
     * Remove email from subscribers
     * @param string $email Email
     */
    function disable( $email ) {
        global $wpdb;

        $wpdb->update( MAILING__PLUGIN_SUBSCRIPTIONS_TBL, [
            'active' => 0,
        ], [ 'email' => $email ] );
    }


    function shortcode( $args ){
        $args = shortcode_atts( array(
            'group' => 0,
            'skin' => '',
            'template' => 'default'
        ), $args );

        ob_start();

        if( !get_template_part( 'mailing/form', 'subscriber', $args ) ) 
            load_template( MAILING__PLUGIN_DIR . '/templates/form-'.$args[ 'template' ].'.php', false, $args );

        $content = ob_get_contents();

        ob_clean();

        return $content;

	}
	add_shortcode( 'mailing', '\Mailing\Subscriber\shortcode' );