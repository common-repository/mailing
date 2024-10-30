<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="x-apple-disable-message-reformatting">
    <title></title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
</head>
<body style="margin:0;padding:0">
    <div style="max-width:70ch;margin:0 auto;font-size:17px;line-height:1.7em;">
        <p><?php _e( 'Hi!', 'mailing_plugin' ); ?></p>
        <p><?php _e( 'Thank you for subscribing to our newsletters.', 'mailing_plugin' ); ?></p>
        <p><?php echo sprintf( __( 'Your verification code is <strong>%s</strong>.', 'mailing_plugin' ), $args[ 'code' ] ); ?></p>
        <p><?php _e( 'Enter this code in confirmation form on our website, or click the link below to confirm your email.', 'mailing_plugin' ); ?></p>

        <p><a href="<?php echo esc_attr( $args[ 'confirm' ] ); ?>"><?php _e( 'Confirm email', 'mailing_plugin' ); ?></a></p>

        <p><?php _e( 'We’re glad you’re here!', 'mailing_plugin' ); ?></p>

        <footer style="text-align:left;padding:2em;color:#adb3b9;font-size:14px">
            <p style="margin:0;"><?php echo get_bloginfo( 'name', 'raw' ); ?></p>
            <?php if( get_privacy_policy_url() !== '' ) { ?>
            <p style="margin:0;">
                <a style="color:#adb3b9;" href="<?php echo esc_attr( get_privacy_policy_url() ); ?>"><?php _e( 'Privacy policy', 'mailing_plugin' ); ?></a>
            </p>
            <?php } ?>
        </footer>
    </div>
</body>
</html>