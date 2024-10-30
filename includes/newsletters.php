<?php

    namespace Mailing\Newsletters;

    use Mailing\Backend\ajaxException;
    use WP_Post;

    class Newsletter {

        const POST_TYPE = 'mailing_newsletter';
        public $ID,
            $subject,
            $body,
            $post;

        private $status, $target;

        function __construct( WP_Post $post ){

            $this->ID = $post->ID;
            $this->post = $post;
            $this->subject = $post->post_title;
            $this->body = apply_filters( 'the_content', $post->post_content );


            $this->target = get_post_meta( $this->ID, '_target', true );
            $this->status = get_post_meta( $this->ID, '_status', true );
            
        }

        function getTarget() {
            return $this->target;
        }

        public function getBody() {

            $body = get_post_meta( $this->ID, '_body', true );
            if( $body == false ) 
                throw new ajaxException( __( 'Body for this newsletter is not ready', 'mailing_plugin' ) );

            return $body;

        }


        /**
         * Get Newsletter by ID
         * @param int $id Post ID
         * @return Newsletter
         */
        static function getByID( $id ) {
            if( !$post = get_post( $id ) )
                return false;

            return new self( $post );
        }


        /**
         * Prepares recepients queue by newsletter targeting
         */
        public function prepareSend() {
            global $wpdb;

            
            $wpdb->delete( MAILING__PLUGIN_QUEUE_TBL, [ 'newsletter_id' => $this->ID ] );

            $where = [ 'active = %d' ];
            $params = [ $this->ID, 'waiting', 1 ];

            if( $this->target > -1 ) {
                $where[] = 'group_id = %d';
                $params[] = $this->target;
            }

            $sql = 'INSERT INTO ' . MAILING__PLUGIN_QUEUE_TBL . ' (newsletter_id, subscriber_id, status ) SELECT %d, id, %s FROM ' . MAILING__PLUGIN_SUBSCRIPTIONS_TBL . ' WHERE ' . implode( ' AND ', $where );

            $wpdb->query( $wpdb->prepare( $sql, $params ) );

            $this->setStatus( 'pause' );

            $metrics = $this->metrics();
            update_post_meta( $this->ID, '_audience', $metrics[ 'audience' ] );
            update_post_meta( $this->ID, '_views', 0 );

        }

        /**
         * Clear queue and save metrics after sending
         */
        function finishSend() {
            global $wpdb;

            $metrics = $this->metrics();
            update_post_meta( $this->ID, '_sent', $metrics[ 'sent' ] );
            update_post_meta( $this->ID, '_audience', $metrics[ 'audience' ] );

            $wpdb->delete( MAILING__PLUGIN_QUEUE_TBL, [ 'newsletter_id' => $this->ID ] );

            $this->setStatus( 'sent' );
        }

        /**
         * GET emails in queue
         * @param int $limit Nuber of emails to fetch from queue
         */
        public function getSubscribers( int $limit = 1 ) {
            global $wpdb;

            return $wpdb->get_results( $wpdb->prepare( 'SELECT q.subscriber_id, email FROM ' . MAILING__PLUGIN_QUEUE_TBL . ' q LEFT JOIN ' . MAILING__PLUGIN_SUBSCRIPTIONS_TBL . ' s ON s.id = q.subscriber_id WHERE status = %s AND newsletter_id = %d LIMIT %d', [ 'waiting', $this->ID, $limit ] ) );
        }


        /**
         * Sending newsletter to recepients left in queue
         * @param int $limit Number of emails to send
         */
        function send( int $limit = 1 ) {
            global $wpdb;
            
            $subscribers = $this->getSubscribers( $limit );


            foreach( $subscribers as $subscriber ) {
                $challenge = \Mailing\Subscriber\generateChallenge( $subscriber->email );
                \Mailing\send( 'newsletter', $this->subject, $subscriber->email, [ 
                    'message' => $this->getBody(), 
                    'email' => $subscriber->email, 
                    'challenge' => $challenge[ 'challenge' ], 
                    'code' => $challenge[ 'code' ],
                    'id' => $this->ID
                ] );
                $wpdb->update( MAILING__PLUGIN_QUEUE_TBL, [ 'status' => 'sent' ], [ 'newsletter_id' => $this->ID, 'subscriber_id' => $subscriber->subscriber_id ] );
            }

            $metrics = $this->metrics();
            update_post_meta( $this->ID, '_sent', $metrics[ 'sent' ] );
         
        }


        /**
         * Get newsletter status
         * @return 'new'|'sent'|'draft'|'pause'
         */
        function getStatus() {

            return ( $this->status )
                ? $this->status
                : 'draft';

        }


        /**
         * Set newsletter status
         * @param 'new'|'sent'|'draft'|'pause'
         */
        function setStatus( $status ) {
            
            $this->status = $status;
            update_post_meta( $this->ID, '_status', $this->status );

        }

        function metrics() {
            global $wpdb;

            if( $this->status === 'sent' ) 
                return [
                    'audience' => (int) get_post_meta( $this->ID, '_audience', true ),
                    'sent' => (int) get_post_meta( $this->ID, '_sent', true ),
                    'views' => (int) get_post_meta( $this->ID, '_views', true ),
                ];
            

            $result = $wpdb->get_row( $wpdb->prepare( 'SELECT SUM(1) audience, SUM(status=%s) sent FROM ' . MAILING__PLUGIN_QUEUE_TBL . ' WHERE newsletter_id = %d', [ 'sent', $this->ID ]), ARRAY_A );

            $result[ 'audience' ] = $result[ 'audience' ] ?? 0;
            $result[ 'sent' ] = $result[ 'sent' ] ?? 0;

            $result[ 'views' ] = (int) get_post_meta( $this->ID, '_views', true );;

            return $result;

        }


        /**
         * Set newsletter body
         * @param string $html Body content ready to send
         * @return void
         */
        function setBody( $html ) {
            update_post_meta( $this->ID, '_body', $html );
        }


        /**
         * Track newsletter view
         * @param string $email Email of viewer
         * @return void
         */
        function trackView( $email ) {

            $views = (int) get_post_meta( $this->ID, '_views', true );
            update_post_meta( $this->ID, '_views', ++$views );

        }

    }


    add_action( 'init', '\Mailing\Newsletters\init' );
    function init() {
        global $wp;
        register_post_type( Newsletter::POST_TYPE,
            [
                'labels' => [
                    'name' => __( 'Newsletters' ),
                    'singular_name' => __( 'Newsletter' ),
                    'add_new' => __( 'Add new' ),
                    'add_new_item' => __( 'Add new' ),
                    'item_published'           => __( 'Newsletter started.', 'textdomain' ),
                    'item_published_privately' => __( 'Newsletter started.', 'textdomain' ),
                    'item_reverted_to_draft'   => __( 'Email reverted to draft.', 'textdomain' ),
                    'item_scheduled'           => __( 'Newsletter scheduled.', 'textdomain' ),
                    'item_updated'             => __( 'Newsletter updated.', 'textdomain' ),
                ],
                'public' => false,
                'menu_icon' => 'dashicons-mailing-newsletter',
                'menu_position' => 13,
                'supports' => [
                    'title',
                    'editor', 
                    'custom-fields',
                    'page-attributes', 
                ],
                'show_ui' => true,
                'show_in_menu' => 'mailing',
                'show_in_rest' => true,
                'has_archive' => false
            ]
        );

        register_meta( 'post', '_target', [
            'object_subtype' => Newsletter::POST_TYPE,
            'show_in_rest' => true,
            'type' => 'integer',
            'single' => true,
            'auth_callback' => function( ) {
                return current_user_can( 'edit_posts' );
            }
        ]);

        register_meta( 'post', '_status', [
            'object_subtype' => Newsletter::POST_TYPE,
            'show_in_rest' => true,
            'type' => 'string',
            'single' => true,
            'auth_callback' => function( ) {
                return current_user_can( 'edit_posts' );
            }
        ]);

    }