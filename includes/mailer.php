<?php

    namespace Mailing;




    /**
     * Send email using template
     * @param string $template template file name
     * @param string $subject mail subject
     * @param string $to recepient email
     * @param array $args array of parameters that will be passed to template
     * @param string|bool $from Sender email, or false to use email from plugin settings
     * @return bool Whether the email was sent successfully
     */
    function send( $template, $subject, $to, $args = [], $from = false ) {


        if( !$from ) {
            $options = get_option( 'mailing_options' );
            $from = $options[ 'default_email' ];
        }

        ob_start(  );
        
        if( !generateBody( $template, $args ) ) 
            throw new mailException( 'Template ' . $template . ' not found' );

        $message = trim( ob_get_contents() );
        ob_end_clean();

        

        $headers  = "From: " . $from;
        $headers .= "\r\nContent-type: text/html; charset=utf-8";

        return wp_mail( $to, $subject, $message, $headers );
    }


    /**
     * @param string $template template name
     * @param array $args arguments used in the template
     * @return bool whether template was found and loaded
     */
    function generateBody( $template, $args ){

        if( $template === 'newsletter' ) {
            $args[ 'unsubscribe' ] = admin_url( 'admin-post.php?' . http_build_query( [
                'action' => 'mailing_unsubscribe',
                'email' => $args[ 'email' ],
                'code' => $args[ 'code' ],
                'challenge' => $args[ 'challenge' ]
            ]) );
        }

        if( $template === 'confirm' ) {
            $args[ 'confirm' ] = admin_url( 'admin-post.php?' . http_build_query( [
                'action' => 'mailing_confirm',
                'email' => $args[ 'email' ],
                'code' => $args[ 'code' ],
                'challenge' => $args[ 'challenge' ],
                'group' => $args[ 'group' ]
            ]) );
        }

        $result = get_template_part( 'mailing/template', $template, $args );
        
        if( $result !== false ) return true;

        if( !file_exists( MAILING__PLUGIN_DIR . '/templates/template-'.$template.'.php' ) ) return false;

        load_template( MAILING__PLUGIN_DIR . '/templates/template-'.$template.'.php', false, $args );

        return true;
        
    }