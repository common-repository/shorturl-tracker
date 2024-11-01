<?php
/*
 * Plugin Name: ShortUrl Tracker
 * Description: ShortUrl Tracker allows you to create ShortLink directly on your WordPress Administration. Track Clicks, Devices, Sales, Top Link etc... Create Campaigns an attached all links you wants.
 * Author: @BuildYourWeb
 * Author URI: https://buildyourweb.fr
 * Plugin URI: https://buildyourweb.fr/plugin-shorturl-tracker/
 * Text Domain: shorturl-tracker
 * Domain Path: /languages
 * Version: 1.0.0
 * Requires at least: 4.9
 * Tested up to: 6.1.1
 * WC requires at least: 5.4
 * WC tested up to: 7.2.2
 * Requires PHP: 7.4
 * License: GPLv2 or later
 * Copyright (C) 2022  Build Your Web (email: contact@buildyourweb.fr)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.

*/

namespace ShortUrlTracker\Plugin;

use ArrayObject;
use WP_Query;

if (!defined('ABSPATH')) die();

if (!class_exists('Sut_Tracker')) {

    class Sut_Tracker {

        /**
         *
         */
        public function __construct() {

            register_activation_hook( __FILE__, array( $this, 'sut_byw_install' ) );
            add_action( 'admin_menu', array( $this, 'sut_byw_register_link_page' ) );
            add_action( 'admin_print_styles', array( $this, 'sut_byw_css' ) );
            add_action( 'admin_print_scripts', array( $this, 'sut_byw_script_js' ) );
            add_action( 'template_redirect', array( $this, 'sut_byw_set_cookie' ) );
            add_action( 'woocommerce_checkout_create_order', array( $this, 'sut_byw_checkout_create_order'), 20, 2 );
            add_action( 'rest_api_init', array( $this, 'sut_byw_create_api_posts_meta_field' ) );
            add_action( 'wp_ajax_sut_byw_create_short_link_ajax', array( $this, 'sut_byw_create_short_link_ajax' ) );
            add_filter( 'https_ssl_verify', '__return_false' );
            add_action( 'init', array($this, 'my_plugin_init') );
            $this->constants();
        }

        /**
         * @return void
         */
        public function constants(): void
        {
            if ( ! defined( 'SUT_BYW_VERSION' ) ) {
                define( 'SUT_BYW_VERSION', '1.0.0' );
            }
            if ( ! defined( 'SUT_BYW_SLICK_VERSION' ) ) {
                define( 'SUT_BYW_SLICK_VERSION', '1.8.1' );
            }
            if ( ! defined( 'SUT_BYW_PURL' ) ) {
                define( 'SUT_BYW_PURL', plugin_dir_url( __FILE__ ) );
            }
            if ( ! defined( 'SUT_BYW_API' ) ) {
                define( 'SUT_BYW_API', 'https://api.buildyourweb.fr/wp-json/shorturl-tracker' );
            }
            if ( ! defined( 'SUT_BYW_TOKEN' ) ) {
                if ($this->sut_byw_get_autorization_value( 'licence_key' )) {
                    define('SUT_BYW_TOKEN', $this->sut_byw_get_autorization_value('licence_key'));
                    if ( ! defined( 'SUT_BYW_LEVEL' ) ) {
                        define( 'SUT_BYW_LEVEL', $this->sut_byw_get_autorization_value('license_level') );
                    }
                } else {
                    define('SUT_BYW_TOKEN', '');
                }
            }
            if ( ! defined( 'SUT_BYW_COOKIE' ) ) {
                if ($this->sut_byw_get_autorization_value( 'cookie_day' )) {
                    define('SUT_BYW_COOKIE', $this->sut_byw_get_autorization_value('cookie_day'));
                }
            }
        }

        /**
         * @return void
         */
        public function my_plugin_init(): void
        {
            load_plugin_textdomain( 'shorturl-tracker', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
        }

        /**
         * @return void
         */
        public function sut_byw_install(): void
        {
            global $wpdb;
            $table_site = $wpdb->prefix . 'shorturl_tracker';
            if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_site'" ) != $table_site ) {
                $sql = "CREATE TABLE `$table_site`( 
                 `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,  
                 `label` TEXT NOT NULL, 
                 `value` TEXT NOT NULL,
                 `status` TEXT NOT NULL,   
                 `level` TEXT NOT NULL          
                 )
              ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;";
                require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
                dbDelta( $sql );
            }
        }

        /**
         * @return void
         */
        function sut_byw_create_api_posts_meta_field(): void
        {
            register_rest_field( 'product_cat', 'variation_products', array(
                'get_callback' => function ( $data ) {
                    return $this->sut_byw_get_category_variations( $data['id'] );
                },
            ) );
            register_rest_field( 'categories', 'variation_products', array(
                'get_callback' => function ( $data ) {
                    return $this->sut_byw_get_category_variations( $data['id'] );
                },
            ) );
            register_rest_field( 'post', 'meta', array(
                'get_callback' => function ( $data ) {
                    return get_post_meta( $data['id'], '', '' );
                },
            ) );
            register_rest_field( 'product', 'meta', array(
                'get_callback' => function ( $data ) {
                    return maybe_unserialize( get_post_meta( $data['id'], '_product_attributes', true ) );
                },
            ) );
            register_rest_field( 'product', 'variation_products', array(
                'get_callback' => function ( $data ) {
                    return $this->sut_byw_get_all_variations( $data['id'] );
                },
            ) );
        }

        /**
         * @return void
         */
        public function sut_byw_register_link_page(): void
        {
            add_menu_page( 'ShortUrl Tracker', 'ShortURL Tracker', 'manage_options', 'sut-home', array(
                $this,
                'sut_byw_dashboard'
            ), 'dashicons-admin-links', 30 );
            add_submenu_page( 'sut-home', __( 'Dashboard', 'shorturl-tracker' ), __( 'Dashboard', 'shorturl-tracker' ), 'manage_options', 'sut-home', array(
                $this,
                'sut_byw_dashboard'
            ), 10 );
            add_submenu_page( 'sut-home', __( 'Create Shortlink', 'shorturl-tracker' ), __( 'Create Shortlink', 'shorturl-tracker' ), 'manage_options', 'sut-create-link', array(
                $this,
                'sut_byw_link_html'
            ), 10 );
            add_submenu_page( 'sut-home', __( 'Create Campaign', 'shorturl-tracker' ), __( 'Create Campaign', 'shorturl-tracker' ), 'manage_options', 'sut-create-campaign', array(
                $this,
                'sut_byw_create_campaign_html'
            ), 10 );
            add_submenu_page( 'sut-home', __( 'All Campaigns', 'shorturl-tracker' ), __( 'All Campaigns', 'shorturl-tracker' ), 'manage_options', 'sut-get-campaign', array(
                $this,
                'sut_byw_get_campaign_html'
            ), 10 );
            add_submenu_page( 'sut-home', __( 'All Shortlinks', 'shorturl-tracker' ), __( 'All Shortlinks', 'shorturl-tracker' ), 'manage_options', 'sut-historic', array(
                $this,
                'sut_byw_historic'
            ), 20 );
            add_submenu_page( 'sut-home', __( 'Settings', 'shorturl-tracker' ), __( 'Settings', 'shorturl-tracker' ), 'manage_options', 'sut-settings', array(
                $this,
                'sut_byw_settings'
            ), 20 );
        }

        /**
         * @return void
         */
        public function sut_byw_css(): void
        {
            if (strpos($_SERVER['REQUEST_URI'], 'sut-')) {
                wp_register_style('shorturl-tracker', SUT_BYW_PURL . 'css/sut-tracker.css', array(), SUT_BYW_VERSION);
                wp_enqueue_style('shorturl-tracker');
                wp_register_style('slick', SUT_BYW_PURL . 'css/slick/slick.css', array(), SUT_BYW_SLICK_VERSION);
                wp_enqueue_style('slick');
            }
        }

        /**
         * @return void
         */
        function sut_byw_script_js(): void
        {

            if ( strpos( $_SERVER['QUERY_STRING'],'sut-create-link' ) || strpos( $_SERVER['QUERY_STRING'], 'sut-home' ) ) {
                wp_register_script( 'tracker', SUT_BYW_PURL . 'js/sut-tracker.js', array(), '1.0', true );
                wp_enqueue_script( 'tracker' );
                wp_localize_script( 'tracker', 'wpApiSettings', array(
                    'root' => esc_url_raw( rest_url() ),
                    'nonce' => wp_create_nonce( 'wp_rest' ),
                    'add' => __( 'Add', 'shorturl-tracker' ),
                    'remove' => __( 'Remove', 'shorturl-tracker' ),
                    'copyShort' => __( '<p>Copy Link</p>', 'shorturl-tracker' ),
                    'imgSocial' => $this->sut_byw_get_social_img(),
                    'copiedShort' => __( '<p>Copied</p><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check"><polyline points="20 6 9 17 4 12"></polyline></svg>', 'shorturl-tracker' )
                ) );
            } elseif ( strpos($_SERVER['QUERY_STRING'],'sut-historic') ) {
                wp_register_script( 'historic', SUT_BYW_PURL . 'js/sut-historic.js', array(), '1.0', true );
                wp_enqueue_script( 'historic', SUT_BYW_PURL . 'js/sut-historic.js' );
                wp_localize_script( 'historic', 'wpApiSettings', array(
                    'root' => esc_url_raw( rest_url() ),
                    'nonce' => wp_create_nonce( 'wp_rest' ),
                    'imgSocial' => $this->sut_byw_get_social_img()
                ) );
            }
        }

        /**
         * @return void
         */
        public function sut_byw_link_html(): void
        {

            require_once( 'link-admin.php' );

        }

        /**
         * @return void
         */
        public function sut_byw_settings(): void
        {

            require_once( 'settings.php' );

        }

        /**
         * @return void
         */
        public function sut_byw_create_campaign_html(): void
        {

            require_once( 'create_campaign.php' );

        }

        /**
         * @return void
         */
        public function sut_byw_get_campaign_html(): void
        {

            require_once( 'get_campaign.php' );

        }

        /**
         * @return void
         */
        public function sut_byw_historic(): void
        {

            require_once( 'historic.php' );

        }

        /**
         * @return void
         */
        public function sut_byw_dashboard(): void
        {

            require_once( 'dashboard.php' );

        }

        /**
         * @return void
         */
        public function sut_byw_set_cookie(): void
        {
            $url = $_SERVER['REQUEST_URI'];
            $day = $this->sut_byw_get_cookie_lifetime();
            if ( strpos( $url, 'byw=' ) ) {
                $array_cookie = array(
                    'expires' => time() + 60 * 60 * 24 * $day,
                    'path' => '/',
                    'secure' => true,
                    'samesite' => 'Strict'
                );
                $url_components = parse_url( $url );
                parse_str( $url_components['query'], $params );
                setcookie( '_byw_li', $params['byw'], $array_cookie );
            }
        }

        /**
         * function to set the lifetime cookie
         * @return int
         */
        public function sut_byw_get_cookie_lifetime(): int {
            global $wpdb;
            $table_site = $wpdb->prefix . 'shorturl_tracker';
            $sql = $wpdb->prepare( "SELECT * FROM " . $table_site . " WHERE label=%s LIMIT 1", 'cookie_day' );
            $map = $wpdb->get_results( $sql );
            if ( ! empty( $map ) ) {
                return $map[0]->value;
            } else {
                return 7;
            }
        }

        public function sut_byw_format_date($date): string
        {
            return substr( $date, 8, 2 ) . '/' . substr( $date, 5, 2 ) . '/' . substr( $date, 0, 4 );
        }

        /**
         * @param $order
         *
         * @return void
         */
        public function sut_byw_checkout_create_order( $order ): void
        {
            if (isset($_COOKIE['_byw_li'])) {
                $origin = sanitize_text_field(htmlentities( $_COOKIE['_byw_li'] ));
                $order->update_meta_data( '_byw_tracking', urldecode( $origin ) );
            }
        }

        /**
         * @param $id
         *
         * @return array
         */
        public function sut_byw_get_category_variations( $id ):array {
            $category = get_term_by( 'id', $id, 'product_cat' );
            $query_args = array(
                'status' => 'publish',
                'limit' => -1,
                'category' => array($category->slug),
            );

            $data = array();
            $request = wc_get_products($query_args);
            if ($request != null) {
                foreach( $request as $product ){
                    foreach( $product->get_attributes() as $taxonomy => $attribute ){
                        if ($attribute->get_terms() != NULL) {
                            foreach ($attribute->get_terms() as $term) {
                                $data[$taxonomy][] = $term->slug;
                            }
                        }
                    }
                }
            }
            return $data;
        }

        /**
         * @param $id
         *
         * @return array
         */
        public function sut_byw_get_all_variations( $id ):array {
            if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
                global $product;
                $product = wc_get_product($id);
                $classes = $product->get_type();
                if (str_contains($classes, 'variable')) {
                    $variations = $product->get_available_variations();
                } else {
                    $variations = [];
                }
                return $variations;
            } else {
                return array();
            }
        }

        public function sut_byw_create_short_link_ajax(): void
        {
            if ( isset( $_REQUEST['data']['nonce'] ) && wp_verify_nonce( sanitize_text_field($_REQUEST['data']['nonce']), 'wp_rest' ) ) {
                if ($this->sut_byw_check_user()) {
                    $data = sanitize_url($_REQUEST['data']['json']['url']);
                    $param = ((isset($_REQUEST['data']['json']['param'])) ? $this->sut_byw_sanitize_array($_REQUEST['data']['json']['param']) :  '');
                    $name = sanitize_text_field($_REQUEST['data']['json']['name']);
                    $campaign_id = sanitize_text_field($_REQUEST['data']['json']['campaignId']);
                    $favorite = sanitize_text_field($_REQUEST['data']['json']['isFavorite']);
                    $token = sanitize_text_field(SUT_BYW_TOKEN);
                    $url = SUT_BYW_API . '/v1/generate';
                    $json = array(
                        'url' => $data,
                        'param' => $param,
                        'description' => $name,
                        'cookie_lifetime' => SUT_BYW_COOKIE,
                        'isFavorite' => $favorite,
                        'campaign_id' => $campaign_id
                    );
                    $payload  = json_encode( $json );
                    error_log($payload);
                    $args     = array(
                        'timeout' => 10,
                        'body' => $payload,
                        'headers' => array(
                            'Origin' => get_home_url(),
                            'Content-Type' => 'application/json',
                            'Authorization' => $token
                        )
                    );
                    $response = wp_remote_post( $url, $args );
                    $answer = json_decode( wp_remote_retrieve_body( $response ) );
                    if ( wp_remote_retrieve_response_code( $response ) == '201' ) {
                        wp_send_json_success($answer, 201 );
                    } else if ( wp_remote_retrieve_response_code( $response ) == '400' ) {
                        wp_send_json_error($answer, 200 );
                    } else if ( wp_remote_retrieve_response_code( $response ) == '403' ) {
                        wp_send_json_error($answer, 200 );
                    }
                } else {
                    wp_send_json_error( 'Not Allowed To access this service',  401 );
                }
            } else {
                wp_send_json_error( 'Not Allowed To access this service',  401 );
            }
        }

        /**
         * @param $input
         * @return array
         */
        public function sut_byw_sanitize_array($input): array
        {

            $new_input = $loop_array = array();

            foreach ( $input as $key => $val ) {
                foreach ($val as $param => $param_value) {
                    $loop_array[sanitize_text_field( $param )] = sanitize_text_field( $param_value );
                }
                $new_input[ $key ] = $loop_array;
            }
            return $new_input;
        }

        /**
         * @return bool
         */
        public function sut_byw_check_user(): bool {
            if ( is_user_logged_in() && current_user_can( 'editor' ) || current_user_can( 'administrator' ) ) {
                return true;
            } else {
                return false;
            }
        }
        /**
         * @param $start
         * @param $end
         *
         * @return string|void
         */
        public function sut_byw_get_all_campaign( $start, $end ) {
            $url = SUT_BYW_API . '/v1/campaign?startdate=' . $start . '&enddate=' . $end;
            $args = array(
                'timeout' => 10,
                'headers' => array(
                    'Origin' => get_home_url(),
                    'Content-Type' => 'application/json',
                    'Authorization' => SUT_BYW_TOKEN
                )
            );
            $response = wp_remote_get( $url, $args );
            $answer   = json_decode( wp_remote_retrieve_body( $response ) );
            $response_code = wp_remote_retrieve_response_code( $response );
            if ( $response_code == '200' ) {
                return $answer;
            } else {
                switch ( $response_code ) {
                    case '401':
                        $error_msg = __( 'Invalid License', 'shorturl-tracker' );
                        break;
                    case '400':
                        $error_msg = __( 'Bad Request', 'shorturl-tracker' );
                        break;
                    default:
                        $error_msg = __( 'Sorry, an error occurred. Please reload the page', 'shorturl-tracker' );
                        break;
                }
                return $error_msg;
            }
        }
        public function sut_byw_get_campaign_details($id, $start, $end) {
            $url      = SUT_BYW_API . '/v1/campaign/'.$id.'?startdate=' . $start . '&enddate=' . $end;
            $args     = array(
                'timeout' => 10,
                'headers' => array(
                    'Origin' => get_home_url(),
                    'Content-Type' => 'application/json',
                    'Authorization' => SUT_BYW_TOKEN
                )
            );
            $date = [];
            $response = wp_remote_get( $url, $args );
            $answer = json_decode( wp_remote_retrieve_body( $response ) );
            $http_response = wp_remote_retrieve_response_code( $response );
            if ( $http_response == '200' ) {
                foreach ($answer->token as $token) {
                    $data_sales = $this->sut_byw_get_sales($token);
                    if (isset($data_sales['date'])) {
                        foreach ($data_sales['date'] as $key => $value) {
                            if (in_array($key, $date)) {
                                $date->$key->amount += $value['amount'];
                                $key['count'] += $value['count'];
                            } else {
                                $data_value = array(
                                    'amount' => $value['amount'],
                                    'count' => $value['count']
                                );
                                $date[$key] = $data_value;
                            }
                        }
                    }
                }
                $sales_list = array(
                    'date' => $date
                );
                if ( isset($sales_list['date']) ) {
                    foreach ($sales_list['date'] as $date_purchase => $key) {
                        if (property_exists($answer->date, $date_purchase)) {
                            $answer->date->$date_purchase->purchase_amount += $key['amount'];
                            $answer->date->$date_purchase->nb_purchase += $key['count'];
                            $answer->total_amount += $key['amount'];
                            $answer->total_purchase += $key['count'];
                        }
                    }
                }
                return $answer;
            } else {
                switch ( $http_response ) {
                    case '401':
                        $error_id = "401";
                        $error_msg = __( 'Invalid License', 'shorturl-tracker' );
                        break;
                    case '400':
                        $error_id = "400";
                        $error_msg = __( 'Bad Request', 'shorturl-tracker' );
                        break;
                    default:
                        $error_id = "500";
                        $error_msg = __( 'Sorry, an error occurred. Please reload the page', 'shorturl-tracker' );
                        break;
                }
                return array(
                    'code' => $error_id,
                    'message' => $error_msg
                );
            }
        }
        public function sut_byw_check_valid_date($date): bool
        {
            $tempDate = explode('-', $date);
            return checkdate($tempDate[1], $tempDate[2], $tempDate[0]);
        }
        /**
         * function to generate html code for each campaign
         *
         * @param $response
         * @param $datedebut
         * @param $datefin
         * @return string
         */
        public function sut_byw_format_campaign_data( $response, $datedebut, $datefin ): string
        {
            if (isset($response->campaign_list)) {
                $data = $response->campaign_list;
                $html = '<div class="historic-tab">';
                foreach ($data as $link) {
                    $html .= '<div class="data-link">';
                    $html .= '<div class="left-part"><h4>' . __('Created the', 'shorturl-tracker') . ' <span class="in-focus">' . $this->sut_byw_format_date($link->created_date) . '</span></h4>';
                    $html .= '<h2><a href="' . esc_url(get_admin_url() . 'admin.php?page=sut-get-campaign&campaign_id=' . $link->campaign_id . '&startdate=' . $datedebut . '&enddate=' . $datefin) . '"><span class="in-focus">' . htmlspecialchars_decode($link->campaign_name) . '</span></a></h2>';
                    $html .= '<h3><a href="' . esc_url(get_admin_url() . 'admin.php?page=sut-get-campaign&campaign_id=' . $link->campaign_id . '&startdate=' . $datedebut . '&enddate=' . $datefin) . '">' . htmlspecialchars_decode($link->campaign_description) . '</a></h3></div>';
                    $html .= '<div class="bottom-left-part"><a href="' . esc_url(get_admin_url() . 'admin.php?page=sut-create-link&campaign_id=' . $link->campaign_id) . '">+ ' . __('add Link', 'shorturl-tracker') . '</a></div>';
                    $html .= '<div class="right-part"><a href="' . esc_url(get_admin_url() . 'admin.php?page=sut-get-campaign&campaign_id=' . $link->campaign_id . '&startdate=' . $datedebut . '&enddate=' . $datefin) . '">' . __('Details', 'shorturl-tracker') . '</a></div>';
                    $html .= '<div class="favorite-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="' . (!$link->favorite ? 'none' : 'currentColor') . '" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-heart"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg></div>';
                    $html .= '</div>';
                }
                $html .= '</div>';

                return $html;
            } else {
                return '';
            }
        }
        /**
         * @param $token
         *
         * @return array
         */
        public function sut_byw_get_sales( $token ): array {
            $args = array(
                'post_type' => 'shop_order',
                'post_status' => array( 'wc-completed', 'wc-processing' ),
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'key' => '_byw_tracking',
                        'value' => $token,
                        'compare' => 'LIKE'
                    )
                )
            );
            $purchase_per_date = [];
            $query = new WP_Query( $args );
            if ( $query->have_posts() ) {
                $count = $total_amount = 0;
                $currency = get_woocommerce_currency_symbol();
                while ( $query->have_posts() ) {
                    $query->the_post();
                    $purchase_amount = get_post_meta( get_the_ID(), '_order_total', true );
                    $total_amount += $purchase_amount;
                    $count ++;
                    $date = date('Y-m-d', get_post_meta( get_the_ID(), '_date_paid', true ));
                    if (!array_key_exists($date, $purchase_per_date)) {
                        $purchase_per_date[$date]['amount'] =  $purchase_amount;
                        $purchase_per_date[$date]['count'] = 1;
                    } else {
                        $purchase_per_date[$date]['amount'] += $purchase_amount;
                        $purchase_per_date[$date]['count'] += 1;
                    }
                }
                return array(
                    'total' => number_format( (float)$total_amount, 2, ',', ' ' ),
                    'average' => number_format( (float)$total_amount / $count, 2, ',', ' ' ),
                    'count' => $count,
                    'currency' => $currency,
                    'date' => (($purchase_per_date != "") ? $purchase_per_date : null)
                );
            } else {
                return array();
            }
        }
        /**
         * function to generate html code for each link
         *
         * @param $response
         * @param $datedebut
         * @param $datefin
         *
         * @return string
         */
        function sut_byw_format_data( $response, $datedebut, $datefin ): string {
            $data = $response->response;
            $html = '<div class="historic-tab">';
            foreach ( $data as $link ) {
                $html .= '<div class="data-link">';
                $html .= '<div class="left-part"><h4>' . __( 'Created the', 'shorturl-tracker' ) . ' <span class="in-focus">' . $this->sut_byw_format_date($link->created_date) . '</span></h4>';
                $html .= '<h2><a href="https://byw.li/' . $link->shortcode . '" target="_blank">byw.li/<span class="in-focus">' . $link->shortcode . '</span></a></h2>';
                $html .= '<h3><a href="' . $link->url . '" target="_blank">' . $link->url . '</a></h3></div>';
                if (SUT_BYW_LEVEL != 'free') {
                    if (isset($link->campaign_id) && $link->campaign_id != "") {
                        $html .= '<div class="bottom-left-part"><a href="' . get_admin_url() . 'admin.php?page=sut-get-campaign&campaign_id=' . $link->campaign_id . '&startdate=' . $datedebut . '&enddate=' . $datefin . '">' . __('Go to Campaign', 'shorturl-tracker') . '</a></div>';
                    }
                }
                $html .= '<div class="right-part"><a href="'. get_admin_url() . 'admin.php?page=sut-historic&link_id='.$link->shortcode.'&startdate=' . $datedebut . '&enddate=' . $datefin.'">'.__( 'Details', 'shorturl-tracker' ).'</a></div>';
                $html .= '<div class="icon-card"><div class="favorite-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="'.(!$link->favorite ? 'none' : 'currentColor').'" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-heart"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg></div><div class="social-share-link">'.$this->sut_byw_build_social_share_block($link->shortcode).'</div></div>';
                $html .= '</div>';
            }
            $html .= '</div>';
            return $html;
        }

        /**
         * @param $startDate
         * @param $endDate
         * @param $data
         * @return ArrayObject
         */
        public function sut_byw_get_between_dates($startDate, $endDate, $data): ArrayObject
        {
            $startDate = strtotime($startDate);
            $endDate = strtotime($endDate);
            for ($currentDate = $startDate; $currentDate <= $endDate; $currentDate += (86400)) {
                $date = date('Y-m-d', $currentDate);
                if (!isset($data[$date])) {
                    $data[$date] = array(
                        'click' => 0,
                        'device' => array(
                            'desktop' => 0,
                            'mobile' => 0
                        ),
                        'nb_purchase' => 0,
                        'purchase_amount' => 0
                    );
                }
            }
            $data_object = new ArrayObject($data);
            $data_object->ksort();
            return $data_object;
        }
        /**
         * generate array of social icon for JS script
         * @return array[]
         */
        function sut_byw_get_social_img(): array {
            return array(
                'facebook'  => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="feather feather-facebook"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>',
                'linkedin'  => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="feather feather-linkedin"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path><rect x="2" y="9" width="4" height="12"></rect><circle cx="4" cy="4" r="2"></circle></svg>',
                'twitter'   => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="feather feather-twitter"><path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"></path></svg>',
                'mail'      => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="feather feather-mail"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>'
            );
        }
        /**
         * @return array[]
         */
        public function sut_byw_is_activated(): array {
            global $wpdb;
            $table_site = $wpdb->prefix . 'shorturl_tracker';
            if ($this->sut_byw_check_db_exist($table_site)) {
                $sql = $wpdb->prepare("SELECT * FROM " . $table_site . " WHERE label=%s LIMIT 1", 'licence_key');
                $map = $wpdb->get_results($sql);
                if (!empty($map) && $map[0]->status == 'on') {
                    return array('activate' => true, 'level' => $map[0]->level);
                } else {
                    return array('activate' => false);
                }
            } else {
                return array('activate' => false);
            }
        }
        /**
         * function to get value from label
         * @param $value
         *
         * @return string
         */
        public function sut_byw_get_autorization_value( $value ): string {
            global $wpdb;
            $table_site = $wpdb->prefix . 'shorturl_tracker';
            if ($this->sut_byw_check_db_exist($table_site)) {
                if ($value == 'license_level') {
                    $sql = $wpdb->prepare("SELECT level FROM " . $table_site . " WHERE value=%s  LIMIT 1", SUT_BYW_TOKEN);
                } else {
                    $sql = $wpdb->prepare("SELECT value FROM " . $table_site . " WHERE label=%s LIMIT 1", $value);
                }
                $map = $wpdb->get_results($sql);
                if (count($map) != 0) {
                    if ($value == 'cookie_day') {
                        if ($map[0]->value != '') {
                            return $map[0]->value;
                        } else {
                            return 7;
                        }
                    } elseif ($value == 'license_level') {
                        return $map[0]->level;
                    } else {
                        return $map[0]->value;
                    }
                } else {
                    return "";
                }
            } else {
                return 'Error';
            }
        }

        public function sut_byw_check_db_exist($table_name): bool
        {
            global $wpdb;
            if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) === $table_name ) {
                return true;
            } else {
                return false;
            }
        }

        /**
         * @param $link
         * @return string
         */
        public function sut_byw_build_social_share_block($link): string
        {
            $social_icons = $this->sut_byw_get_social_img();
            $html = '<div class="share-box"><div class="share-action"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-share-2"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg><p>Share</p></div><ul>';
            $html .= '<li><a href="https://www.facebook.com/sharer/sharer.php?u=https://byw.li/'.$link.'" target="_blank">'.$social_icons['facebook'].'<p>Facebook</p></a></li>';
            $html .= '<li><a href="https://twitter.com/intent/tweet?url=https://byw.li/'.$link.'" target="_blank">'.$social_icons['twitter'].'<p>Twitter</p></a></li>';
            $html .= '<li><a href="https://www.linkedin.com/shareArticle?mini=true&url=https://byw.li/'.$link.'" target="_blank">'.$social_icons['linkedin'].'<p>Linkedin</p></a></li>';
            $html .= '<li><a href="mailto:info@example.com?body=https://byw.li/'.$link.'" target="_blank">'.$social_icons['mail'].'<p>Mail</p></a></li>';
            $html .= '</ul></div>';
            return $html;
        }

        /**
         * @return array[]
         */
        public function sut_byw_get_allowed_tags(): array
        {
            return array(
                'br' => array(),
                'h4' => array(
                    'class' => array(),
                    'id' => array(),
                ),
                'h3' => array(
                    'class' => array(),
                    'id' => array(),
                ),
                'h2' => array(
                    'class' => array(),
                    'id' => array(),
                ),
                'class' => array(),
                'value' => array(),
                'input' => array(),
                'select' => array(
                    'id' => array(),
                ),
                'option' => array(
                    'selected' => array(),
                    'value' => array(),
                ),
                'ul' => array(),
                'li' => array(),
                'div' => array(
                    'class' => array(),
                    'id' => array(),
                ),
                'p' => array(),
                'rect' => array(
                    'x'=> array(),
                    'y'=> array(),
                    'width'=> array(),
                    'height'=> array(),
                ),
                'polyline' => array(
                    'points'=> array()
                ),
                'path' => array(
                    'd'=> array()
                ),
                'circle' => array(
                    'cx' => array(),
                    'cy' => array(),
                    'r' => array(),
                ),
                'line' => array(
                    'x1' => array(),
                    'x2' => array(),
                    'y1' => array(),
                    'y2' => array(),
                ),
                'svg' => array(
                    'class' => array(),
                    'width' => array(),
                    'xmlns' => array(),
                    'height' => array(),
                    'fill' => array(),
                    'stroke' => array(),
                    'stroke-width' => array(),
                    'stroke-linecap' => array(),
                    'stroke-linejoin' => array(),
                ),
                'a' => array(
                    'href' => array(),
                    'target' => array(),
                ),
            );
        }

        public function sut_byw_mask_token($string) {
            $length = strlen($string);
            if ($length <= 6) {
                return $string;
            } else {
                return substr($string, 0, 3) . str_repeat('*', $length - 6) . substr($string, -3);
            }
        }

    }
}
$start = new Sut_Tracker();