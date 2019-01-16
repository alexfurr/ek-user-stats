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
?>
<h1>Reports</h1>





<?php

echo '<div class="ek-preload-content">'; // Start of preload content




$userType = "";

if(isset($_GET['userType']) )
{
	$userType = $_GET['userType'];
}

switch ($userType)
{
	case "subscribers":
		$args = array(
			"userType"	=> "subscribers",
		);
		$activityLog = ek_user_stats_queries::getActivity($args);	
	break;
	
	case "notAdmins":
		$args = array(
			"userType"	=> "notAdmins",
		);
		$activityLog = ek_user_stats_queries::getActivity($args);	
	break;	
	
	default:
		$activityLog = ek_user_stats_queries::getActivity();	
	break;

}


$filterTypeArray = array
(
	"All Users" => "",
	"All but admins / editors" => "notAdmins",
	"Subscribers Only" => "subscribers",
);


echo '<select name="filterType">';

foreach ($filterTypeArray as $filterStr => $filterValue)
{


	echo '<option value="'.$filterValue.'"';
	if($userType==$filterValue){echo ' selected';}
	echo '>';
	echo $filterStr.'</option>';
}

echo '</select>';
?>
<script>
jQuery('select').on('change', function() {
  filterType = this.value;  
  var url = "?page=ek-user-stats-reports&userType="+filterType;
  window.location = url; // redirect
});
</script>

<?php

/*
echo '<a href="?page=ek-user-stats-reports" class="button-primary">All Users</a> | ';
echo '<a href="?page=ek-user-stats-reports&userType=notAdmins">All but admins / editors</a> | ';
echo '<a href="?page=ek-user-stats-reports&userType=subscribers">Subscribers Only</a> | ';
*/

// Show type of devices

$myChartData = ek_user_stats_utils::getDeviceCharts($activityLog);

$deviceTypeData = $myChartData['deviceType'];
$platformData = $myChartData['platform'];
$browserData = $myChartData['browser'];

$ek_gCHARTS = new ek_gCHARTS();

echo '<div class="containerChart">';

echo '<div class="contentBox-01 ek-statsChart">';

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


echo '<div class="contentBox-02 ek-statsChart">';
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


echo '<div class="contentBox-03 ek-statsChart">';
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
echo '</div>'; // End of container chart

echo '</div>'; // End of preload content
echo ek_user_stats_draw::drawPreloader();



?>