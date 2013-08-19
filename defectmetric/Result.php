<html>
	<head>
		<!----  JS/ CSS all scripts go here.. -->		
        <link rel="stylesheet" href="css/style.css"> <!-- link to style sheet -->
		<script src='js/script.js'></script>  <!-- link to JS functions -->
		<link rel="stylesheet" href="font/stylesheet.css"> <!--  link to font style sheets -->
	</head>
	<body>
		<div id ="contents">
			<div id="wrapper">
				<form id ="form" action ="index.php"> 

					<h1> Your Mail has been Sent. </h1><br/><br/>
					<button id="send"> Send Again </button>
					<?php echo"<button id=\"send\" name ='home' 
								onclick= 'window.history.go(-1)';>Home</button> "; ?>
				</form>

			</div>
		</div>
	</body>
</html>

