<?php

if ( ! defined( 'ABSPATH' ) ) 
{
	die();	// Exit if accessed directly
}

// Only let them view if admin		
if(!current_user_can('manage_network_options'))
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
	
	
	
$args['network']=true;
	
	
echo '<h1>'.$chartTitle.'</h1>';



echo '<div class="ek-stats-main-dash">'; // Start of preload content

echo '<div class="ek-stats-main-dash-left">';



echo '<div class="ek-stats-dash-graph">';
if(isset($_REQUEST['filterType']) )
{

	$masterDataArray = ek_user_stats_draw::drawTimeGraph($args);
	
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
	
	$masterDataArray = ek_user_stats_draw::drawTimeGraph($args);
}



echo '</div>'; // End of Graph Div
echo '</div>'; // End of left col

echo '<div class="ek-stats-main-dash-right" style="padding:20px;">';
// Get the total users in the 


echo '<h1>Most Visited Sites</h1>';


foreach ($masterDataArray as $key => $row) {
    $totalHits[$key] = $row['totalHits'];
}

array_multisort($totalHits, SORT_DESC, $masterDataArray);



$i=0;
while ($i<=10)
{
	$tempData = $masterDataArray[$i];
	$blogName = $tempData['blogName'];
	$totalHits = $tempData['totalHits'];
	$users = $tempData['users'];
	$uniqueUsers = count($users);
	$blogURL = $tempData['blogURL'];
	
	if($totalHits>=1)
	{
	
	
	
		echo '<h3><a href="'.$blogURL.'">'.$blogName.'</a></h3>';
		echo $totalHits.' hits<br/>';
		echo $uniqueUsers.' users';
		echo '<hr/>';
	}
	
	
	$i++;
}


echo '</div>'; // End ofmain wrap



?>
