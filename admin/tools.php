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

<h1>Tools</h1>




<?php

$showOptions=true;

if(isset($_GET['action']))
{
	$action=$_GET['action'];
	
	switch ($action) {
		case "deleteAfterDate":
		case "deleteBeforeDate":
		case "deleteAll":
		{
			
	

			$showOptions = false;
			echo '<form method="post" action="admin.php?page=ek-user-stats-admin&action=confirmDelete&type='.$action.'">';
			
			
			switch ($action)
			{
				case "deleteAfterDate":
				{
					
					$deleteAfterDate = $_POST['deleteAfterDate'];
					echo 'Are you sure you want to delete all data after <b>'.$deleteAfterDate.'</b><br/>';
					echo '<input type="text" value="'.$deleteAfterDate.'" name="deleteAfterDate">';
	
					
					break;
				}
				
				case "deleteBeforeDate":
				
					$deleteBeforeDate = $_POST['deleteBeforeDate'];
					echo 'Are you sure you want to delete all data before <b>'.$deleteBeforeDate.'</b><br/>';
					echo '<input type="text" value="'.$deleteBeforeDate.'" name="deleteBeforeDate">';
				
				
				break;
				
				case "deleteAll":
					echo 'Are you sure you want to delete ALL STATS<br/>';
				
				break;
				
			}
			

			echo '<input type="submit" value="Delete" class="button-primary">';
			echo '<a href="admin.php?page=ek-user-stats-admin" class="button-secondary">Cancel</a>';
			wp_nonce_field('deleteCheck');    



			echo '</form>';
			
			
			break;
			
		}
		
		

		

			
		case "confirmDelete":
		{
			
			$retrieved_nonce="";
			if(isset($_REQUEST['_wpnonce'])){$retrieved_nonce = $_REQUEST['_wpnonce'];}
			if (wp_verify_nonce($retrieved_nonce, 'deleteCheck' ) )
			{				
			

				echo '<div class="notice notice-success is-dismissible">';
				echo '<p>Data Deleted</p>';
				echo '</div>';
				
				
				$type = $_GET['type'];
				
				global $wpdb;
				global $ek_user_stats_db;
				$dbName = $ek_user_stats_db->dbTable_usersStats;
				$stats_table = $wpdb->prefix . $dbName;		


				
				
				
				switch ($type)
				{
					case "deleteAll":
					{
						$RunQry = $wpdb->query(	'DELETE FROM '.$stats_table	);			
						break;
					}
					
					case "deleteAfterDate":
					{

						$deleteAfterDate = $_POST['deleteAfterDate'];
						$RunQry = $wpdb->query( $wpdb->prepare(	'DELETE FROM '.$stats_table.' WHERE read_date >  %s',
							$deleteAfterDate
						));
						

						break;
					}
					
					case "deleteBeforeDate":
					{


						$deleteBeforeDate = $_POST['deleteBeforeDate'];
						$RunQry = $wpdb->query( $wpdb->prepare(	'DELETE FROM '.$stats_table.' WHERE read_date <  %s',
							$deleteBeforeDate
						));					
						
						
						break;
					}				
					
				
					
				}
			}
			
		}

		
		
		break;
	}	
	
}











if($showOptions==true)
{
	// Show Total Visits
	$totalHitsRS =  ek_user_stats_queries::getTotalHits();		
	$totalHits = count($totalHitsRS);
	
	//echo $totalHits;
	
	
	
	echo '<h2>Clean up access logs</h2>';
	echo '<span style="color:red">Warning : the below options can be undone</span><br/><br/>';
		
	echo '<a href="admin.php?page=ek-user-stats-admin&action=deleteAll" class="button-primary">Clear All Stats</a>';
	
	echo '<hr/>';
	
	
	echo '<form method="post" action="admin.php?page=ek-user-stats-admin&action=deleteBeforeDate">';
	echo '<label for="deleteBeforeDate">Delete Stats Before:<br/>';
	echo '<script>
		jQuery(document).ready(function() {
			jQuery("#deleteBeforeDate").datepicker({
				dateFormat : "yy-mm-dd"
			});
			
		});
	</script>';
	echo '<input type="text" id="deleteBeforeDate" name="deleteBeforeDate">';
	echo '</label>';	
	echo '<input type="submit" class="button-secondary" value="Delete" />';
	echo '</form>';

	echo '<hr/>';		
	
	echo '<form method="post" action="admin.php?page=ek-user-stats-admin&action=deleteAfterDate">';
	echo '<label for="deleteAfterDate">Delete Stats After:<br/>';
	echo '<script>
		jQuery(document).ready(function() {
			jQuery("#deleteAfterDate").datepicker({
				dateFormat : "yy-mm-dd"
			});
			
		});
	</script>';
	echo '<input type="text" id="deleteAfterDate" name="deleteAfterDate">';
	echo '</label>';
	echo '<input type="submit" class="button-secondary" value="Delete" />';
	
	echo '</form>';
		
}
		
		?>