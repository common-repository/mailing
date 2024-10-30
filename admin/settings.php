<?php

    namespace Mailing\Admin;

    /**
     * Add Settings Link
     */
    add_filter( 'plugin_action_links_' . MAILING__BASENAME, '\Mailing\Admin\settings_link' );
    function settings_link( $links ) {

        $links[ 'settings' ] = '<a href="' . esc_url( admin_url( 'admin.php?page=mailing' ) ) . '">' . __( 'Settings' ) . '</a>';
        return $links;
    }


    /**
     * Add Settings Menu
     */
    add_action( 'admin_menu', '\Mailing\Admin\settigs_page' );
    function settigs_page() {
        add_menu_page(
            __( 'Mailing', 'mailing_plugin' ),
            __( 'Mailing', 'mailing_plugin' ),
            'manage_options',
            'mailing',
            '\Mailing\Admin\settings_html'
        );
        add_submenu_page( 'mailing', __( 'Mailing', 'mailing_plugin' ), __( 'Mailing Options', 'mailing_plugin' ), 'manage_options', 'mailing', '\Mailing\Admin\settings_html' );
    }


    /**
     * Save settings
     */
    add_action( 'admin_post_mailing_update_settings', '\Mailing\Admin\mailing_update_settings' );
    add_action( 'admin_post_nopriv_mailing_update_settings', '\Mailing\Admin\mailing_update_settings' );
    function mailing_update_settings() {

        $options = get_option( 'mailing_options' );

        if( isset( $_POST[ 'mailing_options' ][ 'default_email' ] ) )
            $options[ 'default_email' ] = sanitize_email( $_POST[ 'mailing_options' ][ 'default_email' ] );

        if( isset( $_POST[ 'mailing_options' ][ 'confirm_redirect' ] ) )
            $options[ 'confirm_redirect' ] = sanitize_url( $_POST[ 'mailing_options' ][ 'confirm_redirect' ] );

        if( isset( $_POST[ 'mailing_options' ][ 'unsubscribe_redirect' ] ) )
            $options[ 'unsubscribe_redirect' ] = sanitize_url( $_POST[ 'mailing_options' ][ 'unsubscribe_redirect' ] );

        if( isset( $_POST[ 'mailing_options' ][ 'block_size' ] ) )
            $options[ 'block_size' ] = max( 1, intval( $_POST[ 'mailing_options' ][ 'block_size' ] ) );


        update_option( 'mailing_options', $options );

        wp_safe_redirect( esc_url( admin_url( 'admin.php?page=mailing' ) ) );

        exit;

    }


    /**
     * Add settings page
     */
    function settings_html() {

        if ( ! current_user_can( 'manage_options' ) ) return;

        $options = get_option( 'mailing_options' );
    
        if ( isset( $_GET['settings-updated'] ) ) 
            add_settings_error( 'mailing_options_messages', 'mailing_message', __( 'Settings Saved', 'mailing-plugin' ), 'updated' );
    
        settings_errors( 'mailing_options_messages' );

        ?>
        <div class="mailing-wrap">
            <header>
                <h1><?php echo __( 'Mailing Options'); ?></h1>
            </header>
            <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
                <input type="hidden" name="action" value="mailing_update_settings">
                <div class="option">
                    <label for="fromEmail"><?php _e( 'From:', 'mailing_plugin' ); ?></label>
                    <p class="description">
                        <?php esc_html_e( 'Default sender address for all emails.', 'mailing-plugin' ); ?>
                    </p>
                    <input type="text" id="fromEmail" name="mailing_options[default_email]" value="<?php echo esc_attr( $options[ 'default_email' ] ?? '' ); ?>" placeholder="reply-to@website.com">
                </div>

                <div class="option">
                    <label for="confirm_redirect"><?php _e( 'Redirect after confirm:', 'mailing_plugin' ); ?></label>
                    <p class="description">
                        <?php esc_html_e( 'User will be redirected to this URL after confirm subscription by link from email.', 'mailing-plugin' ); ?>
                    </p>
                    <input type="text" id="confirm_redirect" name="mailing_options[confirm_redirect]" value="<?php echo esc_attr( $options[ 'confirm_redirect' ] ?? '' ); ?>" placeholder="https://website.com/thanks-for-subscribe/">
                    
                </div>

                <div class="option">
                    <label for="unsubscribe_redirect"><?php _e( 'Redirect after unsubscribe:', 'mailing_plugin' ); ?></label>
                    <p class="description"><?php esc_html_e( 'User will be redirected to this URL after unsubscribe from newsletters.', 'mailing-plugin' ); ?></p>
                    <input type="text" id="unsubscribe_redirect" name="mailing_options[unsubscribe_redirect]" value="<?php echo esc_attr( $options[ 'unsubscribe_redirect' ] ?? '' ); ?>" placeholder="https://website.com/you-unsubscribed/">
                </div>

                <div class="option">
                    <label for="block_size"><?php _e( 'Emails block size:', 'mailing_plugin' ); ?></label>
                    <p class="description"><?php esc_html_e( 'Mailing plugin sends newsletters in blocks of several emails per each request. The higher number of emails to send at once the faster all queue will be sent. But it comes with growing chances that your server won\'t be able to proceed block of emails and you will get gateway timeout error.', 'mailing-plugin' ); ?></p>
                    <input type="text" id="block_size" name="mailing_options[block_size]" value="<?php echo (int)($options[ 'block_size' ] ?? 0); ?>" placeholder="How many emails to send per each request?">
                </div>

                <?php submit_button( __( 'Save Settings', 'mailing_plugin' ) ); ?>
            </form>
        </div>
        <?php
    }