<?php

if ( ! defined( 'ABSPATH' ) ) 
{
	die();	// Exit if accessed directly
}

// Only let them view if admin		
if(!current_user_can('delete_others_pages'))
{
	die();
}

$args = array();
$chartTitle = 'This Week';
if(isset($_REQUEST['filterType']) )
{


	$filterType= $_REQUEST['filterType'];	
	
	switch($filterType)
	{
		case "month":				
		
			$startDate = $_REQUEST['monthFilter'].'-01';			
			$tempNextMonthDate = new DateTime($startDate);
			$tempNextMonthDate->modify('first day of next month');
			$endDate = $tempNextMonthDate->format('Y-m-d');
			
			
			// Get the chart title by converting to text
			$chartTitleMondayDate = new DateTime($startDate);
			$chartTitle = $chartTitleMondayDate->format('F Y');
		
			
			
			$args['filterType'] = "month";
			$args['startDate'] = $startDate;
			$args['endDate'] = $endDate;
			$args['chartTitle'] = $chartTitle;		
		


			
		break;
		
		case "year":
		
			$startDate = $_REQUEST['yearFilter'].'-01-01';			
			
			$tempNextYearDate = new DateTime($startDate);
			$tempNextYearDate->modify('first day of next year');
			$endDate = $tempNextYearDate->format('Y-m-d');	

			$args['filterType'] = "year";
			$args['startDate'] = $startDate;
			$args['endDate'] = $endDate;
			$chartTitle = $_REQUEST['yearFilter'];
			$args['chartTitle'] = $chartTitle;
			
		
		break;
	}
}
	
	
	
	
echo '<h1>'.$chartTitle.'</h1>';



echo '<div class="ek-stats-main-dash">'; // Start of preload content

echo '<div class="ek-stats-main-dash-left">';



echo '<div class="ek-stats-dash-graph">';
if(isset($_REQUEST['filterType']) )
{
	ek_user_stats_draw::drawTimeGraph($args);
	
}
else
{
	// Show the last 7 days
	
	
	$endDate = date('Y-m-d');	
	
	$chartTitle = 'This Week';

	$lastWeek = new DateTime($endDate);
	$lastWeek->modify('-7 day');
	$startDate = $lastWeek->format('Y-m-d');	
	
	$args['filterType'] = "week";
	$args['startDate'] = $startDate;
	$args['endDate'] = $endDate;
	$args['chartTitle'] = $chartTitle;
	
	ek_user_stats_draw::drawTimeGraph($args);
}



echo '</div>'; // End of Graph Div
echo '</div>'; // End of left col

echo '<div class="ek-stats-main-dash-right">';
// Get the total users in the 


echo '<div class="statsPercentVisited">';

echo '<div class="ek-stats-heading"><i class="fas fa-user-friends"></i> User Overview</div>';
// Get all users
$masterUserCheckArray = array();

// Get the master activity and check users vs the user list
$masterActivity = ek_user_stats_queries::getActivity();

foreach ($masterActivity as $activityMeta)
{
	$tempUserID = $activityMeta['user_id'];	
	$masterUserCheckArray[$tempUserID] = true;
}

$activeUsers = get_users();

$visitedUsersCount=0;
foreach ($activeUsers as $userMeta)
{
	$thisUserID = $userMeta->ID;	
	
	if(array_key_exists($thisUserID, $masterUserCheckArray) )
	{		
		$visitedUsersCount++;
	}
}

$totalUsers = count($activeUsers);


if($totalUsers==0 || $visitedUsersCount==0)
{
	$percentVisited = 0;
}
else
{
	$percentVisited = round((($visitedUsersCount / $totalUsers*100)), 0);
}


$neverVisit = 100-$percentVisited;

$visitedArrayData = array
(
	array ("Visited", $percentVisited),
	array ("Never Visited", $neverVisit),
);
$ek_gCHARTS = new ek_gCHARTS();

$ek_gCHARTS->draw( 
	'pie', 				//chart type
	$visitedArrayData, 		//chart data
	'platformTypeDiv', 	//html element ID
	'Platform', 		//label for keys
	'Access Count', 	//label for values
	'',		//chart title
	'100%', // width
	'200px' // height
);



echo '</div>';





// Draw Active users

// Get all the users on the blog 

$activeUsers = get_users([
    'meta_key' => 'session_tokens',
    'meta_compare' => 'EXISTS'
]);


$last10users = ek_user_stats_queries::getLast10activeUsers();

$activeCount = count($last10users);


echo '<div class="statsActiveUersWrap">';
echo '<div class="ek-stats-heading"><i class="fas fa-user-circle"></i> Last Active Users</div>';


echo '<div class="statsDashUserList">';

$i=1;
foreach ($last10users as $userInfo)
{
	
	
	$userID = $userInfo['user_id'];
	$lastActiveDate = $userInfo['read_date'];
	
	$userMeta = get_userdata( $userID );
	$username = $userMeta->user_login;	
	
	// Get the last time they were active		
	$args = array(
		"userID"	=> $userID,
	);
	
	
	// Get the user info
	$imperialUserMeta = imperialQueries::getUserInfo($username);
	$userType=$imperialUserMeta['user_type'];
	$CID =$imperialUserMeta['userID'];
	$firstName = $imperialUserMeta['first_name'];
	$lastName = $imperialUserMeta['last_name'];
	$fullname = $firstName.' '.$lastName;
		
	if(strlen($CID)==8) 
	{
		// Get the avatar
		$args = array(			
			"CID"		=> $CID,
		);
	}
	else
	{
		// Get the avatar
		$args = array(			
			"userID"		=> $userID,
		);
	}
	
	$avatarURL = get_user_avatar_url( $args);	
	

	// How long ago
	$start_date = new DateTime($lastActiveDate);
	$since_start = $start_date->diff(new DateTime());
	$daysSinceActive = $since_start->d;	
	$hoursSinceActive = $since_start->h;	
	$minutesSinceActive = $since_start->i;
	$secondsSinceActive = $since_start->s;
	
	$sinceStr = '';
	

	if($daysSinceActive>1)
	{
		$sinceStr.=$daysSinceActive .' days ago';
	}
	elseif($daysSinceActive==1)
	{
		$sinceStr.=$daysSinceActive .' day ago';
	}
	elseif($hoursSinceActive>=1)
	{
		$sinceStr.=$hoursSinceActive .' hours ago';
	}
	elseif($hoursSinceActive==1)
	{
		$sinceStr.=$hoursSinceActive .' hour ago';
	}	
	else
	{
		if($minutesSinceActive==0)
		{
			$sinceStr = '< a minute ago';
		}
		elseif($minutesSinceActive==1)
		{
			$sinceStr = '1 minute ago';		
		}
		else
		{
			$sinceStr = $minutesSinceActive.' minutes ago ('.$lastActiveDate.')';
		}
	}
	
	


	echo '<a href="?page=ek-user-stats-student-list&view=user-activity&userID='.$userID.'">';
	echo '<div class="statsDashListUser">';
	echo '<div class="statsDashListAvatar"><img src="'.$avatarURL.'"></div>';
	echo '<div>';
	echo '<div class="statsDashListName">'.$fullname.'</div>';
	echo '<div class="statsDashListSince">'.$sinceStr.'</div>';		
	echo '</div>';
	echo '</div>';
	echo '</a>';
		

}


echo '</div>';
echo '</div>'; // end of statsActiveUersWrap


echo '</div>'; // End of right col

echo '</div>'; // End ofmain wrap



?>
