<?php
class Voat {
    private $apikey;
    var $error;
    var $baseurl = "https://fakevout.azurewebsites.net/api/";
    var $version = "v1";
    var $token;
    var $loggedin = false;
    
    function __construct($apikey) {
        $this->apikey = $apikey;
    }
    function endpoint($endpoint, $type="POST", $data="") { //Access an endpoint
            $ch = curl_init($this->baseurl . $this->version . "/" . $endpoint);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Voat-ApiKey: ' . $this->apikey
            ));
    return json_decode(curl_exec($ch), 1);
    }
    function user($username, $password) { //Authenitcate user
        $data = http_build_query(
            array(
                grant_type => "password",
                username => $username,
                password => $password
                )
        );
            $ch = curl_init($this->baseurl . "/token");
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Voat-ApiKey: ' . $this->apikey
            ));
        $output =  json_decode(curl_exec($ch), 1);
        $this->token = $output['access_token'];
        $this->loggedin = true;
        return $output;
    }
    function logout() {
        $this->token = null;
        $this->loggedin = false;
    }
    function get_subverse($subverse) {
        $output = $this->endpoint("/v/" . $subverse, "GET");
        return $output;
    }
}