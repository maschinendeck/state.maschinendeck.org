<?php

	require("global.php");

	$template = json_decode(file_get_contents(SPACEAPI_TEMPLATE_FILE_PATH), $assoc=true);

	$statePart = json_decode(@file_get_contents(SPACE_STATE_FILE), $assoc=true);

	$template["state"] = $statePart;

	if (isset($_GET["callback"])) {
		//JSONP
		header("Content-Type: text/javascript");
		echo $_GET["callback"] . "(" . json_encode($template) . ");";
	} else {
		//JSON
		header("Content-Type: application/json");
		echo json_encode($template);
	}

?>