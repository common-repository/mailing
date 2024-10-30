<?php


namespace Mailing\Backend;

use Exception;
use Mailing\Newsletters\Newsletter;

class ajaxException extends Exception {}




/**
 * Update newsletter body with inlined CSS and prepare queue for sending
 */
add_action( 'wp_ajax_update_newsletter_body', 'Mailing\Backend\update_newsletter_body' );
function update_newsletter_body() {

    ajaxResponse( function() {
        
        $newsletter = Newsletter::getByID( (int) $_POST[ 'id' ] );
        $newsletter->setBody( wp_kses_normalize_entities( $_POST[ 'html' ] ) );
        $newsletter->prepareSend();

        return $newsletter->metrics();
        
    });

}




/**
 * Getting newsletter content for gutenberg CSS inliner
 */
add_action( 'wp_ajax_get_newsletter_content', 'Mailing\Backend\get_newsletter_content' );
function get_newsletter_content() {

    ajaxResponse( function() {

        if( !isset( $_POST[ 'html' ] ) ) {
            $newsletter = Newsletter::getByID( (int) $_POST[ 'id' ] );
            $_POST[ 'html' ] = $newsletter->body;
        }

        return [
            'content' => apply_filters( 'the_content', $_POST[ 'html' ] )
        ];
        
    });

}




/**
 * Getting newsletter metrics
 */
add_action( 'wp_ajax_metrics_newsletter', 'Mailing\Backend\metrics_newsletter' );
function metrics_newsletter() {

    ajaxResponse( function() {

        $newsletter = Newsletter::getByID( (int) $_POST[ 'id' ] );

        return $newsletter->metrics();
        
    });

}



/**
 * Sending newsletter
 */
add_action( 'wp_ajax_send_newsletter', 'Mailing\Backend\send_newsletter' );
function send_newsletter() {

    ajaxResponse( function() {
        $newsletter = Newsletter::getByID( (int) $_POST[ 'id' ] );
        $status = $newsletter->getStatus();

        if( $status === 'sent' )
            return $newsletter->metrics();

        $options = get_option( 'mailing_options' );

        $newsletter->send( $options[ 'block_size' ] ?? 10 );

        $metrics = $newsletter->metrics();

        if( $metrics[ 'audience' ] === $metrics[ 'sent' ] )
            $newsletter->finishSend( );

        return $metrics;
        
    });

}




/**
 * Generate challenge for new subscription
 */
add_action( 'wp_ajax_subscribe_attempt', 'Mailing\Backend\subscribe_attempt' );
add_action( 'wp_ajax_nopriv_subscribe_attempt', 'Mailing\Backend\subscribe_attempt' );
function subscribe_attempt() {

    ajaxResponse( function() {
        if( ! filter_var( $_POST[ 'email' ] ?? '', FILTER_VALIDATE_EMAIL ) )
            throw new ajaxException( __( 'Wrong email address', 'mailing_plugin' ) );

        $response = \Mailing\Subscriber\generateChallenge( $_POST[ 'email' ] );

        $response[ 'email' ] = $_POST[ 'email' ];
        $response[ 'group' ] = (int) $_POST[ 'group' ];

        \Mailing\send( 'confirm', __( 'Please verify your email for ', 'mailing_plugin' ) . get_bloginfo( 'name', 'raw' ), $_POST[ 'email' ], $response );

        unset( $response[ 'code' ] );

        return $response;
        
    });

}




/**
 * Confirm challenge by AJAX and save new email
 */
add_action( 'wp_ajax_confirm_attempt', 'Mailing\Backend\confirm_attempt' );
add_action( 'wp_ajax_nopriv_confirm_attempt', 'Mailing\Backend\confirm_attempt' );
function confirm_attempt() {

    ajaxResponse( function() {
        
        if( ! \Mailing\Subscriber\checkChallenge( $_POST[ 'email' ], $_POST[ 'code' ], $_POST[ 'challenge' ] ) ) 
            throw new ajaxException( __( 'Wrong confirmation code', 'mailing_plugin' ) );

        \Mailing\Subscriber\add( sanitize_email( $_POST[ 'email' ] ), (int) $_POST[ 'group' ] );

        return [];
        
    });

}




/**
 * Confirm challenge by POST and save new email
 */
add_action( 'admin_post_mailing_confirm', '\Mailing\Backend\mailing_confirm_post' );
add_action( 'admin_post_nopriv_mailing_confirm', '\Mailing\Backend\mailing_confirm_post' );
function mailing_confirm_post() {
    
    if( ! \Mailing\Subscriber\checkChallenge( $_GET[ 'email' ], $_GET[ 'code' ], $_GET[ 'challenge' ] ) ) 
        throw new ajaxException( 'Wrong confirmation code' );

    \Mailing\Subscriber\add( sanitize_email( $_GET[ 'email' ] ), (int) $_GET[ 'group' ] );

    $options = get_option( 'mailing_options' );

    if( filter_var( $options[ 'confirm_redirect' ], FILTER_VALIDATE_URL ) === false )
        die( __( 'Thank you for sign up.' ) );

    wp_redirect( $options[ 'confirm_redirect' ] );

    
    
}




/**
 * Unsubscribe user
 */
add_action( 'admin_post_mailing_unsubscribe', '\Mailing\Backend\mailing_unsubscribe' );
add_action( 'admin_post_nopriv_mailing_unsubscribe', '\Mailing\Backend\mailing_unsubscribe' );
function mailing_unsubscribe() {
    
    if( ! \Mailing\Subscriber\checkChallenge( $_REQUEST[ 'email' ], $_REQUEST[ 'code' ], $_REQUEST[ 'challenge' ] ) ) 
        die( __( 'We cannot proceed your request at this time. Please use other link.', 'mailing_plugin' ) );

        
    \Mailing\Subscriber\disable( sanitize_email( $_REQUEST[ 'email' ] ) );

    $options = get_option( 'mailing_options' );

    if( filter_var( $options[ 'unsubscribe_redirect' ], FILTER_VALIDATE_URL ) === false )
        die( __( 'Your email is disabled now.' ) );

    wp_redirect( $options[ 'unsubscribe_redirect' ] );

}


/**
 * Track email view
 */
add_filter( 'init', function(  ) {
    if( !preg_match( '/mailing\/([^\/]*)\/tr.gif$/', $_SERVER[ 'REQUEST_URI' ], $matches ) ) return;
    
    list( $newsletterID, $email ) =  explode( ':', $matches[ 1 ] );

    $newsletter = Newsletter::getByID( (int) $newsletterID );
    $newsletter->trackView( sanitize_email( $email ) );

    
    header( 'Content-Type: image/gif' );
    header( 'Content-Length: ' . filesize( MAILING__PLUGIN_DIR . 'assets/images/tr.gif' ) );
    readfile( MAILING__PLUGIN_DIR . 'assets/images/tr.gif' );
    exit;
} );









function ajaxResponse( $fn ) {
    try{
        $result = $fn();
        $result[ 'ok' ] = true;
    } catch( ajaxException $err ) {
        $result = [
            'ok' => false,
            'message' => $err->getMessage()
        ];
    }
    echo json_encode( $result );
    exit;
}