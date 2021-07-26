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
include_once SA_PATH.'services-animals-gutenberg-block.php';

class ServicesAnimals
{
    private static $instance;

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
        add_action('init', [$this, 'create_custom_post_type_animal']);
        add_action('init', [$this, 'create_custom_post_type_service']);

        ServicesAnimalsBackendController::get_instance();
        ServicesAnimalsGutenbergBlock::get_instance();
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

    // The custom post types..
    public function create_custom_post_type_animal()
    {
        $category_labels = [
            'name' => 'Categories',
            'singular_name' => 'Category',
            'menu_name' => 'Categories',
            'all_items' => 'All categories',
            'edit_item' => 'Edit category',
            'view_item' => 'View category',
            'update_item' => 'Update category',
            'add_new_item' => 'Add category',
            'new_item_name' => 'New category name',
            'parent_item' => null,
            'parent_item_colon' => null,
            'search_items' => 'Search categories',
            'popular_items' => 'Popular categories',
            'separate_items_with_commas' => 'Separate items with commas',
            'add_or_remove_items' => 'Add or remove items',
            'choose_from_most_used' => 'Choose from most used',
            'not_found' => 'Not found',
            'back_to_items' => 'Back to items',
        ];

        register_taxonomy('saanimal_category', ['saanimal'], [
            'labels' => $category_labels,
            'hierarchical' => true, // false like tags, true like categories
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
        ]);

        $tag_labels = [
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

        register_taxonomy('saanimal_taxonomy', ['saanimal'], [
            'labels' => $tag_labels,
            'hierarchical' => false, // false like tags, true like categories
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
        ]);

        $post_type_labels = [
            'name' => 'Animals',
            'singular_name' => 'Animal',
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
            'label' => 'Animals',
            'labels' => $post_type_labels,
            'description' => 'Animals..',
            'public' => true,
            'hierarchical' => false,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'show_in_admin_bar' => true,
            'show_in_rest' => true,
            //'rest_base' => 'post_type',
            //'rest_controller_class' => 'WP_REST_Posts_Controller',
            'menu_position' => 40,
            'menu_icon' => 'dashicons-pets',
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
            'taxonomies' => ['saanimal_category', 'saanimal_taxonomy'],
            'has_archive' => true,
            'rewrite' => true,
            'slug' => 'saanimal',
            'with_front' => true,
            'feeds' => true,
            'pages' => true,
            //'ep_mask' => EP_PERMALINK,
            'query_var' => 'saitem',
            'can_export' => true,
            'delete_with_user' => false,
            //'_builtin' => true,
            //'_edit_link' => 'post.php?post=%d',
        ];

        register_post_type('saanimal', $args);
    }

    public function create_custom_post_type_service()
    {
        $category_labels = [
            'name' => 'Categories',
            'singular_name' => 'Category',
            'menu_name' => 'Categories',
            'all_items' => 'All categories',
            'edit_item' => 'Edit category',
            'view_item' => 'View category',
            'update_item' => 'Update category',
            'add_new_item' => 'Add category',
            'new_item_name' => 'New category name',
            'parent_item' => null,
            'parent_item_colon' => null,
            'search_items' => 'Search categories',
            'popular_items' => 'Popular categories',
            'separate_items_with_commas' => 'Separate items with commas',
            'add_or_remove_items' => 'Add or remove items',
            'choose_from_most_used' => 'Choose from most used',
            'not_found' => 'Not found',
            'back_to_items' => 'Back to items',
        ];

        register_taxonomy('saservice_category', ['saservice'], [
            'labels' => $category_labels,
            'hierarchical' => true, // false like tags, true like categories
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
        ]);

        $tag_labels = [
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

        register_taxonomy('saservice_taxonomy', ['saservice'], [
            'labels' => $tag_labels,
            'hierarchical' => false, // false like tags, true like categories
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
        ]);

        $post_type_labels = [
            'name' => 'Services',
            'singular_name' => 'Service',
            'add_new' => 'Add service',
            'add_new_item' => 'Add new service',
            'edit_item' => 'Edit service',
            'new_item' => 'New service',
            'view_item' => 'View service',
            'search_items' => 'Search service',
            'not_found' => 'Service not found',
            'not_found_in_trash' => 'Services not found in trash',
            'parent_item_colon' => '',
        ];

        $args = [
            'label' => 'Services',
            'labels' => $post_type_labels,
            'description' => 'Services..',
            'public' => true,
            'hierarchical' => true,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'show_in_admin_bar' => true,
            'show_in_rest' => true,
            //'rest_base' => 'post_type',
            //'rest_controller_class' => 'WP_REST_Posts_Controller',
            'menu_position' => 40,
            'menu_icon' => 'dashicons-id-alt',
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
            'taxonomies' => ['saservice_category', 'saservice_taxonomy'],
            'has_archive' => false,
            'rewrite' => true,
            'slug' => 'saservice',
            'with_front' => true,
            'feeds' => true,
            'pages' => true,
            //'ep_mask' => EP_PERMALINK,
            'query_var' => 'saservice',
            'can_export' => true,
            'delete_with_user' => false,
            //'_builtin' => false,
            //'_edit_link' => 'post.php?post=%d',
        ];

        register_post_type('saservice', $args);
    }

    public function get_fields_animals()
    {
        return [
            'name' => 'text',
            'description' => 'text',
            'type' => 'text',
            'specie' => 'text',
            'breed' => 'text',
            'birthdate' => 'text',
            'gender' => 'text',
            'number_of_offspring' => 'text',
        ];
    }

    public function get_fields_services()
    {
        return [
            'name' => ['text'],
            'description' => ['text'],
            'type' => [
                'choice',
                'choices' => [],
            ],
        ];
    }
}

// Do all..
ServicesAnimals::get_instance();
