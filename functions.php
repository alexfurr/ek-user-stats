<?php
$ek_user_stats = new ek_user_stats();
class ek_user_stats
{
	
	var $version = '0.3';
	
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
		add_action('wp_head', array($this, 'add_data') );
		add_action('wp_dashboard_setup', array($this, 'my_custom_dashboard_widgets') );		
		add_action( 'admin_enqueue_scripts', array( $this, 'adminEnqueues' ));
		add_action( 'admin_menu', array( $this, 'createAdminMenu' ) );
		
		
		// Add network  pages for global stats
		add_action( 'network_admin_menu', array( $this, 'create_NetworkAdminPages' ));			
		
		
	}
	
	
	
	function add_data()
	{
		global $post;
		global $ek_user_stats_db;
		
		$userID = get_current_user_id();	
		
		if ( is_home() )
		{
			$pageType = 'home';
			$pageID = "";
		}
		elseif ( is_post_type_archive() )
		{		
			$pageID = "";
			$pageType = 'archive';
		
		}
		elseif( is_category() )
		{
			$pageID = "";	
			$pageType = 'category';			
			
		}
		elseif(is_author() )
		{
			$pageID = "";	
			$pageType = 'author';	
		}
		elseif(is_tag() )
		{
			$pageType = 'tag';
			$pageID = "";			
		}	
		elseif( is_attachment () )
		{
			$pageType = 'attachment';
			$pageID = "";
		}
		elseif( is_404 () )
		{
			$pageType = '404';
			$pageID = "";

		}
		elseif( is_search() )
		{
			$pageType = 'search';
			$pageID = "";		}
		else
		{
			$pageType = 'post';
			$pageID = $post->ID;
		}
		
		//Add Last Accessed Date and incremeent session count if so
		
		$sessionCountArray = get_user_meta($userID, "ek_stats_session_count", true);
		$lastAccessArray = get_user_meta($userID, "ek_stats_last_access_date", true);
		
		
		// Get  the current site ID
		$blogID = get_current_blog_id();
		$currentDate = current_time( 'Y-m-d h:i:s' );	

		
		
		// If session count doesn't exist create it
		if(!isset($sessionCountArray[$blogID]) )
		{
			$sessionCount = 1;
			$sessionCountArray[$blogID]=$sessionCount;
			$lastAccessArray[$blogID] = $currentDate;
			update_user_meta( $userID, "ek_stats_session_count", $sessionCountArray ); 
			update_user_meta( $userID, "ek_stats_last_access_date", $lastAccessArray ); 
						
		}
		else
		{
			// Get the Session Count and Access Date
			$sessionCount = $sessionCountArray[$blogID];
			$lastAccessDate = $lastAccessArray[$blogID];
			
			// Check if they need to up the session count			
			$secondsSinceLastAccess = ek_user_stats_utils::dateDiff($lastAccessDate, $currentDate);
			
			$minuteThreshold = 30; // The Amount of minutes before increasing session	
			
			if($secondsSinceLastAccess >  ( $minuteThreshold * 60 ) )
			{
				$sessionCount++;
				$sessionCountArray[$blogID]=$sessionCount;
				update_user_meta( $userID, "ek_stats_session_count", $sessionCountArray );
				
			}
			
			$lastAccessArray[$blogID] = $currentDate;			
			update_user_meta( $userID, "ek_stats_last_access_date", $lastAccessArray );
			
		}
			

		$pageTypeMeta = '';
				
		$args = array(
		"userID"		=>$userID,
		"pageID"		=>$pageID,
		"pageType"		=>$pageType,
		"pageTypeMeta"	=>$pageTypeMeta,		
		"sessionCount"	=> $sessionCount,
		);
			
		if($userID)
		{			// ADD THE DATA
			$ek_user_stats_db->addRecord ( $args );
		}
	}
	
	
	function my_custom_dashboard_widgets() {
		global $wp_meta_boxes;
		wp_add_dashboard_widget('custom_help_widget', 'Student Stats', array('ek_user_stats_dashboard', 'custom_dashboard_help') );
	}
	
	function adminEnqueues()
	{
		wp_enqueue_script('jquery');
        wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		/* add jquery ui datepicker and theme */
		
		// get the jquery ui object
		global $wp_scripts;
		$queryui = $wp_scripts->query('jquery-ui-core');
	 
		// load the jquery ui theme
		$url = "https://ajax.googleapis.com/ajax/libs/jqueryui/".$queryui->ver."/themes/smoothness/jquery-ui.css";	
		
		wp_enqueue_style('jquery-ui-smoothness', $url, false, null);
		wp_enqueue_style( 'ek-user-stats', USER_STATS_URL . '/styles.css' );
		
		wp_register_style( 'wp_enqueue_style', '//use.fontawesome.com/releases/v5.2.0/css/all.css' );

		
		
		// Preloader
		wp_enqueue_style( 'ek-preloader-style', USER_STATS_URL . '/assets/preloader/preloader.css' );
		wp_enqueue_script('ek-preloader-js', USER_STATS_URL . '/assets/preloader/preloader.js' , array( 'jquery' ) ); 		
			
		
		// Data tables
		wp_enqueue_script('ek-datatables-js', '//cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js', array( 'jquery' ) ); 
		wp_enqueue_style( 'ek-datatables-css', '//cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css' );		
		
		// load roboto
		wp_enqueue_style( 'imperial-med-roboto', "//fonts.googleapis.com/css?family=Roboto:100,100i,300,400,400i,700" );
		
		
				
		
	}	
	
	function createAdminMenu ()
	{
		
		// Main Page
		$page_title="My Stats";
		$menu_title = "Course Stats";
		$capability = "delete_others_pages";
		$menu_slug = "ek-user-stats-admin";
		$drawFunction = "drawStatsDashboardPage";
		$icon = "dashicons-chart-line";
		$position = 99;		
		add_menu_page($page_title, $menu_title, $capability, $menu_slug, array($this, $drawFunction), $icon, $position);	

		
		// Only show this if the class exists i.e. imperial course is active

		// Student List
		$parent_slug="ek-user-stats-admin";
		$menu_title = "User List";
		$capability = "delete_others_pages";
		$menu_slug = "ek-user-stats-student-list";
		$drawFunction = "drawStudentList";
		add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, array($this, $drawFunction));	
			
		
		
		// Reports
		$parent_slug="ek-user-stats-admin";
		$menu_title = "Reports";
		$capability = "delete_others_pages";
		$menu_slug = "ek-user-stats-reports";
		$drawFunction = "drawReports";
		add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, array($this, $drawFunction));	
		
		// Pages
		$parent_slug="ek-user-stats-admin";
		$menu_title = "Page Reports";
		$capability = "delete_others_pages";
		$menu_slug = "ek-user-stats-page-reports";
		$drawFunction = "drawPageReports";
		add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, array($this, $drawFunction));	

		// Tools
		$parent_slug="ek-user-stats-admin";
		$menu_title = "Tools";
		$capability = "delete_others_pages";
		$menu_slug = "ek-user-stats-tools";
		$drawFunction = "drawTools";
		add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, array($this, $drawFunction));	


		
	}	
	
	function create_NetworkAdminPages()
	{

		$parentMenuSlug = 'ek-stats-network';		

		/* Network Admin Pages */	
		$page_title = "Network Stats";
		$menu_title = "Network Stats";
		$capability = "manage_network_options"; //'manage_options' for administrators.
		$function = array( $this, 'drawNetworkStats' );
		$icon = 'dashicons-chart-line';
		$handle = add_menu_page( $page_title, $menu_title, $capability, $parentMenuSlug, $function, $icon );
		
			
		
		
		
	}
	
	
	//~~~~~ Drawing
	

	function drawNetworkStats()
	{
		include_once( dirname(__FILE__) . '/admin/network_stats.php');
	}	
	
	
	function drawStatsDashboardPage()
	{
		include_once( USER_STATS_PATH . '/admin/index.php' );
	}	
	
	
	function drawStudentList()
	{
		include_once( USER_STATS_PATH . '/admin/students.php' );
	}	
	
	function drawReports()
	{
		include_once( USER_STATS_PATH . '/admin/reports.php' );
	}	
	
	function drawPageReports()
	{
		include_once( USER_STATS_PATH . '/admin/pages.php' );
	}		
	
	function drawTools()
	{
		include_once( USER_STATS_PATH . '/admin/tools.php' );
	}		
	
} //Close class


?>