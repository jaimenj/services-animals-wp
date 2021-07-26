<?php

defined('ABSPATH') or die('No no no');

class ServicesAnimalsGutenbergBlock
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
        add_action('enqueue_block_editor_assets', [$this, 'saanimal_register_block']);
        add_action('init', [$this, 'saservice_register_block']);
    }

    public function saanimal_register_block()
    {
        // automatically load dependencies and version
        //$asset_file = include plugin_dir_path(__FILE__).'view/index.asset.php';

        wp_enqueue_script(
            'saanimal-block',
            plugins_url('lib/saanimal-block.js', __FILE__),
            ['wp-blocks', 'wp-element', 'wp-editor'], //$asset_file['dependencies'],
            //filemtime(plugin_dir_path(__FILE__).'lib/saanimal-block.js')
        );

        /*register_block_type('gutenberg-examples/example-01-basic-esnext', [
            'editor_script' => 'gutenberg-examples-01-esnext',
        ]);*/
    }

    public function saservice_register_block()
    {
    }
}
