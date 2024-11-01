<?php

namespace ShortUrlTracker\Plugin;

if (!defined('ABSPATH')) die();

if (!class_exists('Sut_Byw_Historic')) {

    class Sut_Byw_Historic extends Sut_Tracker {

        public function __construct() {
            parent::__construct();
            add_action( 'admin_print_scripts', array( $this, 'sut_byw_chart_js' ) );
        }

        /**
         * @param $start
         * @param $end
         *
         * @return string
         */
        public function sut_byw_call_historic( $start, $end ): string
        {
            $url      = SUT_BYW_API . '/v1/historic_link?startdate=' . $start . '&enddate=' . $end;
            $args     = array(
                'timeout' => 10,
                'headers' => array(
                    'Origin'        => get_home_url(),
                    'Content-Type'  => 'application/json',
                    'Authorization' => SUT_BYW_TOKEN
                )
            );
            $response = wp_remote_get( $url, $args );
            $answer   = json_decode( wp_remote_retrieve_body( $response ) );
            if ( wp_remote_retrieve_response_code( $response ) == '200' ) {
                return $this->sut_byw_format_data( $answer, $start, $end );
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

        public function sut_byw_get_link_historic($link, $start, $end) {
            $url      = SUT_BYW_API . '/v1/link_stat?link='.$link.'&startdate=' . $start . '&enddate=' . $end;
            $param     = array(
                'timeout' => 10,
                'headers' => array(
                    'Origin'        => get_home_url(),
                    'Content-Type'  => 'application/json',
                    'Authorization' => SUT_BYW_TOKEN
                )
            );
            $response = wp_remote_get( $url, $param );
            $answer   = json_decode( wp_remote_retrieve_body( $response ) );
            if ( wp_remote_retrieve_response_code( $response ) == '200' ) {
                if (isset($answer->token)) {
                    $sales = $this->sut_byw_get_sales($answer->token);
                    if (isset($sales['date']) && $sales['date'] != null) {
                        foreach ($sales['date'] as $date_purchase => $key) {
                            if (isset($answer->date->$date_purchase)) {
                                $answer->date->$date_purchase->purchase_amount += $key['amount'];
                                $answer->date->$date_purchase->nb_purchase += $key['count'];
                                $answer->total_amount += $key['amount'];
                                $answer->total_purchase += $key['count'];
                            }
                        }
                    }
                }
                return $answer;
            } else {
                $error_code = wp_remote_retrieve_response_code( $response );
                switch ( $error_code ) {
                    case '401':
                        $error_id = "401";
                        $error_msg = __( 'Invalid License', 'shorturl-tracker' );
                        break;
                    case '400':
                        $error_id = "400";
                        $error_msg = __( 'Bad Request', 'shorturl-tracker' );
                        break;
                    default:
                        $error_id = "401";
                        $error_msg = __( 'Sorry, an error occurred. Please reload the page', 'shorturl-tracker' );
                        break;
                }
                return array(
                    'code' => $error_id,
                    'message' => $error_msg
                );
            }
        }


        public function sut_byw_chart_js() {
            wp_register_script( 'chartJs', SUT_BYW_PURL . 'js/chart.min.js', array(), '4.1.1', true );
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

        public function sut_byw_graph_value_js($data, $start, $end) {
            $all_date = $this->sut_byw_get_between_dates($start, $end, $data);
            wp_localize_script( 'sutDashboard', 'dashboardData', array(
                'root'      => esc_url_raw( rest_url() ),
                'nonce'     => wp_create_nonce( 'wp_rest' ),
                'dayByDay'  => $all_date
            ) );
        }

    }
}