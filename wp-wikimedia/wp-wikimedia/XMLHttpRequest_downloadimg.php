<?php

	//ENREGISTREMENT IMAGE
	$si = fopen($_GET['fichier'], "r" );  // open URL  
	$serverImg = stream_get_contents($si);  // read contents  
	fclose($si);  // close file  
	/* open file to save to (w+ creates if file does not exist || b opens binary safe [Win32])
	Seemed to work fine with out the 'b' on Windows NT but just to be safe. */
	$si = fopen('images/' . urldecode(substr($_GET['fichier'], strrpos($_GET['fichier'], "/"), strlen($_GET['fichier']))), "w+b" );
	$erno = fwrite($si, $serverImg);  // write contents to file 
	
	if($erno === false)
		echo '<p> error </p>';
	else
		echo $_GET['path'] . "images" . urldecode(substr($_GET['fichier'], strrpos($_GET['fichier'], "/"), strlen($_GET['fichier'])));

?>