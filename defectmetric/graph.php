<?php
  // This function is used to connect to the database with userid and password are hard coded.
  require_once ('jpgraph-3.5.0b1/src/jpgraph.php');
  require_once ('jpgraph-3.5.0b1/src/jpgraph_bar.php');

  function  parseandexecute($query){
	    global $conn;
	    global $s2;
	    global $result;
	    $conn = oci_pconnect("ats_perf", "ats#performance#", "dbgen-prd1-07.cisco.com:1591/atsprprd");
	    $s2 = oci_parse($conn,"$query");
	    if (!$s2) {
	      $e = oci_error($conn);
	      trigger_error('Could not parse statement: '. $e['message'], E_USER_ERROR);
	    }
	    $r2 = oci_execute($s2);
	     if (!$r2) {
	      $e = oci_error($s2);
	      trigger_error('Could not execute statement: '. $e['message'], E_USER_ERROR);
	     }
	    $result = $s2;
	    return $result ; 
  }



  //This is graph function for that release and creates an image on the server for the release with release name
  function DoubleBarGraph($release,$query){
	    //global $data1y;// 
	    //global $data2y;
	    //global $data3y;
	    
  		global $result;
	    parseandexecute($query);
	    $temp = array();
	    $appCount = array();
	    $perfCount = array();
	    $DateRecorded = array();
	    while (($row = oci_fetch_assoc($result))){
	      $temp[] = $row;
	    }
	    foreach ($temp as $item){
	      $appCount[] = $item['APP#'];
	      $perfCount[] = $item['PERF#'];
	      $DateRecorded[] = $item['TREND_DATE'];
	    }

	    // Create the graph. These two calls are always get_required_files()
	    $graph = new Graph(800,420,'auto');
	    $graph->SetScale('textlin');

	    $theme_class= new UniversalTheme;
	    $graph->SetTheme($theme_class);

	    $graph->SetBox(false);

	    $graph->ygrid->SetFill(false);
	    $graph->xaxis->SetTickLabels($DateRecorded);
	    $graph->xaxis->SetLabelAngle(60);
	    $graph->yaxis->HideLine(false);
	    $graph->yaxis->HideTicks(false,false);
	    // Create the bar plots
	    $b1plot = new BarPlot($appCount);
	    $b2plot = new BarPlot($perfCount);


	    // Create the grouped bar plot
	    $gbplot = new GroupBarPlot(array($b1plot,$b2plot));
	    // ...and add it to the graPH
	    $graph->Add($gbplot);

	    $b1plot->SetColor("white");
	    $b1plot->SetFillColor("#0055FF");
	    $b1plot->value->show();
	    $b1plot->value->SetFormat('%d');
	    $b1plot->SetFillGradient("#0055FF","#0055FF",GRAD_RAISED_PANEL);
	    //$b1plot->value->SetFont(FF_FONT1,FS_NORMAL);
	    $b1plot->SetLegend("App"); ///setting legend  which displays at the bottom of the chart explaining which bar shows the severity number..
	    $b2plot->SetLegend("Perf");
	    $b2plot->SetColor("white");
	    $b2plot->SetFillColor("#FF2828");
	    $b2plot->value->show();
	    $b2plot->value->SetFormat('%d'); 
	    $b2plot->SetFillGradient("#FF2828","#FF2828",GRAD_RAISED_PANEL);
	    $graph->title->Set("Trend of Open TDs Assigned to Application Team and Performance Team ".$release);
	    $graph->title->SetFont(FF_FONT2);
	    //$graph->xaxis->scale->SetDateAlign( DAYADJ_1);
	    //$graph->legend->SetReverse();
	    $graph->legend->SetFrameWeight(9); //setting up the frame of legend
	    $graph->legend->SetColumns(6);  // setting up the columns of legend
	    $graph->legend->SetColor('#4E4E4E','#00A78A'); //setting up the color
	    $graph->legend->SetPos(0.5,0.99,'middle','bottom');
	    //$chartName = $release +".png"; 
	    // Display the graph
	    $graph->Stroke();
  }
  	$release ='Q4FY13';
 	$query ="select  trend_date, app#, perf# from gdcp.cisco_11i_ermo_db_trend where release = '".$release."'  and domain ='ERMORQ' and  ROUND (SYSDATE - TREND_DATE ) < 20 order by trend_date";
  	DoubleBarGraph($release,$query);