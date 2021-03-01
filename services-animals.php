<?php
/**
 * Plugin Name: Services Animals
 * Plugin URI: https://jnjsite.com/services-animals/
 * License: GPLv2 or later
 * Description: Service Animals.
 * Version: 0.1
 * Author: Jaime Niñoles
 * Author URI: https://jnjsite.com/.
 */
defined('ABSPATH') or die('No no no');
define('SA_PATH', plugin_dir_path(__FILE__));

include_once SA_PATH.'services-animals-backend-controller.php';

class ServicesAnimals
{
    private static $instance;
    private $custom_fields;

    public static function get_instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        // Activation and deactivation..
        register_activation_hook(__FILE__, [$this, 'activation']);
        register_deactivation_hook(__FILE__, [$this, 'deactivation']);

        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_css_js']);
        add_action('init', [$this, 'create_custom_post_type']);

        ServicesAnimalsBackendController::get_instance();

        $this->_fill_custom_fields();
    }

    public function activation()
    {
        register_setting('sa_options_group', 'sa_db_version');
        register_setting('sa_options_group', 'sa_quantity_per_batch');
        register_setting('sa_options_group', 'sa_time_between_batches');

        add_option('sa_db_version', 0);
        add_option('sa_quantity_per_batch', '2');
        add_option('sa_time_between_batches', '30');
    }

    public function deactivation()
    {
        unregister_setting('sa_options_group', 'sa_db_version');
        unregister_setting('sa_options_group', 'sa_quantity_per_batch');
        unregister_setting('sa_options_group', 'sa_time_between_batches');
    }

    public function uninstall()
    {
        delete_option('sa_db_version');
        delete_option('sa_quantity_per_batch');
        delete_option('sa_time_between_batches');
    }

    /**
     * It adds assets only for the backend..
     */
    public function enqueue_admin_css_js()
    {
        wp_enqueue_style('sa_custom_style', plugin_dir_url(__FILE__).'lib/sa.min.css', false, '0.0.1');
        wp_enqueue_script('sa_custom_script', plugin_dir_url(__FILE__).'lib/sa.min.js', ['jquery'], '0.0.1');
    }

    // The custom post type for the Sites under study..
    public function create_custom_post_type()
    {
        $taxonomy_labels = [
            'name' => 'Taxonomies',
            'singular_name' => 'Taxonomy',
            'menu_name' => 'Taxonomies',
            'all_items' => 'All taxonomies',
            'edit_item' => 'Edit taxonomy',
            'view_item' => 'View taxonomy',
            'update_item' => 'Update taxonomy',
            'add_new_item' => 'Add taxonomy',
            'new_item_name' => 'New taxonomy name',
            'parent_item' => null,
            'parent_item_colon' => null,
            'search_items' => 'Search taxonomies',
            'popular_items' => 'Popular taxonomies',
            'separate_items_with_commas' => 'Separate items with commas',
            'add_or_remove_items' => 'Add or remove items',
            'choose_from_most_used' => 'Choose from most used',
            'not_found' => 'Not found',
            'back_to_items' => 'Back to items',
        ];

        register_taxonomy('saitems_taxonomy', ['saitem'], [
            'labels' => $taxonomy_labels,
            'hierarchical' => true, // false like tags, true like categories
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
        ]);

        $post_type_labels = [
            'name' => 'Animals',
            'singular_name' => 'animal',
            'add_new' => 'Add animal',
            'add_new_item' => 'Add new animal',
            'edit_item' => 'Edit animal',
            'new_item' => 'New animal',
            'view_item' => 'View animal',
            'search_items' => 'Search animal',
            'not_found' => 'Animal not found',
            'not_found_in_trash' => 'Animals not found in trash',
            'parent_item_colon' => '',
        ];

        $args = [
            'label' => 'Services Animals',
            'labels' => $post_type_labels,
            'description' => 'Services Animals..',
            'public' => false,
            'hierarchical' => false,
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'show_in_admin_bar' => true,
            'show_in_rest' => false,
            //'rest_base' => 'post_type',
            //'rest_controller_class' => 'WP_REST_Posts_Controller',
            'menu_position' => 40,
            'menu_icon' => 'dashicons-admin-tools',
            'capability_type' => 'post',
            'capabilities' => ['edit_post', 'read_post', 'delete_post', 'edit_posts', 'edit_others_posts',
                'delete_posts', 'publish_posts', 'read_private_posts', ],
            'map_meta_cap' => true,
            /*'supports' => [
                'title',
                'editor',
                'comments',
                'revisions',
                'trackbacks',
                'author',
                'excerpt',
                'page-attributes',
                'thumbnail',
                'custom-fields',
                'post-formats',
            ],*/
            'supports' => ['title', 'editor'],
            'register_meta_box_cb' => null, //'register_meta_box_cb' => [$this, 'sa_metabox_add'],
            'taxonomies' => 'saitems_taxonomy',
            'has_archive' => false,
            'rewrite' => true, //'rewrite' => ['slug' => 'saitem', 'with_front' => true],
            'slug' => 'saitem',
            'with_front' => true,
            'feeds' => true,
            'pages' => true,
            //'ep_mask' => EP_PERMALINK,
            //'query_var' => 'saitem',
            //'can_export' => true,
            //'delete_with_user' => false,
            '_builtin' => false,
            //'_edit_link' => 'post.php?post=%d',
        ];

        register_post_type('saitem', $args);
    }

    private function _fill_custom_fields()
    {
        $this->custom_fields = [
            'name' => 'text',
            'description' => 'text',
            'type' => 'text',
            'specie' => 'text',
            'breed' => 'text',
            'birthdate' => 'text',
            'created_at' => 'text',
            'updated_at' => 'text',
            'gender' => 'text',
            'adopter_id' => 'text',
            'internal_id' => 'text',
            'public_phone_number' => 'text',
            'public_email' => 'text',
            'share_counter' => 'text',
            'number_of_offspring' => 'text',
        ];
    }

    public function get_custom_fields()
    {
        return $this->custom_fields;
    }
}

// Do all..
ServicesAnimals::get_instance();
