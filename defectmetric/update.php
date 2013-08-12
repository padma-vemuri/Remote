<html>
	<head>
		<link rel="stylesheet" href="css/style.css"> <!-- link to style sheet -->
		<script src='js/script.js'></script>  <!-- link to JS functions -->
		<link rel="stylesheet" href="font/stylesheet.css"> <!--  link to font style sheets -->
	</head>
	<body style ="font: 17px/22px 'OpenSansRegular';">
		<h1> Update/Edit Page </h1><br/><br/>

		
		<form action ="changes.php" method="post">
			<label>
			Release <input id ="Email" type="text" name="releasename" value ="<?php error_reporting(0); session_start(); $_SESSION['oldcolumnname'] = $_GET['release']; echo $_GET['release'];?>" required placeholder="Release Name"></label><br/>
			<label>
			Domain<select id ="domain"  name = "domain" required placeholder="DOMAIN NAME">
            		   	<?php
            		   		$conn;
                            global $result;
            		   	    include('functions.php');
            		   	    ListAllDomains($result);
                			while (($row = oci_fetch_assoc($result))){
                				echo "<option>". $row['Domain']. "</option>";
                			}

                		?>
                    </select>
            </label>
        

     	
        <button id="update" name="update" class ="btn" value ="update" > Update </button>&nbsp;&nbsp;

        <button id ="delete" class ="btn" name="update" value ="delete" >Delete</button></label>
		</form>
        <button id ="cancel"  class ="btn" onclick ="location.href='meta.php'"> Cancel </button>&nbsp;&nbsp; 
        </body>
</html>

