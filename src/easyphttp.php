<?php
namespace F57;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client as GuzzleClient;

class EasyPHTTP {

    
    /*
    |--------------------------------------------------------------------------
    | Declare all parameters of a Request API
    |--------------------------------------------------------------------------
    */

    protected $url;
    protected $header;
    protected $body;
    protected $method;
    protected $typeRequest;

    function __construct() { 
        $this->url = null;
        $this->body = [];

        //Default Method
        $this->method = 'GET';

        //Default Headers
        $this->header = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ];

        //Default Type of request
        $this->typeRequest = 'SYNC';
    }

    // All Methods that exist actually
    const EXISTING_METHODS = [
        'POST',
        'GET',
        'OPTION',
        'PUT',
        'DELETE',
        'PATCH'
    ];

    const EXISTING_TYPEOF_REQUEST = [
        'SYNC',
        'ASYNC'
    ];

    const API_ERROR_PREFIX = "ApiRequest Error : ";

    //Getting URL of the client
    public static function url($url) {
        $class = new ApiRequest;

        $class->url = $url;
        return $class;
    }

    public function type($type) {

        //Error 
        $errorTxt = 'Wrong type of request, allowed : ';
        foreach(self::EXISTING_TYPEOF_REQUEST as $allowed) {
            $errorTxt .= $allowed . ' ';
        }

        $type = strtoupper($type);

        if(in_array($type, self::EXISTING_TYPEOF_REQUEST)){

            $this->typeRequest = $type;
            return $this;

        }

        throw new \Exception(self::API_ERROR_PREFIX . $errorTxt);

    }

    //Setting More headers if necessary
    public function addHeader($arrayHeaders) {

        foreach($arrayHeaders as $key => $value){
            $this->header[$key] = $value;
        }
        return $this;
 
    }

    //Setting the method, not usefull if this is a get
    public function method($choosen_method) {

        //Error 
        $errorTxt = 'Method not allowed, allowed : ';
        foreach(self::EXISTING_METHODS as $allowed) {
            $errorTxt .= $allowed . ' ';
        }

        $choosen_method = strtoupper($choosen_method);

        if(in_array($choosen_method, self::EXISTING_METHODS)) {

            $this->method = $choosen_method;
            return $this;

        }

        throw new \Exception(self::API_ERROR_PREFIX . $errorTxt);
    }

    //Only for POST : Setting the body
    public function body($arrayBody) {

        if($this->method === 'POST') {

            foreach($arrayBody as $key => $value) {
                $this->body[$key] = $value;
            }

            return $this;

        }else{

            throw new \Exception(self::API_ERROR_PREFIX . 'Only the POST method can have BODY');

        }
        
    }

    private function SYNC() {

        $requestContent = [
            'headers' => $this->header,
        ];

        //If this is a POST we need to verify if the body is not null 
        if($this->method === 'POST'){

            if(!empty($this->body)) {

                $requestContent = [
                    'headers' => $this->header,
                    'json' => $this->body
                ];

            } else {

                throw new \Exception(self::API_ERROR_PREFIX . 'Missing BODY for POST method');

            }
            
        }

        try{

            $client = new GuzzleClient();

            $apiRequest = $client->request($this->method, $this->url, $requestContent);
    
            $response = json_decode($apiRequest->getBody());
    
            return $response;
        
        } catch (\Exception $e) {

            throw new \Exception($e->getMessage());
        }

    }

    private function ASYNC() {

        $client = new GuzzleClient();
        $body;
        $decode;
        $url = $this->url;

        $request = new \GuzzleHttp\Psr7\Request(
            $this->method, 
            $this->url,
            $this->header,
            json_encode($this->body)
        );

        try {
                $promise = $client
                            ->sendAsync($request)
                            ->then(function($response)  {

                                    $statusCode = $response->getStatusCode();
                                    $header = $response->getHeader('content-type')[0];
                                    $body = $response->getBody();
                                    $decode = json_decode($body);
                                    return $decode;
                                    
                            });

            return $promise->wait();

        }catch(\Exception $e){

            return $e->getMessage();

        }
    }

    public function getResponse() {

        if($this->url != null && $this->typeRequest != null) {

            return $this->{$this->typeRequest}();

        }

        throw new \Exception(self::API_ERROR_PREFIX . 'Fatal : Url Missing or Type of request not specified');
    }

    /*
    |--------------------------------------------------------------------------
    | Usefull methods
    |--------------------------------------------------------------------------
    */

    public function getUrl() {
        return $this->url;
    }

    public function auth($value) {

        if(!array_key_exists("Authorization",$this->header)) {

            $this->header['Authorization'] = $value;
            return $this;

        }

        throw new \Exception(self::API_ERROR_PREFIX . 'Authorization Header already exist');
        
    }

}