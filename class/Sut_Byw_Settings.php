<?php

namespace ShortUrlTracker\Plugin;

if (!defined('ABSPATH')) die();

if (!class_exists('Sut_Byw_Settings')) {

    class Sut_Byw_Settings extends Sut_Tracker {

        public function __construct() {
            parent::__construct();
        }

	    /**
	     * @param $licence_type
	     * @param $licence_key
	     *
	     * @return bool|string|void
	     */
	    public function sut_byw_insert_licence_key($licence_type, $licence_key){
		    if ($licence_type == 'licence_key') {
			    $origin = get_home_url();
			    $url = SUT_BYW_API.'/v1/activate/' . $licence_key;
			    $args = array(
				    'headers' => array(
					    'Origin' => $origin
				    )
			    );
			    $response = wp_remote_get($url, $args);
                $answer   = json_decode( wp_remote_retrieve_body( $response ) );
			    if (wp_remote_retrieve_response_code($response) == '200') {
				    global $wpdb;
				    $table_site = $wpdb->prefix.'shorturl_tracker';
				    $sql = $wpdb->prepare("SELECT * FROM ".$table_site." WHERE label=%s LIMIT 1",$licence_type);
				    $wpdb->query($sql);
				    $map = $wpdb->get_results($sql);
				    if (count($map) != 0){
					    $sql=$wpdb->prepare("UPDATE ".$table_site." SET value=%s WHERE label=%s", $licence_key, $licence_type);
					    $wpdb->query($sql);
					    return true;
				    } else {
					    $sql=$wpdb->prepare("INSERT INTO ".$table_site." (label, value, status, level) VALUES (%s,%s,%s,%s)", $licence_type, $licence_key,'on',$answer->license_level);
					    $wpdb->query($sql);
					    if (!$sql) {
						    return "error";
					    } else {
						    return true;
					    }
				    }
			    } else if (wp_remote_retrieve_response_code($response) == '404') {
				    echo '<script>alert("La clé de Licence a atteint le maximum d\'activation")</script>';
			    } else {
				    echo '<script>alert("Une erreur est survenue, veuillez tenter à nouveau")</script>';
			    }
		    } else {
			    global $wpdb;
			    $table_site = $wpdb->prefix.'shorturl_tracker';
			    $sql = $wpdb->prepare("SELECT * FROM ".$table_site." WHERE label=%s LIMIT 1",$licence_type);
			    $wpdb->query($sql);
			    $map = $wpdb->get_results($sql);
			    if (count($map) != 0){
				    $sql=$wpdb->prepare("UPDATE ".$table_site." SET value=%s WHERE label=%s", $licence_key, $licence_type);
				    $wpdb->query($sql);
				    return true;
			    } else {
				    $sql=$wpdb->prepare("INSERT INTO ".$table_site." (label, value, status) VALUES (%s,%s,%s)",  $licence_type, $licence_key, 'saved');
				    $wpdb->query($sql);
				    if (!$sql) {
					    return "error";
				    } else {
					    return true;
				    }
			    }
		    }
	    }

	    /**
	     * function to deactivate customer licence
	     * @param $licence
	     *
	     * @return bool
	     */
	    public function sut_byw_deactivate_licence($licence): bool {
		    $url = SUT_BYW_API.'/v1/deactivate/' . $licence;
		    $args = array(
			    'headers' => array(
				    'Origin' => get_home_url()
			    )
		    );
		    $response = wp_remote_get($url, $args);
		    if (wp_remote_retrieve_response_code($response) == '200' || wp_remote_retrieve_response_code($response) == '404' || wp_remote_retrieve_response_code($response) == '405') {
			    return true;
		    } else {
			    return false;
		    }
	    }


	    /**
	     * @param $licence_key
	     *
	     * @return bool
	     */
	    public function sut_byw_delete_licence($licence_key): bool {
		    global $wpdb;
		    $table_site = $wpdb->prefix.'shorturl_tracker';
		    $sql = $wpdb->prepare("DELETE FROM ".$table_site." WHERE label=%s LIMIT 1", $licence_key);
		    $wpdb->query($sql);
		    return true;
	    }

	    /**
	     * @return void
	     */
	    public function sut_byw_return_page(): void
        {
		    unset($_POST);
		    ?><script>window.location.reload()</script><?php
	    }

        public function sut_byw_get_account_detail(){
            $url = SUT_BYW_API.'/v1/account/';
            $args = array(
                'headers' => array(
                    'Origin' => get_home_url(),
                    'Authorization' => SUT_BYW_TOKEN
                )
            );
            $response = wp_remote_get($url, $args);
            $answer   = json_decode( wp_remote_retrieve_body( $response ) );
            if (wp_remote_retrieve_response_code($response) == '200' ) {
                global $wpdb;
                $table_site = $wpdb->prefix.'shorturl_tracker';
                $sql = $wpdb->prepare("SELECT * FROM ".$table_site." WHERE value=%s LIMIT 1", SUT_BYW_TOKEN);
                $wpdb->query($sql);
                $map = $wpdb->get_results($sql);
                if (count($map) != 0 && $map[0]->level != $answer->license_level){
                    $sql=$wpdb->prepare("UPDATE ".$table_site." SET level=%s WHERE value=%s", $answer->license_level, SUT_BYW_TOKEN);
                    $wpdb->query($sql);
                }
                return $answer;
            } else {
                return false;
            }
        }
    }
}


