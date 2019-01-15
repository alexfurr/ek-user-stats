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
<h1>Student List</h1>
<?php
echo ek_user_stats_draw::drawPreloader();

echo '<div class="ek-preload-content">'; // Start of preload content


$view="";

if(isset($_GET['view']) )
{
	$view = $_GET['view'];
}


switch ($view)
{
	
	case "user-overview":
	case "user-activity":
	case "user-topic-progress":	
	{
	
	
		$userID = $_GET['userID'];
		$userMeta = get_user_meta($userID);
		
		$firstName = $userMeta['first_name'][0];
		$lastName = $userMeta['last_name'][0];	

		echo '<h2>'. $firstName.' '.$lastName .'</h2>';
		$userMenu = ek_user_stats_draw::userMiniMenu($userID);
		echo $userMenu;
		
		
		switch ($view)
		{
			
			case "user-overview":
			{
				ek_user_stats_draw::drawUserOverview($userID);
				break;

			}
			
			case "user-activity":
			{
				ek_user_stats_draw::drawUserActivity($userID);				
				break;
			}
			
			case "user-topic-progress":
			{
				echo ek_user_stats_draw::drawUserTopicActivity($userID);				
				break;
			}			
			



		}
		
		break;
	}

	
	
	default:
	

		echo '<a href="?page=ek-user-stats-student-list&myAction=exportEKstatsUserList" class="button-secondary">Download as CSV</a>';
		echo ek_user_stats_draw::drawAllStudentsLog();
	
	break;
	
	
}


echo '</div>'; // End of preload content


?>