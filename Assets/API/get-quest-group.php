<?php
/*
Category: groupquest
Description: 
This API is for retrieving quest info in a group.
*/

// required headers
header("Access-Control-Allow-Origin: http://localhost/testapi/");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
 
// files needed to connect to database
include_once 'config/database.php';
include_once 'objects/groupquest.php';
include_once 'objects/quest.php';
include_once 'objects/task.php';
 
// get database connection
$database = new Database();
$db = $database->getConnection();
 
// instantiate object
$groupquest = new GroupQuest($db);
$quest = new Quest($db);
$task = new Task($db);
 
// get posted data
$data = json_decode(file_get_contents("php://input"));
$groupId;

// if empty, try getting from webform instead
if (!empty($data->groupId)) {
	$groupquest->groupId = $data->groupId;
}
elseif (!empty($_POST["groupId"])) {
	$groupquest->groupId = $_POST["groupId"];
}

$json = [];
$quests = [];

$arr = $groupquest->getQuest();

if(!empty($arr)) {
	foreach ($arr as $i) {

		// set questId for quest query
		$quest->id = $i['questId'];
		// set questId for task query
		$task->questId = $i['questId'];

		$tasksJson;

		if($quest->getQuest() && $task->getTasks()) {

			if (!empty($task->tasks)) {
				$tasksJson = [];

				foreach ($task->tasks as $t) {
					array_push($tasksJson, array(
						"id" => $t['taskId'],
						"task" => $t['task'],
						"hint" => $t['hint'],
						"itemId" => $t['itemId'],
						"itemAmount" => $t['itemAmount']
					));
				}
			}
			else {
				$tasksJson = null;
			}
			
		}

		array_push($json, array(
			"id" => $quest->id,
		    "title" => $quest->title,
		    "description" => $quest->description,
		    "topic" => $quest->topic,
		    "reward" => $quest->reward,
		    "level" => $quest->level,
		    "npc" => $quest->npc,
			"tasks" => $tasksJson
		));
	}

	$quests = array( "quests" => $json );

	http_response_code(200);

	echo json_encode($quests);
}
else {
	http_response_code(401);
}



/*if() {

	$task->questId = $quest->id;
	if($task->getTasks()) {

		$tasksJson;

		if (!empty($task->tasks)) {
			$tasksJson = [];

			foreach ($task->tasks as $t) {
				array_push($tasksJson, array(
					"id" => $t['taskId'],
					"task" => $t['task'],
					"hint" => $t['hint'],
				));
			}
		}
		else {
			$tasksJson = null;
		}

		$json = array(
					"id" => $quest->id,
				    "title" => $quest->title,
				    "description" => $quest->description,
				    "topic" => $quest->topic,
				    "reward" => $quest->reward,
				    "level" => $quest->level,
				    "npc" => $quest->npc,
				    "tasks" => $tasksJson
				);

		// success
		http_response_code(200);

		echo json_encode($json);
	}
	else {
		// fail
		http_response_code(401);
	}
}
else {
	// fail
	http_response_code(401);
}*/