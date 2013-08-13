<?php
  $conn;
  include('class.phpmailer.php');  // Library for PHPMailer 
  include('functions.php');  //Function.php has all the functions this file is calling.


  session_start(); // Starts a Session

  $pst = date_default_timezone_set('America/Los_Angeles');
  $username = $_POST['email']; // Getting From Email Id.
  $to = $_POST['to']; // Getting / Addding  'TO' part

  if(isset($_POST['ErmoPerf']) && $_POST['ErmoPerf'] == 'Yes') {
    $y = 'Y';
    array_push($_POST['release'], 'ERMO Perf');
    array_push($_POST['release'], 'FastTrack');
  }else
    $y ='N';
  //  
  
  refresh($y);  // Refreshes the meta table and updates with the current date(Defects)// deletes meta data and updates wiht current data from QC


  
foreach ($_POST['graph'] as $row) {
   $Release = $row;
   graph($Release);
        # code...
  }
  //$Release ="Q4FY13"; // Hard coding the release Since only this graph is needed.
  //graph($Release); // Generates the chart with the count of APP and PERF for this release
  $ebody = "<html>";// Email Body Starts here.
  $ebody .= "<body>";
  
  $ebody .= " <p  style =\"font-family:Calibri;font-size:13px;\">Hi All,<br/><br/> Please  check the list  of TD's  
                assigned to Performance  and Application Team @ <b>".date('g:i A') ."</b> PST hours<br/><br/></p>"; 
 
   foreach($_POST['graph'] as $v){
      $ebody .= "<br/><img src = \"".$v.".png\"/><br/>";
    } 

  
   
  foreach($_POST['release'] as $v){ // loop to create all the tables for the selected releases.
    $ebody .= table($v);
  } 
  //ebody .=table('Q4FY13');
  //$ebody .=table('FY13-Q3');
  //$ebody .=table('May-13-Rel');
  //$ebody .=table('June-13-Rel');
  //$ebody .=table('ERMO Perf');
  


  $ebody .= "<img src = \"ciscologo.png\"><br/>";
  $ebody .= "Thanks and Regards, <br/>";
  $ebody .= "ATS Performance Management";
  

  // MAil Fucntion Starts here-- We use PHPMailer to send mail out.
  $mail = new PHPMailer();

  $mail->IsSMTP(); //Checks for SMTP
  $mail->Port = 25; //Sets The Port
  $mail->Host = 'xchcasha.cisco.com'; // host address for outbound cisco mail .. ONLY
  $mail->IsHTML(true); // if you are going to send HTML formatted emails
  $mail->Mailer = 'smtp';
  $mail->SMTPSecure = 'ssh';
  $mail->SMTPAuth = true;
  $mail->SingleTo = false; // if you want to send mail to the users individually so that no recipients can see that who has got the same email.
  $mail->From = $username;
  $mail->FromName = "ATS Performance Management";
  $mail->addAddress($username);
 
  //$mail->addCC("kikulkar@cisco.com","kiran");
  //$mail->addCC();
  //$mail->addCC("smantral@cisco.com","Suresh");
  //$mail->addCC("supadman@cisco.com","Subba");
  //$mail->addCC("brapearc@cisco.com","Brandon");
  $mail->Subject = "Daily Notification Mail ";
  $mail->MsgHTML($ebody);

  foreach($_POST['graph'] as $v){
      $mail->AddAttachment("".$v.".png");
    } 
    // Attchaments both the chart/Graph and cisco logo
 // $mail->AddAttachment("Q1FY14.png");
  $mail->AddAttachment('ciscologo.png');

 /* if(sendemail($ebody)){
    echo "send";
  }
  else
  echo "no"; 
*/
  
  if(!$mail->Send()){
    echo "Message was not sent <br />PHP Mailer Error: " . $mail->ErrorInfo;
    foreach($_POST['release'] as $v){
      deleteFile($v);
    } 
  }
  else{
    foreach($_POST['graph'] as $graph){
      deleteFile($graph);
    } 
    session_destroy();
    header("Location:Result.php");
  }
?>