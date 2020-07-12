<?php
    function data_get($param){
        return $_GET[$param] ?? NULL;
    }

    function data_post($param){
        return $_POST[$param] ?? NULL;
    }


    function json_response($status, $message = null, $data = null, $expand_value = null, $resp_code = 200)
    {
        header('Content-Type: application/json');
		http_response_code($resp_code);
		
        if($status === 'error'){
            if(empty($message) || $message == "")
                $message = 'Invalid input';
        }
        $response = array('status' => $status, 
                            'message' => $message,
                            'data' => $data);

        if($expand_value !== NULL && is_array($expand_value)) {
            $response = array_merge($response,$expand_value);
        }

        echo json_encode($response);
        exit();
        
	}
	
	function cors() {

		// Allow from any origin
		if (isset($_SERVER['HTTP_ORIGIN'])) {
			// Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
			// you want to allow, and if so:
			header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
			header('Access-Control-Allow-Credentials: true');
			header('Access-Control-Max-Age: 86400');    // cache for 1 day
		}
	
		// Access-Control headers are received during OPTIONS requests
		if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
	
			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
				// may also be using PUT, PATCH, HEAD etc
				header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         
	
			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
				header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
	
			exit(0);
		}
	
		//echo "You have CORS!";
		header("Content-Type: application/json; charset=UTF-8");
	}

?>
