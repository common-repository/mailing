<?php

    namespace Mailing\Groups;

    use \WP_Post;


    class Group {

        const POST_TYPE = 'mailing_group';
        public $ID,
            $title,
            $post;

        function __construct( WP_Post $post ){

            $this->ID = $post->ID;
            $this->post = $post;
            $this->title = $post->post_title;
            
        }


        /**
         * Get group by ID
         * @param int $id Post ID
         * @return Group|false
         */
        static function getByID( $id ) {
            if( !$post = get_post( $id ) )
                return false;

            return new self( $post );
        }

        /**
         * Get all groups
         * @return Group[]
         */
        static function getAll() {
            return array_map( fn( $post ) => new self( $post ), get_posts( [ 'post_type' => self::POST_TYPE ] ) );
        }

    }


    add_action( 'init', '\Mailing\Groups\init' );
    function init() {
        register_post_type( Group::POST_TYPE,
            [
                'labels' => [
                    'name' => __( 'Subscription Groups' ),
                    'singular_name' => __( 'Subscription group' ),
                    'add_new' => __( 'Add new' ),
                    'add_new_item' => __( 'Add new' ),
                ],
                'public' => false,
                'menu_icon' => 'dashicons-mailing-group',
                'menu_position' => 13,
                'supports' => [
                    'title',
                ],
                'show_ui' => true,
                'show_in_menu' => 'mailing',
                'show_in_rest' => true,
                'has_archive' => false
            ]
        );
    }