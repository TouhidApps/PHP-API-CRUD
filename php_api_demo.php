<?php 


header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

 case 'POST': // create data

      $data = json_decode(file_get_contents('php://input'), true);  // true means you can convert data to array
    //  print_r($data);
      postOperation($data);

      break;

 	case 'GET': // read data
		  getOperation();
    	break;

  case 'PUT': // update data
      $data = json_decode(file_get_contents('php://input'), true);  // true means you can convert data to array
      putOperation($data);
      break;

  case 'DELETE': // delete data
      $data = json_decode(file_get_contents('php://input'), true);  // true means you can convert data to array
		  deleteOperation($data);
    	break;

  default:
    	print('{"result": "Requested http method not supported here."}');

}



// functions
  function putOperation($data){
    include "db.php";

    $id = $data["id"];
    $name = $data["name"];
    $phone = $data["phone"];
    $area = $data["address"]["area"];
    $road = $data["address"]["road"];
    $fullAddress = $area . ", " . $road;

    $sql = "UPDATE demo_api SET name = '$name', phone = '$phone', address = '$fullAddress', exe_time = NOW() WHERE id = '$id'";

    if (mysqli_query($conn, $sql) or die()) {
        echo '{"result": "Success"}';
    } else {
        echo '{"result": "Sql error"}';
    }

  }

  function getOperation(){

    include "db.php";

    $sql = "SELECT * FROM demo_api";

    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        // output data of each row
      $rows = array();
       while($r = mysqli_fetch_assoc($result)) {
          $rows["result"][] = $r; // with result object
        //  $rows[] = $r; // only array
       }
      echo json_encode($rows);

    } else {
        echo '{"result": "No data found"}';
    }

  }

  function postOperation($data){
   // print_r($data);
 
    include "db.php";

    $name = $data["name"];
    $phone = $data["phone"];
    $area = $data["address"]["area"];
    $road = $data["address"]["road"];
    $fullAddress = $area . ", " . $road;

    $sql = "INSERT INTO demo_api(name, phone, address, exe_time) VALUES('$name', '$phone', '$fullAddress', NOW())";

    if (mysqli_query($conn, $sql)) {
        echo '{"result": "Success"}';
    } else {
        echo '{"result": "Sql error"}';
    }



  }

  function deleteOperation($data){

    include "db.php";

    $id = $data["id"];

    $sql = "DELETE FROM demo_api WHERE id = $id";

    if (mysqli_query($conn, $sql)) {
        echo '{"result": "Success"}';
    } else {
        echo '{"result": "Sql error"}';
    }

  }


 ?>