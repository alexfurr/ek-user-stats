<?php
$ek_user_stats_db = new ek_user_stats_db();

class ek_user_stats_db
{
	
	var $dbTable_usersStats 	= 'ek_users_stats';
	
	//~~~~~
	function __construct ()
	{
		$this->addWPActions();
	}	
	
/*	---------------------------
	PRIMARY HOOKS INTO WP 
	--------------------------- */	
	function addWPActions ()
	{
		
		add_action( 'plugins_loaded', array($this, 'myplugin_update_db_check' ) );
	}
	
	// Function to check latest evrsino and then update DB if needed
	function myplugin_update_db_check()
	{

		global $ek_user_stats;
		$pluginVersion = $ek_user_stats->version;
	
		
		$savedVersion = get_option( 'ekUserStatsVersion' );
		
		if($savedVersion=="")
		{

			add_option( 'ekUserStatsVersion', $pluginVersion );
			$this->installDB();			
			
		}
		elseif ( get_option( 'ekUserStatsVersion' )< $pluginVersion )
		{

			// Update version op
			update_option( 'ekUserStatsVersion', $pluginVersion );
			$this->installDB();
						
		}
		
		// Overrider for testing
		//$this->installDB();
	
	}		
	
	//~~~~~
	function installDB ()
	{
		
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		
		$WPversion = substr( get_bloginfo('version'), 0, 3);
		$charset_collate = ( $WPversion >= 3.5 ) ? $wpdb->get_charset_collate() : $this->getCharsetCollate();
		
		$table = $wpdb->prefix . $this->dbTable_usersStats;

		//users table
		$sql = "CREATE TABLE $table (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			user_id mediumint(9),
			page_id mediumint(9),
			pageURL varchar(256),
			pageType mediumint(9),
			pageTypeMeta mediumint(9),
			read_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			currentSession mediumint(9),
			platform varchar(256),
			detailedPlatform varchar(256),
			browser varchar(256),
			browserVersion varchar(256),
			deviceType varchar(256),
			PRIMARY KEY id (id),
			KEY user_id (user_id),
			KEY read_date (read_date),
			KEY pageURL (pageURL)
			) $charset_collate;";
			
		dbDelta( $sql );

	}	
	
	
	// Add record
	function addRecord ( $args )
	{
		$userID = $args['userID'];
		$pageID = $args['pageID'];		
		$pageType = $args['pageType'];
		$pageTypeMeta = $args['pageTypeMeta'];
		$currentSession = $args['sessionCount'];

		$pageURL = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		
		/* Store the Device Type */
		$deviceType = Detect::deviceType();
	

		$browser = new Browser();
		$thisBrowser = $browser->getBrowser();
		$thisPlatform = $browser->getPlatform();
		$detailedPlatform =  Detect::os();
		$browserVersion = $browser->getVersion();
			
		global $wpdb;
		
		echo 'added to '.$wpdb->prefix . $this->dbTable_usersStats;
		
		 $wpdb->insert( 
			$wpdb->prefix . $this->dbTable_usersStats, 
			array( 
				'user_id' 			=> $userID,
				'page_id' 			=> $pageID,
				'pageURL' 			=> $pageURL,
				'pageType'			=> $pageType,
				'pageTypeMeta'		=> $pageTypeMeta,
				'platform'			=> $thisPlatform,
				'detailedPlatform'	=> $detailedPlatform,
				'browser'			=> $thisBrowser,
				'browserVersion'	=> $browserVersion,
				'deviceType'		=> $deviceType,
				'currentSession'	=> $currentSession,
				'read_date' 	=> current_time( 'mysql' )
			),
			array( '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s'  )
		);
	}
	
	function  getRecords ( $userID = '', $pageID = '' ) 
	{
		global $wpdb;
		
		$sql = "SELECT * FROM " . $wpdb->prefix . $this->dbTable_usersStats;
		if ( $userID !== '' ) 
		{ 
			$sql .= " WHERE user_id=" . $userID;
		}
		if ( $pageID !== '' ) 
		{
			$sql .= " AND page_id=" . $pageID;
		}
		
		return $wpdb->get_results( $sql );
	}


} //Close class