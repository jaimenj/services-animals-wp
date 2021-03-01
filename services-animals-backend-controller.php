<?php

defined('ABSPATH') or die('No no no');

class ServicesAnimalsBackendController
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
        add_action('admin_menu', [$this, 'add_admin_page']);

        // Metabox for custom post type..
        add_action('add_meta_boxes', [$this, 'sa_metabox_add']);
        add_action('save_post', [$this, 'sa_metabox_save']);
    }

    public function add_admin_page()
    {
        $page_title = 'Services Animals Tools';
        $menu_title = $page_title;
        $capability = 'administrator';
        $menu_slug = 'services-animals';
        $function = [$this, 'sa_main_admin_controller'];
        $position = null;

        add_management_page($page_title, $menu_title, $capability, $menu_slug, $function, $position);
    }

    public function sa_main_admin_controller()
    {
        $submitting = false;
        foreach ($_REQUEST as $key => $value) {
            if (preg_match('/submit/', $key)) {
                $submitting = true;
            }
        }

        // Security control
        if ($submitting) {
            if (!isset($_REQUEST['sa_nonce'])) {
                $saSms = '<div id="message" class="notice notice-error is-dismissible"><p>ERROR: nonce field is missing.</p></div>';
            } elseif (!wp_verify_nonce($_REQUEST['sa_nonce'], 'sa')) {
                $saSms = '<div id="message" class="notice notice-error is-dismissible"><p>ERROR: invalid nonce specified.</p></div>';
            } else {
                /*
                 * Handling actions..
                 */
                if (isset($_REQUEST['sa-submit'])) {
                    $saSms = $this->_save_main_configs();
                } else {
                    $saSms = '<div id="message" class="notice notice-success is-dismissible"><p>Cannot understand submitting!</p></div>';
                }
            }
        }

        // Main options..
        $quantity_per_batch = get_option('sa_quantity_per_batch');
        $time_between_batches = get_option('sa_time_between_batches');
        $current_columns_to_show = get_option('sa_current_columns_to_show');

        include SA_PATH.'view/main.php';
    }

    private function _save_main_configs()
    {
        update_option('sa_quantity_per_batch', intval($_REQUEST['quantity_per_batch']));
        update_option('sa_time_between_batches', intval($_REQUEST['time_between_batches']));

        return '<div id="message" class="notice notice-success is-dismissible"><p>Main options saved!</p></div>';
    }

    public function sa_metabox_add()
    {
        add_meta_box(
            'saitem_metabox_id',
            'Services Animals',
            [$this, 'sa_metabox_view'],
            'saitem',
            'normal',
            'default'
        );
    }

    public function sa_metabox_view($post)
    {
        $sa_custom_fields = ServicesAnimals::get_instance()->get_custom_fields();
        foreach ($sa_custom_fields as $key => $type) {
            $$key = get_post_meta($post->ID, $key, true);
        }

        wp_nonce_field('sa_nonce_metabox_action', 'sa_nonce_metabox_name');

        include SA_PATH.'view/metabox.php';
    }

    public function sa_metabox_save($post_id)
    {
        if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        or !isset($_POST['sa_nonce_metabox_name'])
        or !wp_verify_nonce($_POST['sa_nonce_metabox_name'], 'sa_nonce_metabox_action')
        or !current_user_can('edit_post')) {
            return;
        }

        $sa_custom_fields = ServicesAnimals::get_instance()->get_custom_fields();
        foreach ($sa_custom_fields as $key => $type) {
            if ('text' == $type) {
                update_post_meta($post_id, $key, sanitize_text_field($_POST[$key]));
            }
        }
    }
}
