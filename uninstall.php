<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) 
   exit();

require_once('shorturl-tracker.php');
require_once('class/Sut_Byw_Settings.php');
$start = new \ShortUrlTracker\Plugin\Sut_Byw_Settings();
$start->sut_byw_deactivate_licence($start->sut_byw_get_autorization_value('licence_key'));

function gmap_uninstall(){

  global $wpdb; 
 
  $table_site = array(
    $wpdb->prefix.'shorturl_tracker'
  );
 
  foreach ($table_site as $table) {
    if($wpdb->get_var("SHOW TABLES LIKE '$table'") == $table) { 
      $sql = "DROP TABLE `$table`";  
      $wpdb->query($sql); 
    } 
  }
} 
gmap_uninstall();  
