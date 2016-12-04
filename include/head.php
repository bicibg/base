<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Loacal</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    
    
    <!-- Linking BX Slider -->
    <!-- bxSlider Javascript file -->
    <script src="../dist/bxslider/jquery.bxslider.min.js"></script>
    <!-- bxSlider CSS file -->
    <link href="../dist/bxslider/jquery.bxslider.css" rel="stylesheet" />
    
    
    <!-- Linking hoverOverInfo -->
    <link href="../dist/hoverOverInfo/hoverOverInfo.css" rel="stylesheet" />
    <link href="../dist/hoverOverInfo/normalize.css" rel="stylesheet" />
    <!-- Linking Font Awesome -->
    <link href="../dist/font-awesome/css/font-awesome.css" rel="stylesheet" />
    
		<?php
            $root = $_SERVER["DOCUMENT_ROOT"];

			require_once("Configuration/config.php");
			foreach (glob("css/*.css") as $css) {
				echo "<link type='text/css' rel='stylesheet' href='$css'>\n";
			}
			foreach (glob("js/*.js") as $js) {
				print("<script type='text/javascript' src='$js'></script>");
			}

            require_once("include/classes.php");
            require_once("include/functions.php");
            require_once("include/translator.php");


        ?>
	</head>
	<body>