<?php

class ValiAPIImportSettings
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
    }

    public function add_admin_menu()
    {
        add_menu_page(
            'Vali API Settings',
            'Vali API',
            'manage_options',
            'vali_api',
            array($this, 'settings_page'),
            'dashicons-admin-generic'
        );
    }

    public function settings_page()
    {
        ?>
        <div class="wrap">
            <h1>Vali API Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('vali_api_settings_group');
                do_settings_sections('vali_api');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function register_settings()
    {
        register_setting('vali_api_settings_group', 'vali_api_token');
        register_setting('vali_api_settings_group', 'vali_api_data_format');

        add_settings_section('vali_api_settings_section', 'API Settings', null, 'vali_api');

        add_settings_field(
            'vali_api_token',
            'API Token',
            array($this, 'settings_field_token'),
            'vali_api',
            'vali_api_settings_section'
        );

        add_settings_field(
            'vali_api_data_format',
            'Data Format',
            array($this, 'settings_field_data_format'),
            'vali_api',
            'vali_api_settings_section'
        );

        add_settings_field(
            'vali_api_full_endpoint',
            'Full Data Endpoint',
            array($this, 'settings_field_full_endpoint'),
            'vali_api',
            'vali_api_settings_section'
        );

        add_settings_field(
            'vali_api_basic_endpoint',
            'Basic Data Endpoint',
            array($this, 'settings_field_basic_endpoint'),
            'vali_api',
            'vali_api_settings_section'
        );
    }

    public function settings_field_token()
    {
        $value = get_option('vali_api_token', '');
        echo '<input type="password" name="vali_api_token" value="' . esc_attr($value) . '" style="width:30%;" />';
    }

    public function settings_field_data_format()
    {
        $format = get_option('vali_api_data_format', 'xml');
        ?>
        <select name="vali_api_data_format">
            <option value="xml" <?php selected($format, 'xml'); ?>>XML</option>
            <option value="json" <?php selected($format, 'json'); ?>>JSON</option>
        </select>
        <?php
    }

    public function settings_field_full_endpoint()
    {
        $fullEndpoint = site_url('/vali-api-fetch-full/?category_ids=');
        echo '<p>' . esc_html($fullEndpoint) . '</p>';
    }

    public function settings_field_basic_endpoint()
    {
        $basicEndpoint = site_url('/vali-api-fetch-basic/?category_ids=');
        echo '<p>' . esc_html($basicEndpoint) . '</p>';
    }

    public function enqueue_admin_styles()
    {
        wp_enqueue_style('vali_api_admin_styles', plugin_dir_url(__FILE__) . 'css/vali-admin-styles.css');
    }
}

new ValiAPIImportSettings();
