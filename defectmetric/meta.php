<html>
	<head>
		<link rel="stylesheet" href="css/style.css"> <!-- link to style sheet -->
		<script src='js/script.js'></script>  <!-- link to JS functions -->
		<link rel="stylesheet" href="font/stylesheet.css"> <!--  link to font style sheets -->
	</head>
	<body>
		<h1> List of all Releases </h1><br/><br/>
		 &nbsp;<button type="button" class="btn" onclick ="javascript:location.href = '/dev/email'" value="Home"> Home</button><button id ="add"  class ="btn" onclick ="location.href='add.php'"> Add </button>
		<?php
			$conn;
			global $result;
			include('functions.php');
			listall($result);
			$ncols = oci_num_fields($result);
			echo "<br/><table border = '1'  style = \"border-collapse:collapse;padding-left:16px;\"<tr>";
			for ($i = 1; $i <= $ncols; ++$i) {
				$colname = oci_field_name($result, $i);
				echo "<th style=\"background-color:lightblue;font: 17px/22px 'OpenSansRegular';\">".htmlentities($colname, ENT_QUOTES)."</b></th>\n";
			}
			echo "</tr>";
			while (($row = oci_fetch_assoc($result))){
				echo "<tr style =\"padding-left:16px; padding-right:16px;font: 17px/17px 'OpenSansLight';\">";
		    	echo "<td>"."<a href =\"update.php?domain=".$row['Domain']."&release=".$row['Release']."\" style =\"text-decoration:none;\">Update </a>"."</td>";
				
				echo "<td>".$row['Release']. "</td>"; 
				echo "<td>".$row['Domain']."</td>";
				echo "</tr>";
			}
			echo "</table>"
		?>

</body>
</html>

