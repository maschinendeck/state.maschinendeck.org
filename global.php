<?php 

	define("DATA_DIR", "./data");
	define("SPACEAPI_TEMPLATE_FILE_PATH", DATA_DIR . "/spaceapi_template.json");
	define("SPACE_STATE_FILE", DATA_DIR . "/space_state.json");

	function defaultSpaceState() {
    	return array("open"=> false);
    }

    //Check for assumptions

    $shouldDie = false;

    if (!is_writable(DATA_DIR)) {
		echo DATA_DIR . " has to be writable to the process running thsi script.";
		$shouldDie |= true;
	}

	if (!file_exists(SPACEAPI_TEMPLATE_FILE_PATH)) {
		echo SPACEAPI_TEMPLATE_FILE_PATH . " has to be setup. Copy from the sample file.";
		$shouldDie |= true;
	}

	if ($shouldDie) {
		die();
	}

	//Create default state

	if (!file_exists(SPACE_STATE_FILE)) {
		file_put_contents(SPACE_STATE_FILE, json_encode(defaultSpaceState()));
	}

?>