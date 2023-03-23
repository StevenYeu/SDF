<?php

class APIReturnData {

    public $data;
    public $success;
    public $status_code;
    public $status_msg;

    private function __construct($data, $success, $status_code, $status_msg){
        $this->data = $data;
        $this->success = $success;
        $this->status_msg = $status_msg;

        if(is_null($status_code)){
            if($success) $this->status_code = 200;  // default to 200 or 400
            else $this->status_code = 400;
        }else{
            $this->status_code = $status_code;
        }
    }

    static public function quick403(){
        return self::build(NULL, false, 403, "action is not allowed");
    }

    static public function quick404(){
        return self::build(NULL, false, 404, "not found");
    }

    static public function quick400($message){
        return self::build(NULL, false, 400, $message);
    }

    static public function quick500($message){
        return self::build(NULL, false, 500, $message);
    }

    static public function build($data, $success, $status_code=NULL, $status_msg=NULL){
        return new APIReturnData($data, $success, $status_code, $status_msg);
    }

}

?>
