<?php

header('Content-Type: application/json');


$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
	case 'GET':
		getUploadedData();
		break;

	case 'POST':
		uploadPhoto();
		break;
	
	default:
		echo '{"result": "Method not allowed!"}';
		break;
}


function getUploadedData() {


	include "../db.php";
	// get from db
	$sqlGet = "SELECT * FROM user_with_photo";
	try {
		$result = mysqli_query($conn, $sqlGet);

		if (mysqli_num_rows($result) > 0) {
			
			$rows = array();
			while ($r = mysqli_fetch_assoc($result)) {
				$rows[] = $r;
			}

			echo json_encode($rows);

		} else {
			echo '{"result":"No data found"}';
		}
		
	} catch(Exception $e) {
		echo '{"result":"Get data not Successful '.$e->getMessage().'"}';
	}


} // getUploadedData


function uploadPhoto() {

	$name = $_POST['name'];
	$title = $_POST['photoTitle'];



	$targetFolder = "./../uploads/";

	if (is_writable($targetFolder)) {
	//	echo "Dir is writable";

		if (!isset($_FILES['my_profile_pic']['error'])) {
			echo '{"result":"Invalid parameter"}';
			die();
		}

		$errMsg = "";
		switch ($_FILES['my_profile_pic']['error']) {
			case UPLOAD_ERR_OK:
				// NO ERROR, Successful upload.
				break;

				case UPLOAD_ERR_NO_FILE:
				$errMsg = "No file sent";
				break;

				case UPLOAD_ERR_INI_SIZE:
				$errMsg = "File size exceeds the limit set in php.ini";
				break;

				case UPLOAD_ERR_FORM_SIZE:
				$errMsg = "File size exceeds";
				break;

				case UPLOAD_ERR_PARTIAL:
				$errMsg = "The uploaded file was only partially uploaded";
				break;

				case UPLOAD_ERR_NO_TMP_DIR:
				$errMsg = "Missing a temporary folder";
				break;

				case UPLOAD_ERR_CANT_WRITE:
				$errMsg = "Failed to write to disk";
				break;

				case UPLOAD_ERR_EXTENSION:
				$errMsg = "A extension stopped the file upload";
				break;

			default:
				$errMsg = "Unknown error occurred";
				break;
		}

		if (!empty($errMsg)) {
			echo '{"result":"'.$errMsg.'"}';
			die();
		}


		$fileName = $_FILES['my_profile_pic']['name'];
		$ext = pathinfo($fileName, PATHINFO_EXTENSION);

		$filePath = $_FILES['my_profile_pic']['tmp_name'];
		
		$fInfo = new finfo(FILEINFO_MIME_TYPE);
		$isValidExt = array_search($fInfo->file($filePath), array(
			'jpg' => 'image/jpeg',
			'png' => 'image/png'
		), true);
		if (!$isValidExt) {
			echo '{"result":"Invalid file format, it should be jpg or png"}';
			die();
		}

		$fileSize = $_FILES['my_profile_pic']['size'];
		if ($fileSize > 1000000) {
			echo '{"result":"Invalid file size, it should be below 1MB"}';
			die();
		}


		$sha1Name = sha1_file($filePath);

		$fPath = $targetFolder . $sha1Name . "." . $ext;
		$fPath2 = "/uploads/" . $sha1Name . "." . $ext;
		if (move_uploaded_file($filePath, $fPath)) {
			
			include "../db.php";
			// save to db
			$sqlSave = "INSERT INTO user_with_photo(name, title, imageUrl) VALUES ('$name','$title','$fPath2')";
			try {
				if (mysqli_query($conn, $sqlSave)) {
					echo '{"result":"Upload Success", "name":"'.$name.'", "photoTitle":"'.$title.'", "photoUrl":"'.$fPath2.'"}';
				} else {
					echo '{"result":"Upload not Success"}';
				}
			} catch(Exception $e) {
				echo '{"result":"Upload not Success '.$e->getMessage().'"}';
			}


			
		} else {
			echo '{"result":"Upload not Success"}';
		}



	} else {
		echo "Dir is not writable";
	}


} // uploadPhoto





	






?>