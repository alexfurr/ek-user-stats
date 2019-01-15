<?php


class ek_user_stats_utils
{


	static function detectDevice(){
		$userAgent = $_SERVER["HTTP_USER_AGENT"];
		
		
		echo 'userAgent = '.$userAgent.'<br/>';
		
		
		$devicesTypes = array(
			"computer" => array("msie 10", "msie 9", "msie 8", "windows.*firefox", "windows.*chrome", "x11.*chrome", "x11.*firefox", "macintosh.*chrome", "macintosh.*firefox", "opera"),
			"tablet"   => array("tablet", "android", "ipad", "tablet.*firefox"),
			"mobile"   => array("mobile ", "android.*mobile", "iphone", "ipod", "opera mobi", "opera mini"),
			"bot"      => array("googlebot", "mediapartners-google", "adsbot-google", "duckduckbot", "msnbot", "bingbot", "ask", "facebook", "yahoo", "addthis")
		);
		foreach($devicesTypes as $deviceType => $devices) {           
			foreach($devices as $device) {
				if(preg_match("/" . $device . "/i", $userAgent))
				{
					$deviceName = $deviceType;
				}
				else
				{
					$deviceName = 'Unknown';
				}
			}
		}
		return ucfirst($deviceName);
	}
	
	
	
	// Returns an amount in millisceonds between two mysql dates
	public static function dateDiff($startDate, $endDate)
	{
		// Assumes input it a mysql date format
		$startDate = strtotime($startDate);
		$endDate = strtotime($endDate);
		
		$secondsSinceResponse = $endDate-$startDate;			
		
		return $secondsSinceResponse;
	}



	public static function getDeviceCharts($activityLog)
	{
		
		$deviceTypeArrayCount = array();
		$platformCountArray = array();
		$browserCountArray = array();
		
		$deviceTypeData = array();
		$platformData  = array();
		$browserData  = array();

		foreach($activityLog as $activityInfo)
		{

			$deviceType = $activityInfo['deviceType'];
			$activityID = $activityInfo['id'];
			$platform = $activityInfo['platform'];
			$browser = $activityInfo['browser'];
			
			if($deviceType)
			{
				$deviceTypeArrayCount[$deviceType][] = $activityID;		
			}
			
			if($platform)
			{
				$platformCountArray[$platform][] = $activityID;		
			}
			
			if($browser)
			{
				$browserCountArray[$browser][] = $activityID;		
			}
		}

		foreach($deviceTypeArrayCount as $deviceTypeName => $deviceCounts)
		{	
			$thisDeviceCount =  count($deviceCounts);
			$deviceTypeData[] = array( $deviceTypeName, $thisDeviceCount );	
			//$themeChartData[$deviceTypeName] = $thisDeviceCount;
		}

		foreach($platformCountArray as $platformName => $myCount)
		{	
			$thisPlatformCount =  count($myCount);
			$platformData[] = array( $platformName, $thisPlatformCount );	
			//$themeChartData[$deviceTypeName] = $thisDeviceCount;
		}

		foreach($browserCountArray as $browserType => $myCount)
		{	
			$thisBrowserCount =  count($myCount);
			$browserData[] = array( $browserType, $thisBrowserCount );	
			//$themeChartData[$deviceTypeName] = $thisDeviceCount;
		}	

		
		
		$returnArray = array(
			"deviceType" => $deviceTypeData,
			"platform" => $platformData,
			"browser" => $browserData,
		
		);

		return $returnArray; 			
		
		
		
		
	}
		
	static function getUKdate($inputDate, $format="Y-m-d H:i:s")
	{
		$tz = new DateTimeZone('Europe/London');
		$date = new DateTime($inputDate);
		$date->setTimezone($tz);
		$UKdate = $date->format($format);
		
		
		return $UKdate;
	}	

	
	// Gets the start date of a week (Monday) given a date
	static function getStartOfWeekDate($date = null)
	{
		if ($date instanceof \DateTime) {
			$date = clone $date;
		} else if (!$date) {
			$date = new DateTime();
		} else {
			$date = new DateTime($date);
		}
		
		$date->setTime(0, 0, 0);
		
		// Get the Day of the week. iuf it's a monday then retun as is
		if ($date->format('N') == 1)
		{		
			$lastMonday =  $date;
		}
		else
		{
			$lastMonday =  $date->modify('last monday');	
		}
	
		$lastMondayReturn= $lastMonday->format('Y-m-d');		
		$nextSunday =  $lastMonday->modify('next sunday');		
		$nextSundayReturn= $nextSunday->format('Y-m-d');
		$returnArray = array($lastMondayReturn, $nextSundayReturn);
		
		return $returnArray;
		
	}
	
	
	static function getOverallProgress($userID)
	{
		
		$totalPagesViewed = 0;
		
		$masterPageID_array = ek_user_stats_utils::getTopicPagesArray();
		// Get the activity for this student
		$args = array(
			"userID"	=> $userID,
		);
		$myActivity = ek_user_stats_queries::getActivity($args);	
		
		
		$checkPageArray = array();
		// Go through the activity and add the page IS as keys to new array		
		foreach ($myActivity as $activityInfo)
		{
			$thisPageID = $activityInfo['page_id'];
			$checkPageArray[$thisPageID] = true;
		}

		foreach ($checkPageArray as $checkPageID => $value)
		{
			if(in_array($checkPageID, $masterPageID_array) )
			{
				$totalPagesViewed++;
			}
		}

		
		// Count the total number of pages
		$totalPageCount = count($masterPageID_array);
		$overallProgress = 0;	

		if($totalPagesViewed>1)
		{
			$overallProgress = round( ($totalPagesViewed / $totalPageCount * 100), 0);				
		}
		
		
		return $overallProgress;			
		
	}
	
	
	
	// Gets an array of all pages in the tracked topics
	static function getTopicPagesArray()
	{
		
		$masterPageID_array = array();
		$args = array(
			'posts_per_page'   => -1,
			'orderby'          => 'menu_order',
			'order'            => 'ASC',		
			'post_type'        => 'imperial_topic',
			'post_status'      => 'publish',
		);
		$topics = get_posts( $args );		

		foreach($topics as $topicInfo)
		{
			
			$topicName = $topicInfo->post_title;
			$topicID = $topicInfo->ID;
			
			// Get the Sessions 
			$args = array(
				'posts_per_page'   => -1,
				'orderby'          => 'menu_order',
				'order'            => 'ASC',
				'include'          => '',
				'exclude'          => '',
				'post_type'        => 'topic_session',
				'post_parent'      => $topicID,
				'post_status'      => 'publish',
			);
			$topicSessions = get_posts( $args );
					
			foreach($topicSessions as $sessionInfo)
			{
				
				$sessionName = $sessionInfo->post_title;
				$sessionID = $sessionInfo->ID;
								
				$args = array(
					'posts_per_page'   => -1,
					'orderby'          => 'menu_order',
					'order'            => 'ASC',
					'include'          => '',
					'exclude'          => '',
					'post_type'        => 'session_page',
					'post_parent'      => $sessionID,
					'post_status'      => 'publish',
				);
				$sessionPages = get_posts( $args );

				
				// get the session pages
				foreach($sessionPages as $pageInfo)
				{
					$pageID = $pageInfo->ID;

					// Add thie page to the master array
					$masterPageID_array[] = $pageID;
				}	
			
			}
		}	
		
		return $masterPageID_array;
	}
}


?>