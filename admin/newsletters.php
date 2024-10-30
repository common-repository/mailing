<?php
    
    namespace Mailing\Admin\Newsletters;

    use Exception;
    use Mailing\Groups\Group;
    use Mailing\Newsletters\Newsletter;
    use WP_Post;


    /**
     * META BOXES FOR CLASSIC EDITOR
     */
    add_action( 'add_meta_boxes', '\Mailing\Admin\Newsletters\metaboxes' );
    function metaboxes(){
        global $post;
        add_meta_box( Newsletter::POST_TYPE . '_meta_targeting', __( 'Targeting', 'mailing_plugin' ), '\Mailing\Admin\Newsletters\metaTargeting', Newsletter::POST_TYPE, 'side', 'default',
         [ '__back_compat_meta_box' => true ] 
        );


        if( $post->post_status === 'publish' )
        add_meta_box( Newsletter::POST_TYPE . '_meta_status', __( 'Newsletter status', 'mailing_plugin' ), '\Mailing\Admin\Newsletters\metaStatus', Newsletter::POST_TYPE, 'side', 'high',
         [ '__back_compat_meta_box' => true ] 
        );
    }

    function metaStatus( WP_Post $post ) { 
        
        $newsletter = Newsletter::getByID( $post->ID );
        $metrics = $newsletter->metrics();

        if( $post->post_status === 'publish' && $newsletter->getStatus() === 'draft' ) {
            $newsletter->setStatus( 'presending' );
        }
        ?>
        <div id="mailingClassicNewsletterStatus" class="<?php echo esc_attr( $newsletter->getStatus() ); ?>" data-status="<?php echo esc_attr( $newsletter->getStatus() ); ?>" data-id="<?php echo esc_attr( $newsletter->ID ); ?>">

            <div class="newsletter-presending">
                <div class="mailingStatusHeader">
                    <span class="spinner is-active"></span>
                    <?php echo __( 'Preparing email body', 'mailing_plugin' ); ?>
                </div>
            </div>

            <div class="newsletter-sending">
                <div class="mailingStatusHeader">
                    <?php echo __( 'Sending email', 'mailing_plugin' ); ?>
                    <span class="spinner is-active"></span>
                </div>
                <div>
                    <div class="mailingProgress">
                        <div class="mailingProgress-inner"></div>
                        <div class="mailingProgress-text"></div>
                    </div>
                </div>
            </div>

            <div class="newsletter-sent">
                <div class="mailingStatusHeader paused">
                    <?php echo __( 'Newsletter is paused', 'mailing_plugin' ); ?>
                </div>
                <div class="mailingStatusHeader sent">
                    <?php echo __( 'Newsletter is finished', 'mailing_plugin' ); ?>
                </div>
                <div>
                    <div class="mailingMetrics">
                        <div>
                            <div class="mailingMetrics-value audience"><?php echo (int) $metrics[ 'audience' ]; ?></div>
                            <div class="mailingMetrics-caption"><?php _e( 'Targets', 'mailing_plugin' ); ?></div>
                        </div>
                        <div>
                            <div class="mailingMetrics-value sent"><?php echo (int) $metrics[ 'sent' ]; ?></div>
                            <div class="mailingMetrics-caption"><?php _e( 'Sent', 'mailing_plugin' ); ?></div>
                        </div>
                        <!-- <div>
                            <div class="mailingMetrics-value views"><?php echo (int) $metrics[ 'views' ]; ?></div>
                            <div class="mailingMetrics-caption"><?php _e( 'Views', 'mailing_plugin' ); ?></div>
                        </div> -->
                    </div>
                </div>
                <button class="button mailingResume"><?php _e( 'Resume', 'mailing_plugin' ); ?></button>
            </div>

        </div>
    <?php }

    function metaTargeting( WP_Post $post ) { 
        $newsletter = Newsletter::getByID( $post->ID );
        ?>
        <label for="group_id"><?php _e( 'Group', 'mailing_plugin' ); ?></label>
        <select name="group_id" id="group_id">
            <option value="-1" selected><?php _e( 'All groups', 'mailing_plugin' ); ?></option>
            <option value="0" <?php if( $newsletter->getTarget() === 0 ) echo 'selected'; ?>><?php _e( 'No group', 'mailing_plugin' ); ?></option>
            <?php foreach( Group::getAll() as $group ) { ?>
                <option value="<?php echo (int) $group->ID; ?>" <?php if( $newsletter->getTarget() == $group->ID ) echo 'selected'; ?>><?php echo esc_html( $group->title ); ?></option>
            <?php } ?>
        </select>
    <?php }



    /**
     * TRANSLATING PUBLISH TO SEND 
     */
    add_action( 'init', '\Mailing\Admin\Newsletters\init' );
    function init() {

        global $pagenow;

        if( ( ( $pagenow ?? '' ) === 'post-new.php' && ( $_GET[ 'post_type' ] ?? '' ) === Newsletter::POST_TYPE )
        || ( ( $pagenow ?? '' ) === 'post.php' && get_post_type( $_GET[ 'post' ] ?? 0 ) === Newsletter::POST_TYPE ) ) {
            add_filter( 'gettext', fn( $translated,$text_domain,$original) => ( $translated === 'Publish' ) ? __( 'Send', 'mailing_plugin' ) : $translated, 10, 3 );
        }

        
        
    }



    /**
     * ENQUE GUTENBERG SCRIPTS
     */
    add_action( 'enqueue_block_editor_assets', '\Mailing\Admin\Newsletters\blockEditor' );
    function blockEditor() {
        global $pagenow;

        if(
            !( ( $pagenow ?? '' ) === 'post-new.php' && ( $_GET[ 'post_type' ] ?? '' ) === Newsletter::POST_TYPE ) &&
            !( ( $pagenow ?? '' ) === 'post.php' && get_post_type( $_GET[ 'post' ] ?? 0 ) === Newsletter::POST_TYPE )
        ) return;

        wp_enqueue_script(
            'mailing-newsletter',
            plugins_url( 'assets/gutenberg/newsletter.js', __DIR__ ),
            [ 'wp-plugins', 'wp-edit-post', 'wp-i18n', 'wp-element' ]
        );

        wp_enqueue_style( 
            'newsletter-styles',  
            plugins_url( 'assets/gutenberg/newsletter.css', __DIR__ ),
        );
    }


    /**
     * ENQUE CLASSIC EDITOR STYLES FOR CSS INLINER
     */
    add_action('admin_enqueue_scripts', '\Mailing\Admin\Newsletters\classicEditor');
    function classicEditor() {
        global $pagenow;

        if(
            !( ( $pagenow ?? '' ) === 'post-new.php' && ( $_GET[ 'post_type' ] ?? '' ) === Newsletter::POST_TYPE ) &&
            !( ( $pagenow ?? '' ) === 'post.php' && get_post_type( $_GET[ 'post' ] ?? 0 ) === Newsletter::POST_TYPE )
        ) return;

        wp_enqueue_style( 'wp-block-library' );
        wp_enqueue_style( 
            'newsletter-styles',  
            plugins_url( 'assets/gutenberg/newsletter.css', __DIR__ ),
        );
    }



    add_filter('redirect_post_location', '\Mailing\Admin\Newsletters\redirect', 10, 2);
    function redirect( $location, $post_id ){

        if( ! isset( $_POST[ 'publish' ] ) ) return $location;

        if( 'mailing_newsletter' !== get_post_type( $post_id ) ) return $location;

        return $location . '&send=true';
    }


    add_action( 'save_post', '\Mailing\Admin\Newsletters\save', 10, 2 );
    function save( $id, $post ) {

        if( isset( $_POST[ 'group_id' ] ) ) {
            update_post_meta( $id, '_target', (int)$_POST[ 'group_id' ] );
        }

    }


    add_filter( 'manage_' . Newsletter::POST_TYPE . '_posts_columns', '\Mailing\Admin\Newsletters\columns' );
    function columns( $columns ) {
        
        $columns = [
            'cb' => $columns['cb'],
            'title' => __( 'Title' ),
            'status' => __( 'Status', 'mailing_plugin' ),
            'audience' => __( 'Audience', 'mailing_plugin' ),
            'sent' => __( 'Sent', 'mailing_plugin' ),
            // 'views' => __( 'Views', 'mailing_plugin' ),
            'date' => __( 'Date' ),
        ];

        return $columns;
    }

    add_action( 'manage_' . Newsletter::POST_TYPE . '_posts_custom_column', '\Mailing\Admin\Newsletters\column_content', 10, 2);
    function column_content( $column, $post_id ) {
        switch( $column ) {
            case 'sent':
                echo (int) get_post_meta( $post_id, '_sent', true );
                break;
            case 'audience':
                echo (int) get_post_meta( $post_id, '_audience', true );
                break;
            case 'views':
                echo (int) get_post_meta( $post_id, '_views', true );
                break;
            case 'status':
                echo get_post_meta( $post_id, '_status', true );
                break;
        }
    }