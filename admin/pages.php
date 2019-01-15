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

<h1>Pages</h1>
				
<?php

$activityLog = ek_user_stats_queries::getActivity();


global $pageLogArray;
$pageLogArray = array();

foreach ($activityLog as $logInfo)
{
	$pageID = $logInfo['page_id'];
	$read_date = $logInfo['read_date'];
	$pageLogArray[$pageID][] = $read_date;
}

echo '<div class="statsPagesWrap">';
drawChildren(0);
echo '</div>';

function drawChildren($pageID)
{
	
	global $pageLogArray; 
	$args = array( // including all of the defaults here so you can play with them
	'sort_order' => 'ASC',
	'sort_column' => 'post_title',
	'hierarchical' => 1,
	'child_of' => 0,
	'parent' => $pageID,
	'offset' => 0,
	'post_type' => 'page',
	'post_status' => 'publish'
	); 
	$pages = get_pages( $args ); 
	$headerDrawn=false;

	foreach ( $pages as $page )
	{
		
		$pageID = $page->ID;
		$parentID = $page->post_parent;
		$pageName  = $page->post_title;
		
		$ancestors=get_post_ancestors($pageID);
		$parentCount=count($ancestors);
		
		$children=get_pages('child_of=' . $pageID);
		
		$childrenCount=count($children);	
		
		if(isset($pageLogArray[$pageID]) )
		{
			$hitCount = count($pageLogArray[$pageID]);
		}
		else
		{
			$hitCount =0;
		}
		
		echo '<div id="div_'.$pageID.'">';
		
		echo '<div class="statsWrap">';	
		echo '<table width="100%">';
		
		if($parentID==0 && $headerDrawn==false)
		{
			echo '<tr><th>Hits</th><th>Page</th></tr>';
			$headerDrawn = true;
		}
		echo '<tr><td width="50px"><b>'.$hitCount.'</b></td><td';		
		$padding = $parentCount * 20;		
		echo ' style="padding-left:'.$padding.'px" ';		
		echo '>';
		
		if($parentCount>=1)
		{
			echo '- ';
		}
		
		if($childrenCount>=1)
		{
			echo '<span id="toggleDiv_'.$pageID.'" class="toggleButton">';
			
			//echo '<i class="fas fa-chevron-circle-down toggleIcon"></i>';
			//echo '<i class="fas fa-chevron-circle-right toggleIcon hidden"></i>';

		}

		
		echo $pageName;
		
		if($childrenCount>=1)
		{
			echo '</span>';

		}		
		
		
		echo '</td></tr></table>';
		echo '</div>';
		
		echo '<div id="childDiv_'.$pageID.'">';
		drawChildren($pageID);
		echo '</div>';

		
		echo '</div>';


		
	}

}


?>

<script>

jQuery('[id^="toggleDiv_"]').click(function() {
   // do something
   var thisID = jQuery(this).attr('ID');
   var parentID = thisID.split('_')[1];
   var targetClass = '#childDiv_'+parentID; 
   var toggleClass = '#div_'+parentID;
   
   console.log(targetClass);
	
	jQuery(targetClass).slideToggle(); // The main div
	
	// Toggle the icons
	jQuery(toggleClass+' .toggleIcon').toggle();	
	   
});


</script>
