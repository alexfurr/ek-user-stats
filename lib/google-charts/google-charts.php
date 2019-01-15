<?php

class ek_gCHARTS {


	function __construct ()
	{
		$this->enqueueScripts();
	}

	
	function enqueueScripts ()
	{
		$pluginFolder = plugins_url( '/google-charts/', dirname(__FILE__) );

		wp_enqueue_script( 'ek-google-charts', 'https://www.google.com/jsapi' );
		wp_enqueue_script( 'ek-gcharts-custom-js', $pluginFolder . 'googlecharts.js', array( 'jquery' ) );
	}
	
	
	function draw ( $chartType, $data, $elementID, $keyName = 'Keys', $valName = 'Values', $title = 'Chart:', $chartWidth="600px", $chartHeight="300px" )
	{
		
		if ( ! is_array( $data ) ) {
			return;
		}
		
		$c = 1;
		$dataCount = count( $data );
		
		$jsArray = "[ '" .$keyName. "', '" .$valName. "' ],";
		

		foreach ( $data as $i => $values ) 
		{
			
			
			
			
			$jsArray .= "[ '" .$values[0]. "', " .$values[1]. " ]" . ( $c < $dataCount ? ", " : "" );
			$c++;
		}
		?>
		
		<script>		
		jQuery( document ).ready( function () {
			ek_G_CHARTS.charts.push({
				type:		'<?php echo $chartType; ?>',
				data: 		[ <?php echo $jsArray; ?> ],
				elementID:	'<?php echo $elementID; ?>',
				title:		'<?php echo $title; ?>'
				

			});
		});
		</script>		
		
		<?php
		
		// Draw the element
		$myStyle="";
		if($chartType=="pie")
		{
			$myStyle = 'width: '.$chartWidth.'; height: '.$chartHeight.';';
		}
		if($chartType=="bar")
		{
			$myStyle = 'width: 95%;';
		}		
		echo '<div style="'.$myStyle.'" id="'.$elementID.'"></div>';

		
		
	}
	
	function drawCombo ( $args )
	{
		$dataSource = $args['data'];		
		$elementID = $args['elementID'];
		$chartTitle = $args['chartTitle'];
		
		// Get the first Row of data and also create the TYPE of chart col
		$dataCols = $dataSource['dataCols'];
		
		$seriesInfo = '';
		$dataTypeNames = array();
		$dataArrayStr= '[';
		
		$currentTypeNumber=0;
		foreach ($dataCols as $KEY => $dataArray)
		{
			if(!is_array($dataArray) )
			{
				$tempData = $dataArray;
				$dataArrayStr.= "'".$tempData."',";						
			}
			else
			{
				$thisDataName = $dataArray['name'];
				$thisDataType = $dataArray['type'];
				$dataArrayStr.= "'".$thisDataName."',";
				
				$dataTypeNames[] = $thisDataName;
				
				$seriesInfo.=$currentTypeNumber.":{
					type:'".$thisDataType."',
					targetAxisIndex:".$currentTypeNumber.",
				},";
				$currentTypeNumber++;						
				
			}					
		}		
		$dataArrayStr.= '],';
		
		// Now go thorugh the rest of the data
		$myData = $dataSource['data'];
		
		foreach ($myData as $KEY => $dataArray)
		{
			
			$dataArrayStr.= '[';
			
			$dataArrayStr.= "'".$KEY."',";
			
			foreach ($dataArray as $thisData)
			{
				$dataArrayStr.= $thisData.',';
			}	

			$dataArrayStr.= '],';
			
		}			
		
		
		$dataTypeNamesStr='';
		foreach($dataTypeNames as $name)
		{
			
			
			$dataTypeNamesStr.=  '{
					title: "'.$name.'",
					minValue: 0,

				  },';
		}
		

		
		?>
		
		
		
		<script>


	  
		jQuery( document ).ready( function () {

		
			// Start of chart data
			 var data = google.visualization.arrayToDataTable([
				
				<?php
				
				
				echo $dataArrayStr;
				
				?>

			  ]);

			  // Start of chart options
			  var options = {
                height:500,
				//title : '<?php echo $chartTitle;?>',
				title : "",
				titleTextStyle: {
					color: "#336699",
					fontSize: 28, 
					bold: true, 
					italic: false,
				},
				chartArea: {
					'width': '80%', 'height': '70%'
				},
				pointSize: 5,
			
				
				
				
				
				vAxes: [
					<?php		
					echo $dataTypeNamesStr;		
					?>	
				],	
				
				
				// Series information
				hAxis: {
					title: "",
					slantedText:true,
					slantedTextAngle:270,	
					textStyle : {
						fontSize: 10,
						color: "#565656",
					},					
				},							  
				seriesType: "bars",				  
				series:
                    {
						
					<?php
					echo $seriesInfo;
					?>
                    }
			 
			  };

			  var chart = new google.visualization.ComboChart(document.getElementById('chart_div'));
			  chart.draw(data, options);		
		});


		</script>		
		
		<?php
		
		// Draw the element
	
		echo '<div id="chart_div"></div>';

		
		
	}	
	

}
?>