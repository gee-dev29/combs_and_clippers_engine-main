<?php

namespace App\Exceptions;

use Exception;

class ApiResponseException extends Exception
{
    //
    Public $message;
    //
    public function __construct($message){
        
        $this->message = $message;
    }

    public function getErrorMessage(){
        return $this->message;
    }

    public function render(){
        return response()->json([
        "ResponseCode" => 400, 
        "ResponseStatus" => "Unsuccessful", 
        "ResponseMessage" => $this->message, 
        'Detail' => $this->message], 400);
    }
}
