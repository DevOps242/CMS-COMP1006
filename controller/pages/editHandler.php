<?php 
session_start();
require_once __DIR__ . '/../../model/Database.php';
require_once __DIR__ . '/../../utilities/Log.php';


// Check to make sure the fields are not empty.
$userGUID       = $_SESSION['userGUID'];
$pageID         = $_GET['id'];
$pageName       = $_POST['pageName'];
$pageTitle      = $_POST['pageTitle'];
$pageContent    = $_POST['pageContent'];
$pageStatus     = $_POST['pageStatus'];
$pageImg        = null;

// Validate fields not empty
if (empty($pageName) || empty($pageTitle) || empty($pageContent)) {
    $serverMessage = "Error, all fields must be filled. {Page Name, Page Title, and Page Content}";
    header('Location: ../../view/admin/page-edit.php?id='.$pageID.'&message=' . $serverMessage);
    exit;  
} 

if ( !empty($_FILES['pageImg']['name']) ){
        $pageImg = $_FILES['pageImg']['name'];
        // Where the file is going to be stored
        $target_dir = __DIR__ . '/../../storage/app/images/';;
        $file = $_FILES['pageImg']['name'];
        $path = pathinfo($file);
        $filename = $path['filename'];
        $ext = $path['extension'];
        $temp_name = $_FILES['pageImg']['tmp_name'];
        $path_filename_ext = $target_dir.$filename.".".$ext;
        
    // Check if file already exists
    if (file_exists($path_filename_ext) ) {
        // Delete the file 
        unlink($path_filename_ext);
    } 
    // Upload the file.
    move_uploaded_file($temp_name,$path_filename_ext);
}

try {
    // Add info to the database.
    $db = new Database();
    
    // Update the record in the database
    $query = "UPDATE CMSPages 
             SET pageName = :pageName, pageTitle = :pageTitle, pageContent = :pageContent, pageStatus = :pageStatus, userGUID = :userGUID 
             WHERE pageID = :pageID"; 
    if (isset($pageImg)) {
        $query = "UPDATE CMSPages 
        SET pageName = :pageName, pageTitle = :pageTitle, pageContent = :pageContent, pageStatus = :pageStatus, pageImg = :pageImg, userGUID = :userGUID 
        WHERE pageID = :pageID"; 
    }
    $cmd = $db->connect->prepare($query);
    $cmd->bindParam(':pageName', $pageName, PDO::PARAM_STR, 255);
    $cmd->bindParam(':pageTitle', $pageTitle, PDO::PARAM_STR, 255);
    $cmd->bindParam(':pageContent', $pageContent, PDO::PARAM_STR);
    if (isset($pageImg)) {
        $cmd->bindParam(':pageImg', $pageImg, PDO::PARAM_STR, 255);
    }
    $cmd->bindParam(':pageStatus', $pageStatus, PDO::PARAM_STR, 255);
    $cmd->bindParam(':userGUID', $userGUID, PDO::PARAM_STR, 255);
    $cmd->bindParam(':pageID', $pageID, PDO::PARAM_INT);
    $cmd->execute();

    $serverMessage = "Page has been edited successfully";
    header('Location: ../../view/admin/page-edit.php?id='.$pageID.'&message=' . $serverMessage);
    exit;    

} catch (Exception $error) {
    $serverMessage = "There was and error creating your page.";
    Log::error('Page Creation Error: ' . json_encode( $serverMessage . PHP_EOL . $error->getMessage()));
    // Send user to general eror page.
    header('Location: ../../view/error.php');
    exit;   
}
       
 
  


               

    


require_once 'includes/footer.php';

?>