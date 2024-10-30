<?php

    namespace Mailing;

    use Exception;

    class mailException extends Exception {}



    add_action( 'wp_mail_failed', '\Mailing\mailfailed' );
    function mailfailed( $err ) {
        throw new mailException( $err->errors[ 'wp_mail_failed' ][0] );
    }