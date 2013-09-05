
<html>
    <head>
        <link rel="stylesheet" href="css/style.css"> <!-- link to style sheet -->
        <script src='js/script.js'></script>  <!-- link to JS functions -->
        <link rel="stylesheet" href="font/stylesheet.css"> <!--  link to font style sheets -->
    </head>
    <body style ="font: 17px/22px 'OpenSansRegular';">
        <br/><br/>
        <h1> Add  a New Release</h1><br/><br/>
        <input type="button" class="btn" onclick="javascript:location.href = '/defectmetric/meta.php'" value ="Back"/>
        <form action ="changes.php" method="post">
			<label>
			Release <input id ="releasename" type="text" name="releasename"  required placeholder="RELEASE NAME"></label><br/>
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
            </label><br/><br/>
            <label><button id ="Add" name ='add' class ="btn"> ADD </button></label>
        </form>
        

    </body>
</html>
