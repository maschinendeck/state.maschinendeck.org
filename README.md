#Maschinendeck State API

Get the state of the hackerspace.

##Endpoints

###spaceapi.php

Returns JSON complying to the [SpaceAPI v13](http://spaceapi.net/documentation).

If you need a *jsonp* result, specify the `callback` parameter
	
	http://state.yourspace.org/spaceapi.php?callback=jsonPCallback

###update.php

`POST` to update the state of the hackerspace. You can only update one property per request.

* `property=[values]` What does this property do?
* `open=[0,1]` Is the hackerspace open (`1`) or closed (`0`)


####Example

Request

	curl https://state.fancyspace.org/update.php -u username:password -d open=0
	
Response
	
	{
		"status":200, 	//HTTP status code
		"success":true 	//Indicator for success of request
	}
	
Error

	{
		"status":400,	//HTTP status code
		"success":false	//Indicator for success of request
		"errorMessage":"Could not find the parameter 'open'"	//An error message in English that can be displayed to the user
	}
	
	
#Setup

* Copy `spaceapi.json.sample` to `spaceapi.json` and adjust the values for your hackerspace.
* Ensure that the process that'll run `update.php` has read-write-permissions to `spaceapi.json`
* Copy `.htaccess.sample` to `.htaccess`
* Run `htpasswd -c .htpasswd apiuser`. Enter the password for `apiuser` on prompt. This creates the `.htpasswd` file in your current directory.
* Adjust the path to `.htpasswd` in your `.htaccess` in line `AuthUserFile ...`

A bit clunky but this way you don't need a database, etc.

##Implementation

###update.php

* Check for correct request parameters 
* Read `spaceapi.json` and decode it
* If `newValue != currentValue`
	* Get a `write` file handle
	* Get an exclusive lock on that file handle
	* Change value in JSON
	* Encode JSON and write to disk
	
####Responses

Responses are handled by `respond($status, $errorMsg, $callback=null)`.

It sets HTTP headers, creates a JSON response and optionally calls a callback before it calls `exit()`.

**→ Important:** Code after a call to `respond(...)` will not be run.

###Security & Authentication

**Important:** `update.php` does not implement its own authentication.

It is recommended to use **HTTPS Client authentication** or **HTTPS + HTTP Authentication**. Whilst the former is more elegant, the latter can be configured using `.htaccess` files.

You can find a sample `.htaccess` file in the project directory.