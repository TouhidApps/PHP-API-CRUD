<?php


header('Content-Type: application/json');


$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
	case 'GET':
		getLocation();
		break;

	case 'POST':
		insertLocation();
		break;

	case 'PUT':
		updateLocation();
		break;

	case 'DELETE':
		deleteLocation();
		break;

	
	default:
		echo '{"result": "Method not allowed!"}';
		break;
}


function getLocation() {

	include "../db.php";

	$sql = "SELECT * FROM location";

    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
    	
    	$rows = array();
    	while ($r = mysqli_fetch_assoc($result)) {
    		 $rows[] = $r;
    	}

    	echo json_encode($rows);

    } else {
    	echo '{"result": "No data found."}';
    }


} // getUser


function insertLocation() {

	$data = json_decode(file_get_contents("php://input"), true);

	$name = $data['locationName'];

	include "../db.php";
	$sqlInsertLocaton = "INSERT INTO location(locationName) VALUES ('$name')";
	try {
		$isLocationSaved = mysqli_query($conn, $sqlInsertLocaton);
		
		if ($isLocationSaved) {
			echo '{"result": "Success"}';
		} else {
			echo '{"result": "Error"}';
		}
		

	} catch (Exception $e) {
		echo "Error " . $e;
	}

} // insertUser

function updateLocation() {

	$data = json_decode(file_get_contents("php://input"), true);

	$locationId		= $data['locationId'];
	$name 			= $data['locationName'];

	include "../db.php";
	$sqlInsertLocation = "UPDATE location SET locationName = '$name' WHERE id = '$locationId'";

	try {
		$isUserUpdated = mysqli_query($conn, $sqlInsertLocation);
		if ($isUserUpdated) {
			echo '{"result": "Success"}';
		} else {
			echo '{"result": "Error"}';
		}
	} catch(Exception $e) {
		echo "Error ".$e;
	}



} // updateUser

function deleteLocation() {

	$data = json_decode(file_get_contents("php://input"), true);

	$locationId = $data['locationId'];

	include "../db.php";

	$sqlDelete = "DELETE FROM location WHERE id = '$locationId'";
	$isDeleted = mysqli_query($conn, $sqlDelete);

	if ($isDeleted) {
		echo '{"result": "Successfully deleted"}';
	} else {
		echo '{"result": "Error"}';
	}


} // deleteUser









?>