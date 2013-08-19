<?php
  // This function is used to connect to the database with userid and password are hard coded.
  require_once ('jpgraph-3.5.0b1/src/jpgraph.php');
  require_once ('jpgraph-3.5.0b1/src/jpgraph_bar.php');

  // This function return $conn which holds the connection.
  
  function connect(){
    global $conn;
    $conn = oci_pconnect("ats_perf", "ats#performance#", "dbgen-prd1-07.cisco.com:1591/atsprprd"); // using OCI Connect which is PHP library
    return $conn;
  } 

  // This is called inside graph() will return the values from the databae to graph ()
  function dailygraph($release, $domain){
    //global $data1y;// 
    //global $data2y;
    //global $data3y;
    global $appCount;
    global $perfCount;
    global $DateRecorded;

    global $conn;
    global $result;
    connect($conn);
    $query ="select  release,domain,trend_date, app#, perf# from gdcp.cisco_11i_ermo_db_trend where release = '".$release."'  and domain ='".$domain."' and  ROUND (SYSDATE - TREND_DATE ) < 20 order by trend_date";
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
    //$data1y=$appCount; //
    //$data2y=$perfCount; //
    //$data3y=$DateRecorded; //
  }
  //This delete the graph image created on the server
  function deleteFile($release){

    $myFile = $release . ".png";  // Dailygraph PNG is created on the server and After sending the maiul we are deleting it.
     while(is_file($myFile) == TRUE){
      chmod($myFile, 0666);  // setting privilleges to write every one.. so.. We can delete the Chart.
      unlink($myFile);
    }
  }
   // This will parse and execute the query and return the result.
  function  parseandexecute($query){
    global $conn;
    global $s2;
    global $result;
    connect($conn);
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
  
  ///This function show the dropdown list on Form.php .. which lists all the distinct releases available.. to pick
  function dropdown($result){
    global $conn;
    global $s2;
    connect($conn);
    $dropdownList = "select distinct release from GDCP.cisco_11i_ermo_db order by release desc";
    parseandexecute($dropdownList);
    //return $result;
  }
  

  //This is graph function for that release and creates an image on the server for the release with release name
  function graph($release,$domain){
    //global $data1y;// 
    //global $data2y;
    //global $data3y;
    global $appCount;
    global $perfCount;
    global $DateRecorded;

    dailygraph($release,$domain);

    // Create the graph. These two calls are always get_required_files()
    $graph = new Graph(800,420,'auto');
    $graph->SetScale('textlin');

    $theme_class=new UniversalTheme;
    $graph->SetTheme($theme_class);

    //$graph->yaxis->SetTickPositions(array(0,30,60,90,120,150,200,250), array(15,45,75,105,135,175,215));
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
    $graph->Stroke($release .".png");
  }
  
  // Refresh  functions this refreshes the meta table and updates with the current data/ used before we send out the mail.
  function refresh($y){
    global $conn;

    //global $s2;
    connect($conn);
    $query_refresh = "BEGIN gdcp.perf_bug_pkg.populate_defect_data('".$y."'); END;";
    $query_trend_refresh ="BEGIN gdcp.perf_bug_pkg.update_trends; END;";
   
    parseandexecute($query_refresh);
    parseandexecute($query_trend_refresh);
   
    if($r5 && $s5  && $r4 && $s4)
      return true;
    else
      return false;
  }

  // parameter passed is domain..or relaease.. and.. oince this is called generates both app and Performance and Application table.. and adds that to body..
  function table($release,$domain){
    global $query;
    global $orderby;
    global $conn;
    $query = "select distinct defect_id as \"Issue ID\", release as \"Release\",gbp as \"Bussiness Flow\",project as \"Project\",severity  as \"Severity\" ,
              status as \"Status\",null as \"PM Priority\",ROUND (SYSDATE - detected_date) as \"Age\",a.assigned_to as \"Assigned To\",logged_by as \"Reported By\",
              detected_date as \"Reported Date\",
              modified_date as \"Modified\", null as\"Application Name\",  environment as \"Environment\",track as \"Track\",
              summary as \"Summary\"  from gdcp.cisco_11i_ermo_db a, gdcp.perf_assignments b
              where  status <> '12 Closed'  and release ='";
    
    //$orderby = " order by project, severity  "; // order  by project and severity;
    $orderby = " order by project, severity  "; // order  by project and severity;
   
  
    
  
    $domainRQ ="  and domain = '".$domain."' ";
  
    $app = "' and  a.assigned_to not in(select distinct c.assigned_to from gdcp.cisco_11i_ermo_db c, gdcp.perf_Assignments p  where c.assigned_to = p.assigned_to)"; // condition for getting all application bugs
  
    $perf = "' and a.assigned_to = b.assigned_to"; // condtion for getting all performance bugs
  
    $ERMOfilteronly =  " and b.assigned_to IN ('bidalal','gmaganti','raykim','sanjpras','sdulla','sukoyyal','vkhemani','kkestur','abhkapoo','rnadupal','lkondu','datow','11iperf-tuning','vinigam','vithirum','maninpan','lpunukol','aguntupa','atipatel','gisachde','vanarya','ragorle','sipentel','rajnarra','mbagayat','mraoputh','sumoolch','rajichan','ramcasti','stippara','vvelchal','ssreepar','srudhara','srguntup','rajalaga','venvemur','vikapodd','ranjaven','dmahto','shivnaya','vsikarwa','dthankha','vakram','sammanav','navchida ','tbhogara','pavsistl','banjanap','steegela','sproddut','asamant','sramared','rashank3')" ;
  
    connect($conn); // calls connnect to get the conneciton.

    if($release == "ERMOPerf"){
      $release = 'ERMO Perf';
    //$query_for_app_bugs = ""; // actuall query
    //$query_for_perf_bugs = $query.$release.$perf.$ERMOfilteronly.$domainQA.$orderby; //actual query for ERMO Only throws in a different filet
    $query_for_app_bugs  = $query.$release.$perf.$ERMOfilteronly.$domainRQ.$orderby;
    }
    elseif ($release == 'FastTrack') {
        $queryForFastrack = "select distinct defect_id as \"Issue ID\", release as \"Release\",gbp as \"Bussiness Flow\",project as \"Project\",severity  as \"Severity\" ,
              status as \"Status\",null as \"PM Priority\",ROUND (SYSDATE - detected_date) as \"Age\",a.assigned_to as \"Assigned To\",logged_by as \"Reported By\",
              detected_date as \"Reported Date\",
              modified_date as \"Modified\", impacted_hours as\"Application Name\",  environment as \"Environment\",track as \"Track\",
              summary as \"Summary\"  from gdcp.cisco_11i_ermo_db a, gdcp.perf_assignments b
              where  status <> '12 Closed' ";
        $perfForFastrack = " and a.assigned_to = b.assigned_to";
        $appForFasttrack = " and  a.assigned_to not in(select distinct c.assigned_to from gdcp.cisco_11i_ermo_db c, gdcp.perf_Assignments p  where c.assigned_to = p.assigned_to) "; // condition for getting all application bugs
        $onlyFasttrack = " and impacted_hours = 'NPA' and release <>'ERMO Perf' ";
       
       $query_for_app_bugs  = $queryForFastrack.$appForFastrack.$onlyFasttrack.$domainRQ.$orderby;
       $query_for_perf_bugs = $queryForFastrack.$perfForFastrack.$onlyFasttrack.$domainRQ.$orderby;
    }
    
    else{
      $query_for_app_bugs  = $query.$release.$app.$domainRQ.$orderby; // actuall query for other releases
      $query_for_perf_bugs = $query.$release.$perf.$domainRQ.$orderby; //actual query
    }
    $s = oci_parse($conn,"$query_for_perf_bugs"); // perf This is first table;
    if (!$s) {
      $e = oci_error($conn);
      deleteFile($release);
      trigger_error('Could not parse statement: '. $e['message'], E_USER_ERROR);
    }
    $r = oci_execute($s);
    if (!$r) {
      $e = oci_error($s);
      deleteFile($release);
      trigger_error('Could not execute statement: '. $e['message'], E_USER_ERROR);
    }

    $ncols = oci_num_fields($s); // gives out number of collumns;
    $body = "";
    if($release == 'ERMO Perf'){
      $body .= "";
    }
    else{

      $body .= "<b  style = \"font-family:Calibri;font-size:16px;\">Assigned to Performance  Team  &nbsp;".$release." : </b>  <br/><br/>
                <table border = '1'  style = \"border-collapse:collapse;font-family:Calibri;width:100%;padding-left:6px; font-size:12px;\">"; //Table for Application.
      $body .= "<tr>";
      for ($i = 1; $i <= $ncols; ++$i) {
        $colname = oci_field_name($s, $i);
        $body .= " <th style=\"background-color:lightblue;font-family:Calibri;font-size:12px;\">".htmlentities($colname, ENT_QUOTES)."</b></th>\n";
      }
      $body .= "</tr>\n";
      $count = 0;
      while (($row = oci_fetch_array($s, OCI_ASSOC+OCI_RETURN_NULLS)) != false) {  
        foreach ($row as $item) {
          $item = str_replace('?', '-', $item);
          $body .= " <td>".($item!==null?htmlentities($item,ENT_QUOTES):"&nbsp;")."</td>\n";
        }
        $body .= "</tr>\n"; $count++;
      }

      $body .= "</table></div>\n";
      
      $body .= "Total Count:   ".$count;
      $body .= "<br/><br/><br/><br/>";
    }




    /// This is second Table -------------------------------
    $s1 = oci_parse($conn,"$query_for_app_bugs"); //app
    if (!$s1) {
      $e = oci_error($conn);
      deleteFile($release);
      trigger_error('Could not parse statement: '. $e['message'], E_USER_ERROR);
    }
    $r1 = oci_execute($s1);
    if (!$r1) {
      $e = oci_error($s1);
      deleteFile($release);
      trigger_error('Could not execute statement: '. $e['message'], E_USER_ERROR);
    }
    $ncols1 = oci_num_fields($s1); // number of collums

    if($release == 'ERMO Perf'){
      $body .= "<b style = \"font-family:Calibri;font-size:16px;\"> Assigned to  Performance Team &nbsp;: ".$release." - Domain ERMO RQ </b>  <br/><br/>";
    }

    elseif($release == 'Q1FY14'){
      $body .= "<b style = \"font-family:Calibri;font-size:16px;\"> Assigned to  Application Team &nbsp; ".$release." : </b>  <br/><br/>";
      $body .= "<u style =\"font-family:Calibri;font-size:14px;color:red\">Note : The status needs to be changed for the cases highlighted in yellow</u><br/><br/>";
    }
    else{
      $body .= "<b style = \"font-family:Calibri;font-size:16px;\"> Assigned to  Application Team &nbsp; ".$release." : </b>  <br/><br/>";
    }
    $body .=  "<table border = '1'  style = \"border-collapse:collapse;font-family:Calibri;padding-left: 6px; font-size:12px;\">"; // Table for Performance
    $body .= "<tr>";
    for ($i = 1; $i <= $ncols1; ++$i) {
      $colname = oci_field_name($s1, $i);
      $body .= " <th style=\"background-color:lightblue;font-family:Calibri;font-size:12px;\">".htmlentities($colname, ENT_QUOTES)."</b></th>\n";
    }
    $body .= "</tr>\n";
    $count = 0;
    while (($row = oci_fetch_array($s1, OCI_ASSOC+OCI_RETURN_NULLS)) != false) {
      $body .= "<tr>\n";
      foreach ($row as $item) {
        $item = str_replace('?', '-', $item);
        $body .= " <td>".($item!==null?htmlentities($item,ENT_QUOTES):"&nbsp;")."</td>\n";
      }
      $body .= "</tr>\n";$count ++;
    }
    if ($count == 0){
      $body .=  "No Data";
    }
    $body .= "</table></div>\n";
    $body .= "Total Count: ".$count;
    $body .= "<br/><br/><br/><br/>";
    return $body;
  }

  //list all the releases from the meta table
  function ListAll($result){
    global $conn;
    global $result;
    connect($conn);
    $Listall ="select  NULL as \"Edit\", release as \"Release\", domain as \"Domain\" from gdcp.cisco_11i_ermo_db_meta order by decode(release,'Q4FY13',1,'Q1FY14',2,'FY13-Q3',3,'May-13-Rel',4,5)";
    parseandexecute($Listall);
  }
  function ListAllDomains($result){
    global $conn;
    global $result;
    connect($conn);
    $ListAllDomains ="select  distinct domain as \"Domain\" from gdcp.cisco_11i_ermo_db";
    parseandexecute($ListAllDomains);
  }
  function insert($releasename,$domain){
    global $conn;
    global $releasename;
    global $domain;
    connect($conn);
    $insert = "insert into  gdcp.cisco_11i_ermo_db_meta values('".$releasename."','".$domain."')";
    if(parseandexecute($insert))
      return true;
  }

  //function to update
  function update($oldcollumnname,$releasename,$domain){
    global $conn;
    connect($conn);
    $update ="update gdcp.cisco_11i_ermo_db_meta set release ='".$releasename."', domain = '".$domain."' where release = '".$oldcollumnname."'";
    if(parseandexecute($update))
      return true;
  }

  //function to delete
  function delete($releasename,$domain){
    global $conn;
    connect($conn);
    $query = "alter session set current_schema = gdcp";
    parseandexecute($query);
   // $releasename = 'Q4FY13';
    //$domain ='ERMORQ';

    //$delete ="delete from gdcp.cisco_11i_ermo_db_meta where release ='".$releasename."'"; //and domain ='".$domain."'";
    $delete ="delete from gdcp.cisco_11i_ermo_db_meta  where release = '".$releasename."' and domain = '".$domain."'";
    if(parseandexecute($delete))
      return true;
  }

  function sendemail($ebody){
   // global $conn;
   // connect($conn);

    $send = "BEGIN gdcp.send_email('".$ebody."','Trying to Send using db','venvemur@cisco.com','padma.vemuri@gmail.com'); END;";
    if(parseandexecute($send))
      return true;
    else 
      return false;

  }
?>
