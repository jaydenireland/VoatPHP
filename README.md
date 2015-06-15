Voat PHP API
==========

A php library that allows for easy access to the Voat API.

# Documentation (WIP...)
## Encryption
A file called "encryption.key" is needed in the directory that the voat.php script is in. 
In the file should be a password that will be used to encrypt all the user tokens, by the wishes of [/u/PutItOutPlease](https://fakevout.azurewebsites.net/v/Api/comments/2641)
## Initialize the class
```PHP
require_once("path/to/your/voat.php");
$voat = new voat("email", "password");
```
## If login is succesful
```PHP
if ($voat->error) {
    echo "Succesfully logged in!";
} else {
    echo "That username and password didn't work";
}
```
The wrapper returns the json decoded response in an array, so some debugging should be in place but most responses look like this before parsed by json_decode
```JSON
{"code": "", "data": {}, "success": true, "error": ""}
```
### Here are all the functions you can call at the moment
```PHP
user($username, $password);
logout();
get_subverse($subverse);
get_subverse_info($subverse);
block_subverse($subverse);
unblock_subverse($subverse);
post_url($subverse, $title, $url, $nsfw);
post_self($subverse, $title, $content, $nsfw);
get_comments($subverse, $id, $parentid);
get_comment($id);
comment_top($comment, $subverse, $submissionid);
comment_reply($comment, $commentid);
edit_comment($commentid, $content);
delete_comment($commentid);
user_prefernces();
```
