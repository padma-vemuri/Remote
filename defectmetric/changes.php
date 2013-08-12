<?php
include('functions.php');
session_start();

//error_reporting(0);

global $domain;
global $releasename;
global $oldcolumnname;
 
$oldcolumnname = $_SESSION['oldcolumnname'];
$releasename = $_POST['releasename'];
$domain = $_POST['domain'];
if(isset($_POST['update']) && $_POST['update'] == 'update') {
//echo $releasename;   
//echo $domain;
//echo $oldcolumnname;
if(update($oldcolumnname,$releasename,$domain))
	header("Location:meta.php");
}
elseif(isset($_POST['update']) && $_POST['update'] == 'delete') 
  {
  	if(delete($releasename,$domain))
  			header("Location:meta.php");
  	else
  		header("Location:update.php");
}

else{
	if(insert($releasename,$domain))
			header("Location:meta.php");	
}
?>


