<?php

	require("config.php");

	if (isset($_GET["callback"])) {
		echo $_GET["callback"] . "(";
		readfile(SPACEAPI_FILE_PATH);
		echo ");";
	} else {
		readfile(SPACEAPI_FILE_PATH);
	}

?>