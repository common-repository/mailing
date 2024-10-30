<div class="mailing-subscribe-form" data-skin="<?php echo $args[ 'skin' ]; ?>">
    <div class="inner">
        <form class="mailingSubscribe step">
            <h2><?php echo __( 'Join the List', 'mailing_plugin' ); ?></h2>
            <div class="caption"><?php echo __( 'You will never miss our latest news. Our newsletter is once a week, every Wednesdat.', 'mailing_plugin' ); ?></div>
            <input type="email" name="email" placeholder="<?php echo __( 'Type your email address', 'mailing_plugin' ); ?>"/>
            <input type="submit" value="<?php echo __( 'Subscribe', 'mailing_plugin' ); ?>"/>
            <input type="hidden" name="group" value="<?php echo (int) $args[ 'group' ]; ?>"/>
            <div class="footer"><?php echo __( 'By submitting above, you agree to our ', 'mailing_plugin' ); ?><a href="<?php echo get_privacy_policy_url(); ?>" target="_blank" rel="noopener"><?php echo __( 'privacy policy', 'mailing_plugin' ); ?></a>.</div>
        </form>
        <form class="mailingSubscribeConfirm step">
            <div class="caption"><?php echo __( 'We sent a confirmation code to the email [useremail]. Please use it to confirm your email.', 'mailing_plugin' ); ?></div>
            <input type="text" name="code" maxlength="6" placeholder="<?php echo __( 'Confirmation code', 'mailing_plugin' ); ?>"/>
            <input type="hidden" name="group" value="<?php echo (int) $args[ 'group' ]; ?>"/>
            <div class="footer"><?php echo __( 'Did not receive the email? Check your spam filter, or ', 'mailing_plugin' ); ?><a href="#" class="otherEmail"><?php echo __( 'try another email address', 'mailing_plugin' ); ?></a></div>
        </form>
        <div class="mailingSubscribed step"><h2><?php echo __( 'Thank you for subscribing!', 'mailing_plugin' ); ?></h2></div>
    </div>
</div>