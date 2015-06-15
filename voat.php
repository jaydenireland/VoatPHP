<?php
class Voat {
    private $apikey;
    var $error = array();
    var $baseurl = "https://fakevout.azurewebsites.net/api/";
    var $version = "v1";
    var $token;
    var $loggedin = false;
    var $encryptionkey = "awkdmnawoidm";
    public function __construct($apikey) {
        $this->apikey = $apikey;
        $test = $this->get_subverse("test");//Test if API key worked or not
        if ($test['success'] !== true) {
            array_push($this->error, $test['error']['message']);
        }
    }
    public function endpoint($endpoint, $type="POST", $data="", $json=false) { //Access an endpoint
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
            $test = json_decode(curl_exec($ch), 1);
            if (!$test['success']) {
                array_push($this->error, $test['error']['message']);
            }
            return $test;
    }
    public function user($username, $password) { //Authenitcate user
        $foundtoken = 0;
        $flatfile = json_decode(file_get_contents("tokens.json"), 1);
        while ($users = current($flatfile['users'])) {
            if ($users['username'] == $username) {
                $encrypted = $flatfile['users'][key($flatfile['users'])]['token'];
                $this->token = openssl_decrypt($encrypted, "aes128", $this->encryptionkey);
                $foundtoken = 1;
            }
            next($flatfile['users']);
        }
        if (!$foundtoken) {
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
        error_reporting(0);
        array_push($flatfile['users'], array(
            username => $username,
            token => openssl_encrypt($output['access_token'], "aes128", $this->encryptionkey))
        );
        file_put_contents("tokens.json", json_encode($flatfile));
        error_reporting(E_ERROR | E_WARNING | E_PARSE);
        }
        //echo $this->token;
        if ($output['success']) {
            $this->loggedin = true;
        }
        return $output;
    }
    public function logout() {
        $this->token = null;
        $this->loggedin = false;
    }
    public function get_subverse($subverse) {
        $output = $this->endpoint("/v/" . $subverse, "GET");
        return $output;
    }
    public function get_subverse_info($subverse) {
        $output = $this->endpoint("/v/" . $subverse . "/info", "GET");
        return $output;
    }
    public function block_subverse($subverse) {
        if (!$this->loggedin) {
            $this->error = "Not logged in";
            return null;
        }
        $output = $this->endpoint("/v/" . $subverse . "/block", "POST");
        return $output;
    }
    public function unblock_subverse($subverse) {
        if (!$this->loggedin) {
            $this->error = "Not logged in";
            return null;
        }
        $output = $this->endpoint("/v/" . $subverse . "/block", "DELETE");
        return $output;
    }
    public function post_url($subverse, $title, $url, $nsfw=0) {
        if (!$this->loggedin) {
            $this->error = "Not logged in";
            return null;
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
    public function post_self($subverse, $title, $content, $nsfw=0) {
        if (!$this->loggedin) {
            $this->error = "Not logged in";
            return null;
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
    public function get_comments($subverse, $id, $parentid=false) {
        if ($parentid):
            $url = "/v/" . $subverse . "/" . $id  . "/comments/" . $parentid;
        else:
            $url = "/v/" . $subverse . "/" . $id  . "/comments";
        endif;
        $output = $this->endpoint($url, "GET");
        return $output;
    }
    public function get_comment($id) {
        $output = $this->endpoint("/comments/" . $id, "GET");
        return $output;
    }
    public function comment_top($comment, $subverse, $submissionid) {
        $data = json_encode(
            array(
                value => $comment
                )
            );
        $ouput = $this->endpoint("/v/" . $subverse . "/" . $submissionid . "/comment", "POST", $data, 1);
        return $output;
    }
    public function comment_reply($comment, $commentid) {
            $data = json_encode(
                array(
                    value => $comment
                    )
                );
            $output = $this->endpoint("/v/" . $subverse . "/" . $submissionid . "/comment/" . $commentid, "POST", $data, 1);
            return $output;
    }
    public function edit_comment($commentid, $content) {
        if (!$this->loggedin) {
            $this->error = "Not logged in";
            return null;
        }
        $data = array(
            value => $content
            );
        $ouput = $this->endpoint("/comments/" . $commentid, "POST", json_encode($data), 1);
        return $output;
    }
    public function delete_comment($commentid) {
        if (!$this->loggedin) {
            $this->error = "Not logged in";
            return null;
        }
        $ouput = $this->endpoint("/comments/" . $commentid, "DELETE", "", 1);
        return $output;
    }
    public function user_prefernces() {
        if (!$this->loggedin) {
            $this->error = "Not logged in";
            return null;
        }
        $output = $this->endpoint("/u/preferences");
        return $output;
    }
}
