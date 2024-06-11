<?php

class ValiAPIImportSettings
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function add_admin_menu()
    {
        add_menu_page(
            __('Vali API Settings', 'vali-api-import'),
            'Vali API',
            'manage_options',
            'vali_api',
            array($this, 'settings_page'),
            'dashicons-database-import'
        );
    }

    public function settings_page()
    {
        ?>
        <div class="wrap">
            <h1><?php _e('Vali API Settings', 'vali-api-import'); ?></h1>
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

        add_settings_section('vali_api_settings_section', __('API Settings', 'vali-api-import'), null, 'vali_api');

        add_settings_field(
            'vali_api_token',
            __('API Token', 'vali-api-import'),
            array($this, 'settings_field_token'),
            'vali_api',
            'vali_api_settings_section'
        );

        add_settings_field(
            'vali_api_full_endpoint',
            __('Full Data Endpoint', 'vali-api-import'),
            array($this, 'settings_field_full_endpoint'),
            'vali_api',
            'vali_api_settings_section'
        );

        add_settings_field(
            'vali_api_basic_endpoint',
            __('Basic Data Endpoint', 'vali-api-import'),
            array($this, 'settings_field_basic_endpoint'),
            'vali_api',
            'vali_api_settings_section'
        );

        add_settings_field(
            'vali_api_all_endpoint',
            __('All Data Endpoint', 'vali-api-import'),
            array($this, 'settings_field_all_endpoint'),
            'vali_api',
            'vali_api_settings_section'
        );
    }

    public function settings_field_token()
    {
        $value = get_option('vali_api_token', '');
        echo '<input type="password" name="vali_api_token" value="' . esc_attr($value) . '" style="width:30%;" />';
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

    public function settings_field_all_endpoint()
    {
        $basicEndpoint = site_url('/vali-api-fetch-all');
        echo '<p>' . esc_html($basicEndpoint) . '</p>';
    }
}

new ValiAPIImportSettings();
