
<?php

// DATABASE CONNECTION
$db_conn = mysqli_connect("localhost","root","","upload_image");

// CHECKING THE DATABASE CONNECTION
if (mysqli_connect_errno())
{
    echo "Failed to connect to MySQL Database: " . mysqli_connect_error();
    exit;
}

// FETCH ALL IMAGES FROM DATBASE
$all_images = mysqli_query($db_conn,"SELECT * FROM `images`");


if(isset($_POST['submitUpload']) && isset($_FILES['targetFile'])){

    $error_int = $_FILES['targetFile']['error'];

    $fileSize = $_FILES['targetFile']['size'];

    $upload_dir = 'C:\xampp\htdocs\uploads';

    $targetFile = $upload_dir.$_FILES['targetFile']['name'];

    $path_info = pathinfo($_FILES["targetFile"]["name"]);

    $tmpName = $_FILES["targetFile"]["tmp_name"];

    $fileType = ['png','jpg','jpeg','gif'];

    if($error_int === 1){
        echo "File is too large | The uploaded file exceeds the upload_max_filesize.";
    }
    // if $_FILES IS EMPTY
    elseif($error_int === 4){
        header('Location: index.php');
        exit;
    }
    elseif($fileSize > 1048576){
        echo "The file size is over 1MB, that's why this file is not allowed to upload.";
    }
    elseif(!in_array($path_info['extension'],$fileType)){
        echo "Please choose an Image file.";
    }
    else{

        $number = 1;
        while(file_exists($targetFile)){
            $targetFile = $upload_dir.$path_info['filename']."-".$number.".".$path_info['extension'];
            $number++;
        }

        $is_uploaded = move_uploaded_file($tmpName, $targetFile);

        if($is_uploaded){

            $file_name = basename($targetFile);

            // INSERT STATEMENT
            $image_name_insert_stmt = mysqli_prepare($db_conn, "INSERT INTO `images` (image_name) VALUES (?)");
            mysqli_stmt_bind_param($image_name_insert_stmt, "s", $file_name);

            // CHECKING, IF THE FILE NAME SAVED
            if(mysqli_stmt_execute($image_name_insert_stmt)){
                header('Location: index.php');
                exit;
            }
            else{
                echo "Failed to save the file name into the Database.";
            }

            echo "The file uploaded successfully";
        }
        else{
            echo "The file not uploaded.";
        }

    }

    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Upload Images</title>
    <style>
        .all_images{
            display:flex;
            flex-wrap:wrap;
        }
        .image-wrapper{
            width:150px;
            border:1px solid #dedede;
            flex:1 1 auto;
            margin:5px;
            padding:3px;
            display:flex;
        }
        .image-wrapper img{
            width:100%;
            display:block;
        }

    </style>
</head>
<body>

<form action="./index.php" method="POST" enctype="multipart/form-data">
    <label for="myFile"><b>Select file to upload:</b></label><br>
    <input type="file" name="targetFile" id="myFile">
    <input type="submit" name="submitUpload" value="Upload">
</form>

<hr/>
<h3>All images</h3>

<div class="all_images">
    <?php
    if(mysqli_num_rows($all_images) > 0){

        while($row = mysqli_fetch_assoc($all_images)){

            echo '<div class="image-wrapper">
                <img src="uploads/'.$row['image_name'].'">
            </div>';

        }

    }
    else{
        echo '<p>There are no images. Please insert some images.</p>';
    }
    ?>
</div>

</body>
</html>
