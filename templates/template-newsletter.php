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
        <?php echo $args[ 'message' ]; ?>
        <?php unset( $args[ 'message' ] ); ?>

        <footer style="text-align:left;padding:2em;color:#adb3b9;font-size:14px">
            <p style="margin:0;"><?php echo get_bloginfo( 'name', 'raw' ); ?></p>
            <p style="margin:0;">
                <a style="color:#adb3b9;" href="<?php echo esc_attr( $args[ 'unsubscribe' ] ); ?>"><?php _e( 'Unsubscribe', 'mailing_plugin' ); ?></a>

                <?php if( get_privacy_policy_url() !== '' ) { ?>
                    <span style="margin:0 1em">|</span>
                    <a style="color:#adb3b9;" href="<?php echo esc_attr( get_privacy_policy_url() ); ?>"><?php _e( 'Privacy policy', 'mailing_plugin' ); ?></a>
                <?php } ?>
                
            </p>
        </footer>
    </div>
</body>
</html>