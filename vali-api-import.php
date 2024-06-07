<?php
/*
Plugin Name: Vali API Import
Description: A plugin to import product data from Vali API and integrate with WP All Import PRO.
Version: 1.0
Author: Georgi Georgiev
*/

if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'includes/settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/api-handler.php';

class ValiAPIImportFull
{
    private $api;
    private $categories = [];

    public function __construct()
    {
        $apiToken = get_option('vali_api_token', '');
        $useXML = get_option('vali_api_data_format', 'xml') === 'xml';
        $this->api = new ValiAPIImport($apiToken, $useXML);

        add_action('init', array($this, 'register_api_endpoints'));
        add_action('query_vars', array($this, 'add_query_vars'));
        add_action('template_redirect', array($this, 'template_redirect'));
        $this->fetch_categories();

        // Add settings link to plugins page
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_settings_link'));
    }

    public function add_settings_link($links)
    {
        $settings_link = '<a href="admin.php?page=vali_api">' . __('Settings') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    public function register_api_endpoints()
    {
        add_rewrite_rule('^vali-api-fetch-full/?', 'index.php?vali_api_fetch_full=1', 'top');
        add_rewrite_rule('^vali-api-fetch-basic/?', 'index.php?vali_api_fetch_basic=1', 'top');
    }

    public function add_query_vars($vars)
    {
        $vars[] = 'vali_api_fetch_full';
        $vars[] = 'vali_api_fetch_basic';
        return $vars;
    }

    public function template_redirect()
    {
        if (get_query_var('vali_api_fetch_full')) {
            $this->fetch_and_output_data(true);
            exit;
        }

        if (get_query_var('vali_api_fetch_basic')) {
            $this->fetch_and_output_data(false);
            exit;
        }
    }

    private function fetch_categories()
    {
        $response = $this->api->getCategories();

        if ($this->api->errorCode != 200) {
            error_log("Vali API request error {$this->api->errorCode}: $response");
            return;
        }

        $dataFormat = get_option('vali_api_data_format', 'xml');

        if ($dataFormat === 'xml') {
            $xml = new SimpleXMLElement($response);

            foreach ($xml->category as $category) {
                $id = (int)$category->id;
                foreach ($category->name->item as $name) {
                    if ($name->language_code == 'bg') {
                        $this->categories[$id] = (string)$name->text;
                        break;
                    }
                }
            }
        } else {
            $categories = json_decode($response);
            foreach ($categories as $category) {
                $id = $category->id;
                foreach ($category->name as $name) {
                    if (isset($name->language_code) && $name->language_code == 'bg') {
                        $this->categories[$id] = $name->text;
                        break;
                    }
                }
            }
        }
    }

    public function fetch_and_output_data($full)
    {
        $categoryIds = isset($_GET['category_ids']) ? array_map('intval', explode(',', $_GET['category_ids'])) : [];
        if (empty($categoryIds)) {
            wp_send_json_error("Invalid category IDs", 400);
        }

        $combinedProducts = [];
        $dataFormat = get_option('vali_api_data_format', 'xml');

        foreach ($categoryIds as $categoryId) {
            if (!$categoryId) {
                continue;
            }

            $data = $this->api->getProductsByCategory($categoryId, $full);

            if ($this->api->errorCode != 200) {
                error_log("Vali API request error {$this->api->errorCode}: $data");
                continue;
            }

            if ($dataFormat === 'xml') {
                $data = $this->remove_items_with_language_code($data, 'en');
                $categoryName = isset($this->categories[$categoryId]) ? $this->categories[$categoryId] : $categoryId;
                $data = str_replace("<category>{$categoryId}</category>", "<category>{$categoryName}</category>", $data);
                $xml = new SimpleXMLElement($data);
                foreach ($xml->product as $product) {
                    $combinedProducts[] = $product->asXML();
                }
            } else {
                $products = json_decode($data);
                foreach ($products as $product) {
                    $product->category = isset($this->categories[$categoryId]) ? $this->categories[$categoryId] : $categoryId;
                    $combinedProducts[] = json_encode($product);
                }
            }
        }

        if ($dataFormat === 'xml') {
            $combinedData = '<?xml version="1.0" encoding="UTF-8"?><products>';
            $combinedData .= implode('', $combinedProducts);
            $combinedData .= '</products>';
            header('Content-Type: application/xml');
        } else {
            $combinedData = '[' . implode(',', $combinedProducts) . ']';
            header('Content-Type: application/json');
        }

        echo $combinedData;
    }

    private function remove_items_with_language_code($xmlData, $languageCode)
    {
        $xml = new SimpleXMLElement($xmlData);
        foreach ($xml->xpath("//item[language_code='{$languageCode}']") as $item) {
            unset($item[0]);
        }
        return $xml->asXML();
    }
}

new ValiAPIImportFull();
