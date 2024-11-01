<?php

namespace ShortUrlTracker\Plugin;

if (!defined('ABSPATH')) die();

if (!class_exists('Sut_Byw_Campaign')) {

    class Sut_Byw_Campaign extends Sut_Tracker {

        public function __construct() {
            parent::__construct();
            add_action( 'admin_print_scripts', array($this, 'sut_byw_campaign_js') );
        }

        /**
         * @param $name
         * @param $description
         * @param $favorite
         * @return bool|string
         */
        public function sut_byw_create_campaign( $name, $description, $favorite ): bool|string
        {
            $data = array(
                'name' => $name,
                'description' => $description,
                'isFavorite' => $favorite
            );
            $payload = json_encode($data);
            $url      = SUT_BYW_API . '/v1/campaign';
            $args     = array(
                'timeout' => 10,
                'body'    => $payload,
                'headers' => array(
                    'Origin'        => get_home_url(),
                    'Content-Type'  => 'application/json',
                    'charset'       => 'utf8',
                    'Authorization' => SUT_BYW_TOKEN
                )
            );
            $response = wp_remote_post( $url, $args );
            $answer   = json_decode( wp_remote_retrieve_body( $response ) );
            if ( wp_remote_retrieve_response_code( $response ) == '201' ) {
                header('Location: '.$_SERVER['HTTP_REFERER'].'&campaign_id='.$answer->campaign_id);
                return true;
            } else {
                $error_code = wp_remote_retrieve_response_code( $response );
                switch ( $error_code ) {
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


        public function sut_byw_campaign_js() {
            wp_register_script( 'chartJs', SUT_BYW_PURL . 'js/chart.min.js', array(), '4.1;1', true );
            wp_register_script( 'chartPluginJs', SUT_BYW_PURL . 'js/chart-plugin-datalabels.min.js', array(), '2.2.0', true );
            wp_register_script( 'sutDashboard', SUT_BYW_PURL . 'js/sut-dashboard.js', array(), '1.0', true );
            wp_enqueue_script( 'chartJs' );
            wp_enqueue_script( 'chartPluginJs' );
            wp_enqueue_script( 'sutDashboard' );
            wp_enqueue_script('jquery');
            wp_register_script( 'slick', SUT_BYW_PURL . 'js/slick/slick.min.js', array('jquery'), SUT_BYW_SLICK_VERSION, true );
            wp_enqueue_script( 'slick' );
            wp_register_script( 'slick-init', SUT_BYW_PURL . 'js/slick/slick.init.js', array('jquery'), SUT_BYW_SLICK_VERSION, true );
            wp_enqueue_script( 'slick-init' );
        }

        public function sut_byw_create_campaign_js() {
            wp_register_script( 'sutCampaign', SUT_BYW_PURL . 'js/sut-campaign.js', array(), '1.0', true );
            wp_enqueue_script( 'sutCampaign' );
        }

        public function sut_byw_campaign_value_js($data, $start, $end) {
            $all_date = $this->sut_byw_get_between_dates($start, $end, $data);
            wp_localize_script( 'sutDashboard', 'dashboardData', array(
                'root'      => esc_url_raw( rest_url() ),
                'ajaxUrl'   => admin_url( 'admin-ajax.php'),
                'nonce'     => wp_create_nonce( 'wp_rest' ),
                'dayByDay'  => $all_date,
            ) );
        }
    }
}