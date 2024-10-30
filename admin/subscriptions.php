<?php

    namespace Mailing\Admin;

    if ( ! class_exists( 'WP_List_Table' ) ) 
        require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

use Mailing\Groups\Group;
use WP_List_Table;


    
     /**
     * Add the top level menu page.
     */
    add_action( 'admin_menu', '\Mailing\Admin\subscriptions_page' );
    function subscriptions_page() {
        add_submenu_page( 'mailing', 'Subscriptions', 'Subscriptions', 'manage_options', 'subscriptions', '\Mailing\Admin\subscriptions_html' );
    }


    function subscriptions_html() { 
        
        $table = new SubscriptionsTable();
        $table->prepare_items();
        ?>
        <div class="wrap">
            <h2><?php _e( 'Subscriptions', 'mailing_plugin' ); ?></h2>
            <form action="">
            <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ) ?>" />
            <?php
                $table->search_box( __( 'Search', 'mailing_plugin' ), 'search_id' );
                $table->display();
            ?>
            </form>
        </div>
    <?php }


    class SubscriptionsTable extends WP_List_Table {

        const PER_PAGE = 30;

        private $userMessages;
        
        function __construct(){
            $this->userMessages = [];
            parent::__construct([
                'singular' => __( 'Subscription', 'mailing_plugin' ),
                'plural' => __( 'Subscriptions', 'mailing_plugin' ),
                'ajax' => false
            ]);
        
        }


        /**
         * Get total rows number
         */
        function total() {
            global $wpdb;

            return $wpdb->get_var( 'SELECT COUNT(1) FROM ' . MAILING__PLUGIN_SUBSCRIPTIONS_TBL );
        }



        /**
         * Default cell output
         * @param Array $item - Row item
         * @param string $column_name column name
         * @return string cell text
         */
        function column_default( $item, $column_name ) {
            return $item[ $column_name ] ?? '';
        }

        /**
         * Checkbox column output
         * @param Array $item - Row item
         * @return string cell text
         */
        function column_cb( $item ) {
            return sprintf(		
                '<label class="screen-reader-text" for="subscription_' . (int)$item[ 'id' ] . '">' . sprintf( __( 'Select %s' ), esc_html( $item['email'] ) ) . '</label>'
                . '<input type="checkbox" name="subscriptions[]" id="subscription_' . (int)$item['id'] . '" value="' . (int)$item['id'] . '" />'
                );
        }

        /**
         * Status column output
         * @param Array $item - Row item
         * @return string cell text
         */
        function column_status( $item ) {

            return ( $item[ 'active' ] > 0 )
                ? __( 'Active', 'mailing_plugin' )
                : __( 'Unsubscribed', 'mailing_plugin' );
        }

        /**
         * Group column output
         * @param Array $item - Row item
         * @return string cell text
         */
        function column_group( $item ) {
            $group = Group::getByID( $item[ 'group_id' ] );

            $title = $group 
            ? $group->title
            : 'No group';

            return sprintf( '<a href="?page=%s&group_id=%d">%s</a>', esc_attr( $_REQUEST[ 'page' ] ), (int) $item[ 'group_id' ], esc_html( $title ) );
        }

        function column_time( $item ) {
            return 'Subscribed<br />' . mysql2date( get_option('date_format') . ' ' . get_option('time_format'), $item[ 'time' ] );
        }

        function column_email( $item ) {
            $actions = [
                'delete'    => sprintf('<a href="?page=%s&action=%s&subscription=%s&_wpnonce=%s">Delete</a>',esc_attr( $_REQUEST['page'] ),'delete',(int)$item['id'], wp_create_nonce( 'bulk-' . $this->_args[ 'plural' ] )),
            ];
            
            return sprintf('%1$s %2$s', esc_html( $item['email'] ), $this->row_actions($actions) );
        }

        /**
         * Columns
         */
        public function get_columns() {
            return [
                'cb' => '<input type="checkbox" />',
                'email' => __( 'Email', 'mailing_plugin' ),
                'status' => __( 'Status', 'mailing_plugin' ),
                'group' => __( 'Subscription group', 'mailing_plugin' ),
                'time' => __( 'Date', 'mailing_plugin' )
            ];
        }


        /**
         * Get sortable columns
         * @return Array
         */
        public function get_sortable_columns() {
            return [
               'time'=>'time'
            ];
         }


        /**
         * List of bulk actions
         */
        function get_bulk_actions() {
            return [
                'bulk-delete' => __( 'Delete', 'mailing_plugin' )
            ];
        }


        /**
         * Output if there is no subscriptions
         */
        public function no_items(){
            _e( 'No subscriptions yet', 'mailing_plugin' );
        }


        /**
         * DELETE subscriptions
         */
        private function deleteSubscription( Array $ids ) {
            global $wpdb;

            $wpdb->query( 'DELETE FROM ' . MAILING__PLUGIN_SUBSCRIPTIONS_TBL . ' WHERE id IN( ' . implode( ',', array_map( 'absint', $ids ) ) . ' )' );

        }


        /**
         * Get subscriptions
         * @param int $per_page Items per page
         * @param int $paged Page Number
         * @return Array[] Array of rows
         */
        private function getSubscriptions( $per_page, $paged ) {
            global $wpdb;

            $where = [ 'true' ];
            $params = [];

            if( $_GET[ 's' ] ?? false ) {
                $where[] = 'email RLIKE %s';
                $params[] = sanitize_email( $_GET[ 's' ] );
            }

            if( isset( $_GET[ 'group_id' ] ) ) {
                $where[] = 'group_id = %d';
                $params[] = (int)$_GET[ 'group_id' ];
            }

            $sql = 'SELECT * FROM ' . MAILING__PLUGIN_SUBSCRIPTIONS_TBL . ' WHERE ' . implode( ' AND ', $where );


            $sql .= ' ORDER BY %s';
            $params[] .= ! empty( $_REQUEST[ 'orderby' ] ) 
                ? sanitize_sql_orderby( $_REQUEST['orderby'] )
                : 'time';

            $sql .= ' %s';
            $params[] .= ! empty( $_REQUEST[ 'order' ] ) 
                ? ' ASC'
                : ' DESC';

         
            $sql .= ' LIMIT %d';
            $params[] = (int)$per_page;

            $sql .= ' OFFSET %d';
            $params[] = ( $paged - 1 ) * $per_page;

            return $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );
        }


        function handle_actions() {

            if( !$this->current_action() ) return;
            // Check nonce
            $nonce = wp_unslash( $_REQUEST['_wpnonce'] );
            
            if ( ! wp_verify_nonce( $nonce, 'bulk-' . $this->_args[ 'plural' ] ) ) 
                return $this->invalid_nonce_redirect();
            
                
            // proceed action
            switch( $this->current_action() ) {
                case 'bulk-delete':
                    $this->deleteSubscription( array_map( 'absint', $_REQUEST[ 'subscriptions' ] ) );
                    $this->message( __( 'Bulk deleted...', 'mailing_plugin' ), 'success' );
                    break;
                case 'delete':
                    $this->deleteSubscription( [ (int)$_REQUEST[ 'subscription' ] ] );
                    $this->message( __( 'Email deleted', 'mailing_plugin' ), 'success' );
                    break;
            }
        }


        /**
         * Prepare Table
         */
        public function prepare_items() {
            

            $this->_column_headers = [ $this->get_columns(), ['id'], $this->get_sortable_columns() ];

            $this->handle_actions();

            $per_page = self::PER_PAGE;
            $paged = max( ( int ) ( $_GET[ 'paged' ] ?? 1 ), 1 );

            $this->items = $this->getSubscriptions( $per_page, $paged );

            $this->set_pagination_args( [
                'total_items' => $this->total(),
                'per_page'    => $per_page
            ] );

        }

        function extra_tablenav( $which ) {
            if ( $which !== "top" ) return;

            foreach( $this->userMessages as $message ) { ?>
                <div class="notice notice-<?php echo esc_attr( $message[1] ); ?> is-dismissible">
                    <p><?php echo esc_html( $message[0] ); ?></p>
                </div>
            <?php }
        }

        function message( $text, $type = 'info' ) {
            $this->userMessages[] = [ $text, $type ];
        }

    }