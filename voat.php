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
    function endpoint($endpoint, $type="POST", $data="", $json=false) { //Access an endpoint
            $ch = curl_init($this->baseurl . $this->version . "/" . $endpoint);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            $headers = array(
            'Voat-ApiKey: ' . $this->apikey,
            'Authorization: Bearer ' . $this->token
            );
            if ($json) {
                array_push($headers, "Content-type: application/json");
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
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
    function get_subverse_info($subverse) {
        $output = $this->endpoint("/v/" . $subverse . "/info", "GET");
        return $output;
    }
    function block_subverse($subverse) {
        if (!$this->loggedin) {
            $this->error = "Not logged in";
            return 0;
        }
        $output = $this->endpoint("/v/" . $subverse . "/block", "POST");
        return $output;
    }
    function unblock_subverse($subverse) {
        if (!$this->loggedin) {
            $this->error = "Not logged in";
            return 0;
        }
        $output = $this->endpoint("/v/" . $subverse . "/block", "DELETE");
        return $output;
    }
    function post_url($subverse, $title, $url, $nsfw=0) {
        if (!$this->loggedin) {
            $this->error = "Not logged in";
            return 0;
        }
        $data = http_build_query(
            array(
                title => $title,
                nsfw => $nsfw,
                url => $url
                )
            );
        $output = $this->endpoint("/v/" . $subverse, "POST", $data, 1);
        return $output;
    }
    function post_self($subverse, $title, $content, $nsfw=0) {
        if (!$this->loggedin) {
            $this->error = "Not logged in";
            return 0;
        }
        $data = json_encode(
            array(
                title => $title,
                nsfw => $nsfw,
                content => $content
                )
            );
        $output = $this->endpoint("/v/" . $subverse, "POST", $data, 1);
        return $output;
    }
    function get_comments($subverse, $id, $parentid=false) {
        if ($parentid):
            $url = "/v/" . $subverse . "/" . $id  . "/comments/" . $parentid;
        else:
            $url = "/v/" . $subverse . "/" . $id  . "/comments";
        endif;
        $output = $this->endpoint($url, "GET");
        return $output;
    }
    function get_comment($id) {
        $output = $this->endpoint("/comments/" . $id, "GET");
        return $output;
    }
    function comment($type, $comment, $commentid, $submissionid, $subverse) {
        if ($type == "top"):
            $ouput = $this->endpoint("/v/" . $subverse . "/" . $submissionid . "/comment");
            return $output;
        elseif ($type == "reply"):
            $ouput = $this->endpoint("/comments/" . $commentid);
            return $output;
        endif;
    }
    function edit_comment($commentid, $content) {
        $data = array(
            value => $content
            );
        $ouput = $this->endpoint("/comments/" . $commentid, "POST", json_encode($data), 1);
        return $output;
    }
}