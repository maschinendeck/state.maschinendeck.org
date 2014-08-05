<?php

/*
	This script modifies the spaceapi.json file's state.open path based on POST request
		&open=[true|false]
	It is recommended to use HTTPS client certificates or HTTPS + HTTP AUTH as a strategy for authorizing the request.
	-> See .htaccess.sample for an example file
*/

	define("OPEN_STATUS_PARAMETER", "open");
	define("SPACEAPI_FILE_PATH", "./spaceapi.json");

    /* This method
     	- sets HTTP headers for response
     	- creates the JSON response
		- calls callback if is not null
		- exits
     */
    function respond($status, $errorMsg, $callback=null) {
    	http_response_code($status);
    	header("Content-Type: application/json");

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
	    if ($newOpenStatus != false && $newOpenStatus != true) {
	    	respond(400, "The parameter '" . OPEN_STATUS_PARAMETER . "' has to be either 0(closed) or 1(open)");
	    }

   	    //Read file and decode
	    $fileContents = file_get_contents(SPACEAPI_FILE_PATH);
	    if ($fileContents === false) {
	    	respond(500, "Could not get contents of file " . SPACEAPI_FILE_PATH);
	    }

	   	$currentJSON = json_decode($fileContents, $assoc=true);
	    if (json_last_error() != JSON_ERROR_NONE) {
	    	respond(500, "Could not find " . SPACEAPI_FILE_PATH);
	    }

	    $currentOpenStatus = $currentJSON["state"]["open"];

	    if ($currentOpenStatus != $newOpenStatus) {
	    	
	    	$currentJSON["state"]["open"] = (bool) $newOpenStatus;
	    
	    	//Write to disk
	    	 //Open file for writing and get a lock
		    $fileHandle = fopen(SPACEAPI_FILE_PATH, "w");
		   	if ($fileHandle === false) {
		   		respond(500, "Could not fopen " . SPACEAPI_FILE_PATH);
		   	}
		   	$fileHandle_close = function() use($fileHandle) {
		   		fclose($fileHandle);
		   	};

		    //Get a lock on the file
		    if (flock($fileHandle, LOCK_EX) == false) {
	    		respond(500, "Could not get a lock on " . SPACEAPI_FILE_PATH, $fileHandle_close);
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