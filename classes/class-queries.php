<?php
class ek_user_stats_queries
{
	static function getTotalHits()
	{
		global $wpdb;
		global $ek_user_stats_db;
		$dbName = $ek_user_stats_db->dbTable_usersStats;		
		$stats_table = $wpdb->prefix . $dbName;		
		$SQL='Select id FROM '.$stats_table;
		$rs = $wpdb->get_results( $SQL, ARRAY_A );
		return $rs;
	}
	
	// Get the year and month of first hit for graph filtering
	static function getFirstHit()
	{
		global $wpdb;
		global $ek_user_stats_db;
		$dbName = $ek_user_stats_db->dbTable_usersStats;		
		$stats_table = $wpdb->prefix . $dbName;		
		$SQL='Select read_date FROM '.$stats_table.' ORDER by read_date ASC LIMIT 1';
		$rs = $wpdb->get_row( $SQL, ARRAY_A );
		
		$thisDate = $rs['read_date'];
		
		return $thisDate;
	}	
	
	static function getUniqueUsers()
	{
		global $wpdb;
		global $ek_user_stats_db;
		$dbName = $ek_user_stats_db->dbTable_usersStats;		
		$stats_table = $wpdb->prefix . $dbName;		
		$SQL='Select user_id FROM '.$stats_table.' GROUP BY user_id';		
		$rs = $wpdb->get_results( $SQL, ARRAY_A );
		return $rs;			
	}	
	
	static function getActivity($args="")
	{

		$userID='';
		//$userWhere = '';
		
		$filterQry = '';
		
		$userType='';
		//$userTypeWhere='';
		//$startDateFilter = '';
		//$endDateFilter = '';
		
		$startDate='';
		$endDate='';
				
		
		if(isset($args['userID']) )
		{
			$userID = $args['userID'];
			$filterQry.= ' WHERE user_id = '.$userID;
			
		}
		
		if(isset($args['userType']) )
		{
			$userType = $args['userType'];

			switch ($userType)
			{
				case "notAdmins":

					if($filterQry=="")
					{
						$filterQry=' WHERE ';
					}
					
				
					// Get a list of editors and admins
					$users = get_users( [ 'role__in' => [ 'editor', 'administrator' ] ] );					
					$i=1;
					foreach ( $users as $user ) {
						$userID = $user->ID;

						if($i>1)
						{
							$filterQry.= ' AND ';
						}
						$filterQry.= ' user_ID<>'.$userID.' ';
						
						$i++;						
					}
				
				break;
				
				case "subscribers":
				
				
					if($filterQry=="")
					{
						$filterQry = ' WHERE ';
					}
					
					// Get a list of editors and admins
					$users = get_users( [ 'role__in' => [ 'subscriber' ] ] );					
					$i=1;
					foreach ( $users as $user ) {
						$userID = $user->ID;

						if($i>1)
						{
							$filterQry.= ' OR ';
						}
						$filterQry.= ' user_ID='.$userID.' ';
						
						$i++;						
					}
				
				break;				
				
				
				
			}
		}
		
		
		// Filter by date range
		if(isset($args['startDate']) )
		{
			
			if($filterQry=="")
			{
				$filterQry = ' WHERE ';
			}
			else
			{
				$filterQry.= ' AND ';
			}
			
			
			$startDate = $args['startDate'];			
			$filterQry.= ' read_date >= "'.$startDate.'"';
		}		
		
		if(isset($args['endDate']) )
		{
			if($filterQry=="")
			{
				$filterQry = ' WHERE ';
			}
			else
			{
				$filterQry.= ' AND ';
			}			
			
			$endDate = $args['endDate'];			
			$filterQry.= ' read_date <= "'.$endDate.'"';
		}				
		
		

		
		global $wpdb;
		global $ek_user_stats_db;
		$dbName = $ek_user_stats_db->dbTable_usersStats;		
		$stats_table = $wpdb->prefix . $dbName;	
		
		//$SQL='Select pageURL, user_id, read_date, currentSession, page_id FROM '.$stats_table.$userWhere;
		$SQL='Select * FROM '.$stats_table.$filterQry.' ORDER by id ASC';
		
		$rs = $wpdb->get_results( $SQL, ARRAY_A );

		return $rs;
	}
	
	// Get Activity from a specific blog - for network wide reporting
	static function getActivityFromBlogID($blogID)
	{

		
		global $wpdb;
		global $ek_user_stats_db;
		$dbName = $ek_user_stats_db->dbTable_usersStats;		
		$stats_table = $wpdb->prefix . $dbName;	
		
		//$SQL='Select pageURL, user_id, read_date, currentSession, page_id FROM '.$stats_table.$userWhere;
		$SQL='Select user_id, page_id FROM '.$stats_table;
				
		$rs = $wpdb->get_results( $SQL, ARRAY_A );
		return $rs;
	}	
	
	
	static function getStatsDataForGraph($args)
	{

		$startDate = $args['startDate'];
		$endDate = $args['endDate'];
		$filterType=$args['filterType'];
			
		$args = array(
			"startDate"	=> $startDate,
			"endDate"	=> $endDate,
		);
		$myActivity = ek_user_stats_queries::getActivity($args);

		// Create blank array of the hits etc
		$dataArray = array();
		
		$masterTotalHits = 0;
		$uniqueUsersMasterArray = array();
		
		
		
		switch ($filterType)
		{
			case "year":
			
			
				$thisYear = date('Y', strtotime($startDate) );

				
				// Create the array
				$i=1;
				while ($i<=12)
				{
					$monthValue = $i;
					
					// Ao
					if($monthValue<10){$monthValue = '0'.$monthValue;}		
					$monthValue = date('F', strtotime($thisYear.'-'.$monthValue) );
					
					$dataArray[$monthValue]['totalHits'] = 0;
					$dataArray[$monthValue]['uniqueUsers'] = array();					
					$i++;
				}
				
				foreach($myActivity as $activityMeta)
				{
					$read_date = $activityMeta['read_date'];
					$read_date = date('F', strtotime($read_date) );
					$userID = $activityMeta['user_id'];
					
					// Increment the total hits by one
					$totalHits = $dataArray[$read_date]['totalHits'];		
					$newHits = $totalHits+1;
					
					$dataArray[$read_date]['totalHits'] = $newHits;
					$dataArray[$read_date]['uniqueUsers'][$userID] = true;
					
					// Set the master hits and master unique users array
					$masterTotalHits++;
					$uniqueUsersMasterArray[$userID]=true;
				}		
				
		
			break;
			
			
			default:
			
			
			
			
				$period = new DatePeriod(
					 new DateTime($startDate),
					 new DateInterval('P1D'),
					 new DateTime($endDate)
				);			
			
				foreach ($period as $key => $value) {
					$thisDate =  $value->format('jS (D)');
					$checkMonday = $value->format('N');
					if($checkMonday==1)
					{
						$thisDate =  $value->format('jS (D)');
					}
					else
					{
						$thisDate =  $value->format('jS');
					}
					$dataArray[$thisDate]['totalHits'] = 0;
					$dataArray[$thisDate]['uniqueUsers'] = array();
				}
				
				
				
				foreach($myActivity as $activityMeta)
				{
					$read_date = $activityMeta['read_date'];
					$checkMonday = date('N', strtotime($read_date) );

					if($checkMonday==1)
					{
						$read_date = date('jS (D)', strtotime($read_date) );
					}
					else
					{
						$read_date = date('jS', strtotime($read_date) );
					}					
					
					
					$userID = $activityMeta['user_id'];
					
					// Increment the total hits by one
					$totalHits = $dataArray[$read_date]['totalHits'];		
					$newHits = $totalHits+1;
					
					$dataArray[$read_date]['totalHits'] = $newHits;
					$dataArray[$read_date]['uniqueUsers'][$userID] = true;
					
					// Set the master hits and master unique users array
					$masterTotalHits++;
					$uniqueUsersMasterArray[$userID]=true;
					
				}				
			
			
			
			
			
			
			
			break;
			
		}
		
	
		
		
		// Create arrays of the data
		$graphData['dataCols'] = array(
			"Date", // The Vertical Axis Name
			
			// The first set of data meta
			array(
				"name" => "Total  Hits",
				"type" => "bar",
			),
			
			// The scond data meta
			array(
				"name" => "Unique Users",
				"type" => "line",

			),
		);
		
		$graphData['data'] = array();


		foreach($dataArray as $thisDay => $thisDayData)
		{
			$thisDayHits = $thisDayData['totalHits'];
			$thisDayUniqueUsers = count($thisDayData['uniqueUsers']);
			
			$graphData['data'][$thisDay][] = $thisDayHits;			
			$graphData['data'][$thisDay][] = $thisDayUniqueUsers;			
		}
		
		$graphData['totalHits'] = $masterTotalHits;
		$graphData['uniqueUsers'] = count($uniqueUsersMasterArray);
		

		return $graphData;		
	


	}
	
	
	public static function getLast10activeUsers()
	{

			global $wpdb;
		global $ek_user_stats_db;
		$dbName = $ek_user_stats_db->dbTable_usersStats;		
		$stats_table = $wpdb->prefix . $dbName;	
		
		//$SQL='Select pageURL, user_id, read_date, currentSession, page_id FROM '.$stats_table.$userWhere;
		$SQL='SELECT user_id,  MAX(read_date), read_date
		FROM '.$stats_table.' GROUP BY user_id DESC LIMIT 10';
		

		$rs = $wpdb->get_results( $SQL, ARRAY_A );
		return $rs;		
		
		
		
		
	}
	
	
	
	
	
} //Close class