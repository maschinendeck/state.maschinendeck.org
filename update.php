<?php
	require("global.php");
	require("phpMQTT.php");

	define("OPEN_STATUS_PARAMETER", "open");


	/*
		This script modifies the SPACE_STATE_FILE file
			&open=[true|false]
		It is recommended to use HTTPS client certificates or HTTPS + HTTP AUTH as a strategy for authorizing the request.
		-> See .htaccess.sample for an example file
	*/

    /* This method
     	- sets HTTP headers for response
     	- creates the JSON response
		- calls callback if is not null
		- exits
     */
    function respond($status, $errorMsg, $callback=null) {
    	header("Content-Type: application/json", $http_response_code = $status);

    	$response = array("status" => $status);
    	if ($errorMsg !== null) {
    		$response["success"] = false;
    		$response["errorMessage"] = $errorMsg;
    	} else {
    		$response["success"] = true;
    	}
    	echo json_encode($response);
    	if ($callback) {
    		$callback();
    	}

    	exit($status >= 400 ? 1 : 0);
    }

	if($_SERVER['REQUEST_METHOD'] == 'POST') {


	    if (!isset($_POST[OPEN_STATUS_PARAMETER])) {
	    	respond(400, "Could not find the parameter '" . OPEN_STATUS_PARAMETER . "'");
	    }

	    $newOpenStatus = $_POST[OPEN_STATUS_PARAMETER];
	    if ($newOpenStatus != '0' && $newOpenStatus != '1') {
	    	respond(400, "The parameter '" . OPEN_STATUS_PARAMETER . "' has to be either 0(closed) or 1(open)");
	    }

	    if(MQTT_ENABLE) {
		    $mqtt = new phpMQTT(MQTT_SERVER_ADRESS, MQTT_SERVER_PORT, MQTT_CLIENTID);
		    if ($mqtt->connect(true, NULL, MQTT_USERNAME, MQTT_PASSWORD)) {
				$mqtt->publish(MQTT_TOPIC, ($newOpenStatus ? "open" : "closed"), 1, true);
				$mqtt->close();
		    }
		}

   	    //Read file and decode
	    $fileContents = file_get_contents(SPACE_STATE_FILE);
	    if ($fileContents === false) {
	    	if (!is_writable(DATA_DIR)) {
				respond(500, "Cannot write to data dir " . DATA_DIR);
	    	}
	    }

	   	$currentJSON = json_decode($fileContents, $assoc=true);
	    if (json_last_error() != JSON_ERROR_NONE) {
	    	respond(500, "Could not parse json " . SPACE_STATE_FILE);
	    }

	    $currentOpenStatus = $currentJSON["open"];

	    if ($currentOpenStatus != $newOpenStatus) {
	    	
	    	$currentJSON["open"] = (bool) $newOpenStatus;
	    
	    	//Write to disk
	    	 //Open file for writing and get a lock
		    $fileHandle = fopen(SPACE_STATE_FILE, "w");
		   	if ($fileHandle === false) {
		   		respond(500, "Could not fopen " . SPACE_STATE_FILE);
		   	}
		   	$fileHandle_close = function() use($fileHandle) {
		   		fclose($fileHandle);
		   	};

		    //Get a lock on the file
		    if (flock($fileHandle, LOCK_EX) == false) {
	    		respond(500, "Could not get a lock on " . SPACE_STATE_FILE, $fileHandle_close);
	    	}
	    
	    	$encoded = json_encode($currentJSON);
	    	if (json_last_error() != JSON_ERROR_NONE) {
	    		respond(500, "Could not encode updated JSON.", $fileHandle_close);
	    	}
	    		    	
	    	if (fwrite($fileHandle, $encoded) === false) {
	    		respond(500, "Could not write updated status to file.", $fileHandle_close);
	    	} else {
	    		respond(200, null, $fileHandle_close);
	    	}

	    } else {
	    	respond(200, null);
	    }

	}

	respond(400, "Cannot handle this request.");


?>
