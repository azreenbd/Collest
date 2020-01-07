<?php
// required headers
header("Access-Control-Allow-Origin: http://localhost/testapi/");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// required to decode jwt
include_once 'config/core.php';
include_once 'libs/php-jwt-master/src/BeforeValidException.php';
include_once 'libs/php-jwt-master/src/ExpiredException.php';
include_once 'libs/php-jwt-master/src/SignatureInvalidException.php';
include_once 'libs/php-jwt-master/src/JWT.php';
use \Firebase\JWT\JWT;
 
// files needed to connect to database
include_once 'config/database.php';
include_once 'objects/group.php';
include_once 'objects/user.php';
 
// get database connection
$database = new Database();
$db = $database->getConnection();
 
// instantiate product object
$group = new Group($db);
$user = new User($db);
 
// get posted data
$data = json_decode(file_get_contents("php://input"));

// if empty, try getting from webform instead
if (!empty($data)) {
    // set product property values
    $group->name = $data->name;
    $jwt = $data->jwt;
}
elseif (!empty($_POST["name"]) && !empty($_POST["jwt"])) {
    $group->name = $_POST["name"];
    $jwt = $_POST["jwt"];
}
else {
    $jwt = "";
}

if($jwt) {
 
    // if decode succeed, show user details
    try {
        // decode jwt
        $decoded = JWT::decode($jwt, $key, array('HS256'));

        // to use with $user->groupExists() and $group->create()
        $user->id = $decoded->data->id;
        $group->creator = $decoded->data->id;

        // check if user have group
        if(!$user->groupExists() && $group->create()) {

            if($group->creatorExists()) {

                $user->group = $group->id;

                if($user->joinGroup()) {
                    http_response_code(200);
                    echo json_encode(array("message" => "Group created."));
                }
                else {
                    // if the creator unable to join his own group, delete it
                    $group->delete();

                    http_response_code(401);
                    echo json_encode(array("message" => "Access denied."));
                }
            }
            else {
                http_response_code(401);
                echo json_encode(array("message" => "Access denied."));
            }
        }
        else {
            http_response_code(401);
            echo json_encode(array("message" => "Access denied."));
        }
    }
    catch (Exception $e){ // if decode fails, it means jwt is invalid
     
        // set response code
        http_response_code(401);
     
        // tell the user access denied  & show error message
        echo json_encode(array(
            "message" => "Access denied.",
            "error" => $e->getMessage()
        ));
    }
}
else { // show error message if jwt is empty
 
    // set response code
    http_response_code(401);
 
    // tell the user access denied
    echo json_encode(array("message" => "Access denied."));
}
?>