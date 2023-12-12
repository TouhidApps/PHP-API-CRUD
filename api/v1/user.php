<?php


header('Content-Type: application/json');


$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
	case 'GET':
		getUser();
		break;

	case 'POST':
		insertUser();
		break;

	case 'PUT':
		updateUser();
		break;

	case 'DELETE':
		deleteUser();
		break;

	
	default:
		echo '{"result": "Method not allowed!"}';
		break;
}


function getUser() {

	include "../db.php";


	$pageNo = 1;
	if (isset($_GET['pageNo'])) {
		$pageNo = $_GET['pageNo'];
	}

	$perPage = 30;
	if (isset($_GET['perPageData'])) {
		$perPage = $_GET['perPageData'];
	}

	$offset = $perPage * ($pageNo - 1);

	$sql = "SELECT * FROM (SELECT DISTINCT * FROM user ORDER BY uId ASC LIMIT $perPage OFFSET $offset) AS u\n"

    . "LEFT JOIN address ON u.uId = address.user_id\n"

    . "LEFT JOIN user_location ON u.uId = user_location.user_id\n"

    . "LEFT JOIN location ON location.id = user_location.location_id;";


    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
    	
    	$rows = array();
    	while ($r = mysqli_fetch_assoc($result)) {
    		// $rows[] = $r;

    		$uId = $r['uId'];
    		if (in_array($uId, array_column($rows, "userId"))) {
    			
    			$locationObj = array();
	    		$locId = $r['location_id'];
	    		if (!empty($locId)) {
	    			$locationObj = array(
		    			"locationId" 	=> (int)$locId,
		    			"locationName" 	=> $r['locationName']
	    			);
	    			array_push($rows[count($rows)-1]['location'], $locationObj);

	    		}	

    		} else {
    			$addressObj = array();
	    		$addId = $r['id'];
	    		if (!empty($addId)) {
	    			$addressObj = array(
						"addressId"	 	=> (int)$addId,
		    			"city"	 		=> $r['city'],
		    			"country" 		=> $r['country'],
	    			);
	    		}

				$locationObj = array();
	    		$locId = $r['location_id'];
	    		if (!empty($locId)) {
	    			$locationObj = array(
		    			"locationId" 	=> (int)$locId,
		    			"locationName" 	=> $r['locationName']
	    			);
	    		}	

	    		$rows[] = array(

	    			"userId" 		=> (int)$r['uId'],
	    			"name" 			=> $r['name'],
	    			"phoneNumber" 	=> $r['phoneNumber'],
	    			"amount" 		=> (double)$r['amount'],
	    			"address"		=> $addId != NULL ? $addressObj : (object)array(),
	    			"location" 		=> $locId != NULL ? array($locationObj) : (array)array()

	    		);
    		}

    	}

    	echo json_encode($rows);

    } else {
    	echo '{"result": "No data found."}';
    }


} // getUser


function insertUser() {

	$data = json_decode(file_get_contents("php://input"), true);

	$name 			= $data['name'];
	$phoneNumber 	= $data['phoneNumber'];
	$amount			= $data['amount'];

	$city			= $data['city'];
	$country		= $data['country'];

	$locations		= $data['locations']; // array

	include "../db.php";
	$sqlInsertUser = "INSERT INTO user(name, phoneNumber, amount) VALUES ('$name','$phoneNumber','$amount')";
	try {
		$isUserSaved = mysqli_query($conn, $sqlInsertUser);
		$userId = NULL;
		if ($isUserSaved) {
			$userId = mysqli_insert_id($conn);
		}

		if (!empty($city) || !empty($country)) {
			$sqlAddress = "INSERT INTO address(user_id, city, country) VALUES ('$userId','$city','$country')";
			mysqli_query($conn, $sqlAddress);
		}

		if (!empty($locations)) {
			$sqlLocations = "INSERT INTO user_location(user_id, location_id) VALUES";

			foreach ($locations as $index => $location) {
				$sqlLocations .= "('$userId','$location')";
				if ($index < count($locations)-1) {
					$sqlLocations .= ",";
				}
			}
			if(mysqli_query($conn, $sqlLocations)) {
				echo '{"result": "Success"}';
			} else {
				echo '{"result": "Error"}';
			}

		} else {
			if ($isUserSaved) {
				echo '{"result": "Success"}';
			} else {
				echo '{"result": "Error"}';
			}
		}

	} catch (Exception $e) {
		echo "Error " . $e;
	}

} // insertUser

function updateUser() {

	$data = json_decode(file_get_contents("php://input"), true);

	$userId 		= $data['userId'];
	$name 			= $data['name'];
	$phoneNumber 	= $data['phoneNumber'];
	$amount			= $data['amount'];

	$city			= $data['city'];
	$country		= $data['country'];

	$locations		= $data['locations']; // array

	include "../db.php";
	$sqlInsertUser = "UPDATE user SET name = CASE WHEN '$name' != '' THEN '$name' ELSE name END, phoneNumber= CASE  WHEN '$phoneNumber' != '' THEN '$phoneNumber' ELSE phoneNumber END, amount = CASE WHEN '$amount' != '' THEN '$amount' ELSE amount END WHERE uId = '$userId'";


	try {
		$isUserUpdated = mysqli_query($conn, $sqlInsertUser);

// Address
		if (!empty($city) || !empty($country)) {
			$sqlAddressId = "SELECT user_id FROM address WHERE user_id = '$userId'";
			$aId = mysqli_query($conn, $sqlAddressId);
			$row = $aId->fetch_assoc();
			$usrId = $row['user_id'];

			if (!empty($usrId)) {
				
				$sqlAddressUpdate = "UPDATE address SET city = '$city', country = '$country' WHERE user_id = '$userId'";
				mysqli_query($conn, $sqlAddressUpdate);

			} else {

				$sqlInsertAddress = "INSERT INTO address(user_id, city, country) VALUES ('$userId','$city','$country')";
				mysqli_query($conn, $sqlInsertAddress);

			}

		}

// Location

		$sqlLocationDelete = "DELETE FROM user_location WHERE user_id = '$userId'";
		try {
			mysqli_query($conn, $sqlLocationDelete);
		} catch(Exception $e) {
			echo "Eror " . $e;
		}

		if (!empty($locations)) {
			
			$sqlLocations = "INSERT INTO user_location(user_id, location_id) VALUES";

			foreach ($locations as $index => $location) {
				$sqlLocations .= "('$userId','$location')";
				if ($index < count($locations)-1) {
					$sqlLocations .= ",";
				}
			}
			if(mysqli_query($conn, $sqlLocations)) {
				echo '{"result": "Success"}';
			} else {
				echo '{"result": "Error"}';
			}


		} else {
			if ($isUserUpdated) {
				echo '{"result": "Success"}';
			} else {
				echo '{"result": "Error"}';
			}
		}

	} catch(Exception $e) {
		echo "Error ".$e;
	}



} // updateUser

function deleteUser() {

	$data = json_decode(file_get_contents("php://input"), true);

	$userId = $data['userId'];

	include "../db.php";

	$sqlDelete = "DELETE FROM user WHERE uId = '$userId'";
	$isDeleted = mysqli_query($conn, $sqlDelete);

	if ($isDeleted) {
		echo '{"result": "Successfully deleted"}';
	} else {
		echo '{"result": "Error"}';
	}


} // deleteUser









?>