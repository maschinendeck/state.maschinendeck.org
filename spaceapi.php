<?php

	require("global.php");

	$template = json_decode(file_get_contents(SPACEAPI_TEMPLATE_FILE_PATH), $assoc=true);

	$statePart = json_decode(@file_get_contents(SPACE_STATE_FILE), $assoc=true);

	$template["state"] = $statePart;

	header("Cache-Control: no-cache", true);
	header("Access-Control-Allow-Origin: *", true);
	header("Content-Type: application/json");
	
	echo json_encode($template);

?>