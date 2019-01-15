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
<h1>User Stats</h1>
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

<h2>Latest Activity</h2>


<?php

// Get the latest actvity

$activityLog = ek_user_stats_queries::getActivity();

// Get Array of users for user lookup
$userList = get_users();

// Array of WP_User objects.
$userLookupArray = array();
foreach ( $userList as $userInfo ) {
	
	$userID = $userInfo->ID;
	$username = $userInfo->user_login;
	$firstName = get_user_meta($userID, 'first_name', true);
    $lastName = get_user_meta($userID, 'last_name', true);
	
	$userLookupArray[$userID] = array(
		"username"	=> $username,
		"firstName"	=> $firstName,
		"lastName"	=> $lastName,
	);
}


echo '<div class="ek-preload-content">'; // Start of preload content


// Create lookup array in case user info is blank for any reason
$wpLookupArray = array();

echo '<table id="activityLog">';
echo '<thead><tr><th>Date</th><th>User</th><th>Username</th><th>WP User ID</th><th>Page</th><th>Device Type</th></tr></thead>';
echo '<tbody>';
foreach($activityLog as $activityInfo)
{
	$activityDate = $activityInfo['read_date'];
	$userID = $activityInfo['user_id'];
	$pageID = $activityInfo['page_id'];
	$pageURL = $activityInfo['pageURL'];
	$activityDate = $activityInfo['read_date'];
	$deviceType = $activityInfo['deviceType'];

	$username = '';
	$firstName = '';
	$lastName = '';
	if(isset($userLookupArray[$userID]["username"]) )
	{
		$username = $userLookupArray[$userID]["username"];
	}
	if(isset ($userLookupArray[$userID]["firstName"] ) )
	{
		$firstName = $userLookupArray[$userID]["firstName"];
	}
	
	if(isset ($userLookupArray[$userID]["lastName"] ) )
	{
		$lastName = $userLookupArray[$userID]["lastName"];	
	}
	$user_fullname = $firstName.' '.$lastName;
	
	// Add username details t temporary array if username is blank
	if($username=="")
	{
		
		if(isset($wpLookupArray[$userID]) )
		{
			$username = $wpLookupArray[$userID]['username'];
			$firstName = $wpLookupArray[$userID]['firstName'];
			$lastName = $wpLookupArray[$userID]['lastName'];
			
		}
		else // Create the lookup entry
		{
			$user_info = get_userdata($userID);
			$username = $user_info->user_login;
			$firstName = $user_info->first_name;
			$lastName = $user_info->last_name;	
			
			$wpLookupArray[$userID]['username'] = $username;
			$wpLookupArray[$userID]['firstName'] = $firstName;
			$wpLookupArray[$userID]['lastName'] = $lastName;
		}	
		$user_fullname = $firstName.' '.$lastName;
		
	}

	echo '<tr>';	
	
	echo '<td>'.$activityDate.'</td>';
	echo '<td>'.$user_fullname.'</td>';
	echo '<td>'.$username.'</td>';
	echo '<td>'.$userID.'</td>';
	echo '<td>'.$pageURL.'</td>';
	echo '<td>'.$deviceType.'</td>';
	
	echo '</tr>';
	
}



echo '</tbody>';

echo '</table>';

echo '</div>'; // End of preload content
echo ek_user_stats_draw::drawPreloader();



?>
<script>
jQuery(document).ready( function () {
    jQuery('#activityLog').DataTable();
} );
</script>
