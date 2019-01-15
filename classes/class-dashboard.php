<?php

class ek_user_stats_dashboard
{
	
	static function custom_dashboard_help()
	{
	
		
		// Show Total Visits
		$totalHitsRS =  ek_user_stats_queries::getTotalHits();		
		$totalHits = count($totalHitsRS);
		
		$fontSize = "40";
		if($totalHits>=100000)
		{
			$fontSize = "20";
		}
		elseif($totalHits>=10000)
		{
			$fontSize = "26";
		}
		elseif($totalHits>=1000)
		{
			$fontSize = "32";
		}

		$totalHitsStr= '<div class="userStatsDashbox">';
		$totalHitsStr.= '<div class="userStatsBig" style="font-size:'.$fontSize.'px">';
		$totalHitsStr.= $totalHits;
		$totalHitsStr.= '</div>';
		$totalHitsStr.= '<div class="userStatsDashboxContent">';				
		$totalHitsStr.= 'Page impressions';		
		$totalHitsStr.= '</div>';
		$totalHitsStr.= '</div>';
		
		// Show Unique Users
		$uniqueUsersRS =  ek_user_stats_queries::getUniqueUsers();	
		$uniqueUsersCount = count($uniqueUsersRS);

		$fontSize = "40";
		if($uniqueUsersCount>=1000)
		{
			$fontSize = "32";
		}		
		
		
		$totalUsersStr= '<div class="userStatsDashbox">';
		$totalUsersStr.= '<div class="userStatsBig">';
		$totalUsersStr.= $uniqueUsersCount;
		$totalUsersStr.= '</div>';
		$totalUsersStr.= '<div class="userStatsDashboxContent">';				
		$totalUsersStr.= 'People have Visited';		
		$totalUsersStr.= '</div>';
		$totalUsersStr.= '</div>';
		
		//echo '<div class="row userStatsDashWrap">';
        echo '<div class="dash-row userStatsDashWrap">';
        
		//echo '<div class="col-md-3">';
        echo '<div class="cell-25">';
		echo $totalHitsStr;
		echo '</div>';

		//echo '<div class="col-md-3">';	
		echo '<div class="cell-25">';
        echo $totalUsersStr;
		echo '</div>';
		
		//echo '<div class="col-md-6">';
        echo '<div class="cell-50 dash-quicklinks">';
		echo '<h2>Quick Reports</h2>';		
		echo '<a href="admin.php?page=ek-user-stats-admin">Activity log</a>';
		echo '<hr/>';
		echo '<a href="admin.php?page=ek-user-stats-student-list">Student List</a>';
		echo '<hr/>';
		echo '<a href="admin.php?page=ek-user-stats-reports">Reports</a>';		
		echo '</div>';
		
        echo '<br class="clear">';
		echo '</div>';
		
		
		
		
		
		
		
		
		
		
		
		
		
	}		

} //Close class