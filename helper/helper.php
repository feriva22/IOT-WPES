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

?>
