<?php


class ek_user_stats_draw
{
	static function drawUserTopicActivity($userID)
	{
		$html='';
		$html.='<div class="ekStatsUserTopicProgressReport">';
		
		
		$masterPageID_array = array(); // Array to keep all pages IDs in to get overal progress
		
		$totalPagesViewed = 0; // Counter for pages viewed
		
		// Get all topics and put into array
		$topics = getTopics();
		
		// Get the actiivty for this studentList
		$args = array(
			"userID"	=> $userID,
		);
		$myActivity = ek_user_stats_queries::getActivity($args);	
		$myActivityLookupArray = array();
		
		// Go through the activity and add the page IS as keys to new array
		
		foreach ($myActivity as $activityInfo)
		{
			$thisPageID = $activityInfo['page_id'];
			$thisReadDate = $activityInfo['read_date'];
			$myActivityLookupArray[$thisPageID][] = $thisReadDate;
		}
		
		$topicsHTML = '';
		foreach($topics as $topicInfo)
		{
			
			$topicName = $topicInfo->post_title;
			$topicID = $topicInfo->ID;	
			$sessionHTML='';

			
			// Get the Sessions 			
			$topicSessions = getTopicSessions($topicID);
			$thisTopicPageCount = 0;
			$thisTopicPageViews = 0;			
			
			foreach($topicSessions as $sessionInfo)
			{
				
				$sessionName = $sessionInfo->post_title;
				$sessionID = $sessionInfo->ID;
				
				$sessionHTML.='<h3>'.$sessionName.'</h3>';					
				
				$sessionHTML.='<table>';
				$sessionPages = getSessionPages($sessionID);
				

				$currentPageNo = 1;
				// get the session pages
				foreach($sessionPages as $pageInfo)
				{
					
					$pageName = $pageInfo->post_title;
					$pageID = $pageInfo->ID;
					$pageName = $pageInfo->post_title;
					$thisClass='ekStatsReportNotRead';
					$lastAccessDate= '';
					if (isset($myActivityLookupArray[$pageID]) )
					{
						$thisClass='ekStatsReportRead';						
						$lastAccess = end($myActivityLookupArray[$pageID]);						
						$lastAccessDate = ek_user_stats_utils::getUKdate($lastAccess, "jS F, Y h:i A");						
						
						// Up the master count and the topic count page views
						$totalPagesViewed++;
						$thisTopicPageViews++;	
					}						
					
					$sessionHTML.='<tr><td><span class="'.$thisClass.'">'.$currentPageNo.'. '.$pageName.'</span></td>';
					
					$sessionHTML.='<td>'.$lastAccessDate.'</td></tr>';
					
					// Add thie page to the master array
					$masterPageID_array[] = $pageID;
					$thisTopicPageCount++;
					$currentPageNo++;


				}	
				$sessionHTML.='</table>';
			
			}
			
			// Add thie page to the master array
			$topicsHTML.='<div class="topicDiv">';			
			$topicsHTML.=	'<h2>'.$topicName.'</h2>';			
				
			$topicProgress=0;
			$topicProgressBarHTML='';
			if($thisTopicPageViews>1)
			{
				$topicProgress = round( ($thisTopicPageViews / $thisTopicPageCount * 100), 0);
			}
			
			$topicProgressBar = imperialCourse_draw::drawProgressBar($topicProgress);
			$topicProgressBarHTML.='<div style="width:300px">'.$topicProgressBar.'</div>';			
			$topicsHTML.=$topicProgressBarHTML;
			$topicsHTML.=$sessionHTML;
			
			
			$topicsHTML.='</div>';
		}
		
		// Count the total number of pages
		$totalPageCount = count($masterPageID_array);
		
		$overallProgress = 0;		
		if($totalPagesViewed>1)
		{
			$overallProgress = round( ($totalPagesViewed / $totalPageCount * 100), 0);				
		}
		
		$overallProgressBar = imperialCourse_draw::drawProgressBar($overallProgress);		
		
		
		$html.='<div class="topicDiv"><h2>Overall progress</h2>';
		$html.=$totalPagesViewed.' pages viewed out of '.$totalPageCount.'<hr/>';
		$html.='<div style="width:500px">';
		$html.=$overallProgressBar.'</div></div>';
		
		$html.=$topicsHTML;
		$html.='</div>';
		
		return $html;		
		
	}
	
	

	static function drawAllStudentsLog($csv=false)
	{
		// Get the latest actvity
		$activityLookupArray = array();
		$masterUserActivityArray = array();
		$masterPageID_array = array();
		
		
		
		$csvArray = array();
		$html='';
		
		

		// Get an array of all tracked pages
		$trackedPages = ek_user_stats_utils::getTopicPagesArray();	
				
		$masterUserActivityArray = array();


		// Get Array of users for user lookup
		$userList = get_users();
		// Array of WP_User objects.
		$html.= '<table id="studentList">';
		$html.= '<thead><tr><th>Name</th><th>Username</th><th>WP User ID</th><th>Page Hits</th><th>Sessions</th><th>Last Seen</th>';
		
		if(class_exists('cpt_topics') )
		{
			$html.='<th>Progress</th><th>Progress Hidden</th>';
		}
		
		
		$html.='</tr></thead>';
		$html.= '<tbody>';
		
		
		$csvHeadingArray = array("Name", "Username", "Page hits", "Sessions", "Last Seen");
		
		if(class_exists('cpt_topics') )
		{
			$csvHeadingArray[] = "Progress";
		}	
		
		
		
		$csvArray[] = $csvHeadingArray;
		
		
		foreach ( $userList as $userInfo )
		{

			$userID = $userInfo->ID;
			$username = $userInfo->user_login;
			$firstName = get_user_meta($userID, 'first_name', true);
			$lastName = get_user_meta($userID, 'last_name', true);
			
			$userPageImpressions = 0;
			$lastAccessDate = 'Never';
			$totalSessions = 0;
			
			// Get the actiivty for this studentList
			$args = array(
				"userID"	=> $userID,
			);			
			$myActivity = ek_user_stats_queries::getActivity($args);
						
			$thisUserLookupArray = array();
			$myActivityLookupArray = array();
			
			// Go through the array and put activity into array with userID as the key
			foreach($myActivity as $activityInfo)
			{
				
				$pageID = $activityInfo['page_id'];
				$pageURL = $activityInfo['pageURL'];
				$activityDate = $activityInfo['read_date'];
				$currentSession = $activityInfo['currentSession'];
				
				// Add this item to the user ID array
				$myActivityLookupArray[] = array
				(
					"pageID"			=> $pageID,
					"pageURL"			=> $pageURL,
					"activityDate"		=> $activityDate,
					"currentSession"	=> $currentSession,
				);
				
				// Also update the quick progres lookup
				$pageID = $pageID;
				$thisUserLookupArray[$pageID] = true;	
				
			}
			

			$userPageImpressions = count($myActivityLookupArray);
			if($userPageImpressions>=1)
			{
				$lastItem  = array_values(array_slice($myActivityLookupArray, -1))[0];
				$lastAccessDate = $lastItem["activityDate"];
				$totalSessions = $lastItem["currentSession"];
			}
				

			$user_fullname = $lastName.', '.$firstName;
			
			
			
			// Finally get the progress for this studentList
			
			// Go through the page array = see if there is a corresponding KEY				
			if(class_exists('cpt_topics') )
			{
		
				$progressBar = '';
				$thisUserPageCount = 0;								
				$totalPages = count($trackedPages);
				foreach ($trackedPages as $checkPageID)
				{
					if (array_key_exists($checkPageID,$thisUserLookupArray))
					{
						$thisUserPageCount++;
					}					
				}
				
				$progress = round( ($thisUserPageCount / $totalPages * 100), 0);	
				
				$progressBar = imperialCourse_draw::drawProgressBar($progress);
			
			}
			$html.= '<tr>';		
			$html.= '<td><a href="admin.php?page=ek-user-stats-student-list&view=user-overview&userID='.$userID.'">'.$user_fullname.'</a></td>';
			$html.= '<td>'.$username.'</td>';
			$html.= '<td><span style="color:#ccc">'.$userID.'</span></td>';
			$html.= '<td>'.$userPageImpressions.'</td>';
			$html.= '<td>'.$totalSessions.'</td>';	
			$html.= '<td>'.$lastAccessDate.'</td>';
			
			if(class_exists('cpt_topics') )
			{			
				$html.= '<td>'.$progressBar.'</td>';
				$html.= '<td>'.$progress.'</td>';
			
			}
			$html.= '</tr>';
			
			$csvArrayValues = array($user_fullname, $username, $userPageImpressions, $totalSessions, $lastAccessDate);

			
			
			
			if(class_exists('cpt_topics') )
			{	
				$csvArrayValues[] = $progress.'%';
			}
			
			$csvArray[] = $csvArrayValues;
			
			
			
			
		}
		$html.= '</tbody>';
		$html.= '</table>';
		$html.='	
		<script>
		jQuery(document).ready(function() {
				jQuery(\'#studentList\').DataTable( {
				bAutoWidth:false,
				"order": [[ 0, "asc" ]],
				
				
				';
				
				if(class_exists('cpt_topics') )
				{	
			
					$html.='
					\'columnDefs\': [
						{ \'orderData\':[7], \'targets\': [6] },
						{
							\'targets\': [7],
							\'searchable\': false,
							\'visible\': false,
						},
					],	
					';	
				}

				$html.='			
					
					
				} );
			} );
		</script>';	
		
		if($csv==true)
		{
		return $csvArray;	
		}
		else
		{
			return $html;
		}


	}	
	
	
	
	public static function drawUserOverview($userID)
	{


		$args = array(
			"userID"	=> $userID,
		);
		$userLog = ek_user_stats_queries::getActivity($args);

		$highestSession = 0;
		foreach ($userLog as $activityMeta)
		{
			$thisSession = $activityMeta['currentSession'];
			
			if($highestSession<$thisSession)
			{
				$highestSession = $thisSession;
			}
		}
		
		$totalHits = count($userLog);
		
		echo '<b>'.$highestSession.'</b> browsing sessions<hr/>';
		echo '<b>'.$totalHits.'</b> total page hits<hr/>';

		
		

		$myChartData = ek_user_stats_utils::getDeviceCharts($userLog);

		$deviceTypeData = $myChartData['deviceType'];
		$platformData = $myChartData['platform'];
		$browserData = $myChartData['browser'];


		$ek_gCHARTS = new ek_gCHARTS();
		
		echo '<div class="contentBox ek-statsChart">';
		echo '<h1>Access by Browser</h1>';
		$ek_gCHARTS->draw( 
			'pie', 				//chart type
			$browserData, 		//chart data
			'browserTypeDiv', 	//html element ID
			'Broswer', 		//label for keys
			'Access Count', 	//label for values
			''		//chart title
		);	

		echo '</div>';		

		
		echo '<div class="contentBox ek-statsChart">';
		echo '<h1>Access by Platform</h1>';
		$ek_gCHARTS->draw( 
			'pie', 				//chart type
			$platformData, 		//chart data
			'platformTypeDiv', 	//html element ID
			'Platform', 		//label for keys
			'Access Count', 	//label for values
			''		//chart title
		);
		echo '</div>';		
		
		
		echo '<div class="contentBox ek-statsChart">';
		echo '<h1>Access by Device Type</h1>';
		$ek_gCHARTS->draw( 
			'pie', 				//chart type
			$deviceTypeData, 		//chart data
			'deviceTypeDiv', 	//html element ID
			'Device', 		//label for keys
			'Access Count', 	//label for values
			''		//chart title
		);
		echo '</div>';
	
		
	}
	
	
	
	public static function drawUserActivity($userID)
	{

		$args = array(
			"userID"	=> $userID,
		);
		
		$actvityLog = ek_user_stats_queries::getActivity($args);
		
		
		echo '<table id="activityLog">';
		echo '<thead><tr><th>Date</th><th>Page</th><th>Session</th><th>Device Type</th><th>Browser</th></tr></thead>';
		echo '<tbody>';
		foreach($actvityLog as $activityInfo)
		{
			$activityDate = $activityInfo['read_date'];
			$pageID = $activityInfo['page_id'];
			$pageURL = $activityInfo['pageURL'];
			$activityDate = $activityInfo['read_date'];
			$deviceType = $activityInfo['deviceType'];
			$platform = $activityInfo['platform'];
			$browser = $activityInfo['browser'];
			$currentSession = $activityInfo['currentSession'];
			
			echo '<tr>';
			echo '<td>'.$activityDate.'</td>';
			echo '<td>'.$pageURL.'</td>';
			echo '<td>'.$currentSession.'</td>';
			echo '<td>'.$deviceType.'</td>';
			echo '<td>'.$platform.' : '.$browser.'</td>';
			
			echo '</tr>';
			
		}

		echo '</tbody>';
		echo '</table>';


		?>	
		<script>
		jQuery(document).ready( function () {
			jQuery('#activityLog').DataTable(		
			{
				"order": [[ 0, "desc" ]]
			} 
			);
		} );

		
		
		</script>
		<?php
		
		
		

		
	}	
	
	
	public static function userMiniMenu($userID)
	{
		$rootURL = "admin.php?page=ek-user-stats-student-list&userID=".$userID.'&view=';
		$html= '<ul>';
		$html.= '<li><a href="'.$rootURL.'user-overview">Overview</a></li>';
		$html.= '<li><a href="'.$rootURL.'user-activity">Activity Log</a></li>';
			
		if(class_exists('cpt_topics') )
		{			
			$html.= '<li><a href="'.$rootURL.'user-topic-progress">Topic Progress</a></li>';
		}

		
	//	$html.= '<li><a href="'.$rootURL.'user-sessions">Session Routes</a></li>';		
		$html.= '</ul>';

		return $html;		
		
	}
	
	
	public static function drawPreloader()
	{
		$preloader = '<div class="cssload-loader">
			<div class="cssload-inner cssload-one"></div>
			<div class="cssload-inner cssload-two"></div>
			<div class="cssload-inner cssload-three"></div>	
		</div>
		';
		
		return $preloader;
	}
	
	
	static function drawTimeGraph($args=array())
	{
		
		
		$filterStr =  ek_user_stats_draw::drawFiltering();
		$startDate  = $args['startDate'];
		$endDate  = $args['endDate'];
		$chartTitle = $args['chartTitle'];
		$filterType=$args['filterType'];
		
		
		$siteData = ''; // Blank Var for returning if not network
		$network = false;
		
		if(isset($args['network']) )
		{
			$network=true;
		}
		
		
		$args = array(
			"startDate"	=> $startDate,
			"endDate"	=> $endDate,
			"filterType" => $filterType,
		);
		
		if($network==true)
		{
			$returnData = ek_user_stats_queries::getNetworkStatsDataForGraph($args);		
			$graphData = $returnData['graphData'];
			$siteData = $returnData['siteData'];
		}
		else
		{
			$graphData = ek_user_stats_queries::getStatsDataForGraph($args);			
		}	
		

		$totalHits = 	$graphData['totalHits'];	
		$uniqueUsers = 	$graphData['uniqueUsers'];	
		$overviewStats = '';
		
		
		$overviewStats.= '<div class="statsChartHits"><div class="chartStatsData">'.$totalHits.'</div><div>Total Hits</div></div>';
		$overviewStats.= '<div class="statsChartUsers"><div class="chartStatsData">'.$uniqueUsers.'</div><div>Unique Users</div></div>';
		
		
		
		$graphTop = '';
		$graphTop.= '<div class="graphTopRow">';

		$graphTop.=$filterStr;
		$graphTop.=$overviewStats;		
		$graphTop.='</div>';
		
		echo $graphTop;

		echo '<div class="chartStatsWrap">';		
		
		
		$ek_gCHARTS = new ek_gCHARTS();
		
		
		$graphArgs = array(
			"data"	=> $graphData,
			"elementID"	=> "myData",
			"chartTitle"	=> $chartTitle,
		);
		
		$ek_gCHARTS->drawCombo( $graphArgs );
		
		echo '</div>';	
		
		// Return the raw data
		return $siteData;
		


	}
	
	
	static function drawFiltering()
	{
		
		$html='';

		// Get earliest date for the report
		$firstHit = ek_user_stats_queries::getFirstHit();
		$firstHitYear = date('Y', strtotime($firstHit));
		$firstHitMonth = date('m', strtotime($firstHit));
		$lastHitYear = date('Y');
		$lastHitMonth = date('m');		
		
		//$html.= '<div class="ek-stats-dash-filter">';
		//$html.= '<div class="chartFilteringOptions">';
	
		$html.= '<div>';
		$html.= '<form action="" method="POST">';
		$html.= 'By Year<br/>';
		$html.= '<select name="yearFilter">';
		$tempYear = $lastHitYear;
		while($tempYear>=$firstHitYear)
		{
			
			$html.= '<option value="'.$tempYear.'" ';
			
			$html.= '>'.$tempYear.'</option>';
			$tempYear--;
		}
		$html.= '</select>';
		$html.= '<input type="hidden" value="year" name="filterType">';
		$html.= '<input type="submit" value="View Yearly Stats" class="button-secondary">';
		$html.= '</form>';
		$html.= '</div>';

		// Monthly filtering
		$html.= '<div>';
		$html.= '<form action="" method="post">';
		$html.= 'By Month<br/>';
		$html.= '<select name="monthFilter">';
		$tempYear = $lastHitYear;
		while($tempYear>=$firstHitYear)
		{
			$tempMonth=12;	
			while($tempMonth>=1)
			{
				$dateObj   = DateTime::createFromFormat('!m', $tempMonth);
				$monthName = $dateObj->format('F');
				$drawMonth=true;		
				// Are they in the future?
				if($tempYear==$lastHitYear)
				{
					if($lastHitMonth<$tempMonth)
					{
						$drawMonth=false;
					}
				}		
				
				// Are they before stats recorded?
				if($tempYear==$firstHitYear)
				{
					if($firstHitMonth>$tempMonth)
					{
						$drawMonth=false;
					}
				}		
				
				
				if($drawMonth == true)
				{
					
					$tempValue = $tempMonth;
					if($tempValue<10){$tempValue='0'.$tempValue;}
					$tempValue = $tempYear.'-'.$tempValue;
					$html.= '<option value="'.$tempValue.'" ';		
					$html.= '>- '.$monthName.' '.$tempYear.'</option>';
				}
				$tempMonth--;
			}
			$tempYear--;
		}

		$html.= '</select>';
		$html.= '<input type="hidden" value="month" name="filterType">';
		$html.= '<input type="submit" value="View Monthly Stats" class="button-secondary">';
		$html.= '</form>';
		$html.= '</div>';
		//$html.= '</div>';
		//$html.= '</div>'; // end of filtering Div		
		
		
		return $html;
		
		
		
		
	}
	
}

?>
