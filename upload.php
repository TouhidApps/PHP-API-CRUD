<?php 

// chmod 777 /Applications/XAMPP/xamppfiles/htdocs/tut
// chmod 777 /Applications/MAMP/htdocs/tut

header('Content-Type: application/json');

try {
   
    // Undefined | Multiple Files | $_FILES Corruption Attack
    // If this request falls under any of them, treat it invalid.
    if (!isset($_FILES['upfile']['error']) || is_array($_FILES['upfile']['error'])) {
        // throw new RuntimeException('Invalid parameters.');
        echo '{"message":"Invalid parameters."}';
        die();
    }

    // Check $_FILES['upfile']['error'] value.
    switch ($_FILES['upfile']['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
			echo '{"message":"No file sent."}';
        	die();
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            echo '{"message":"Exceeded filesize limit."}';
        	die();
        default:
            echo '{"message":"Unknown errors."}';
        	die();
    }

    // You should also check filesize here.
    if ($_FILES['upfile']['size'] > 1000000) { // 1000000 byte = 1 MB
        echo '{"message":"Exceeded filesize limit. Max 1 MB"}';
        die();
    }

    // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
    // Check MIME Type by yourself.
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    if (false === $ext = array_search(
        $finfo->file($_FILES['upfile']['tmp_name']),
        array(
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
        ),
        true
    )) {
        echo '{"message":"Invalid file format."}';
        die();
    }

    // You should name it uniquely.
    // DO NOT USE $_FILES['upfile']['name'] WITHOUT ANY VALIDATION !!
    // On this example, obtain safe unique name from its binary data.
    $fileReName = sprintf('%s.%s', sha1_file($_FILES['upfile']['tmp_name']), $ext);

    if (!move_uploaded_file($_FILES['upfile']['tmp_name'], './img/' . $fileReName)) {
    	// If failed to move, check upload directory name and it's permission
        echo '{"message":"Failed to move uploaded file."}'; 
        die();
    }


    // get Extra param with file if needed
    $title = $_POST['title'];

    // saved path
    $savedPath = '/img/'.$fileReName;

    // TODO save to database
    // $title and $savedPath


    echo '{"message":"File is uploaded successfully.", "path":"'.$savedPath.'", "title":"' . $title . '"}'; 
    

} catch (RuntimeException $e) {

    echo $e->getMessage();

}

 ?>











