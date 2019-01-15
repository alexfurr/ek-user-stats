<?php
/*
Plugin Name: Edukit User Stats
Description: Gather stats about registered users activity on your site
Version: 0.1
Author: Alex Furr and Simon Ward
License: GPL
*/
define( 'USER_STATS_URL', plugins_url('ek-user-stats' , dirname( __FILE__ )) );
define( 'USER_STATS_PATH', plugin_dir_path(__FILE__) );

include_once( USER_STATS_PATH . 'functions.php' );
include_once( USER_STATS_PATH . 'classes/class-database.php' );
include_once( USER_STATS_PATH . 'classes/class-dashboard.php' );
include_once( USER_STATS_PATH . 'classes/class-queries.php' );
include_once( USER_STATS_PATH . 'classes/class-utils.php' );
include_once( USER_STATS_PATH . 'classes/class-draw.php' );

/* Libs for detecting Browsert type / platform etc */
include_once( USER_STATS_PATH . 'classes/class-mobile-detect.php' );
include_once( USER_STATS_PATH . 'classes/class-detect.php' );

// Libs
// Google Charts
include_once( USER_STATS_PATH . '/lib/google-charts/google-charts.php');
/* Include the Browser version */
include_once( USER_STATS_PATH . 'lib/browser.php' );



 
// CSV Exports
include_once( USER_STATS_PATH . '/classes/class-export.php');

?>