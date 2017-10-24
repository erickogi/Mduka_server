<?php
/**
 * Created by PhpStorm.
 * User: Eric
 * Date: 10/13/2017
 * Time: 9:56 PM
 */


include '../include/UserDbHandler.php';
$db = new UDbHandler();


$response = array();



if (isset($_POST['mobile']) && $_POST['mobile'] != '') {

    $name = $_POST['name'];
    $shopemail = $_POST['shopemail'];
    $mobile = $_POST['mobile'];

    $shopname = $_POST['shopname'];
    $image = $_POST['image'];
    $shopphone=$_POST['shopphone'];
   



    $path = "/uploads/$mobile.png";
    
    
    $path1 = "../uploads/$mobile.png";
    
    
    
    
     
    
    

    $actualpath = "http://wwww.erickogi.co.ke/Mduka/Mduka/$path";
  
    $res = $db->updateUser($name,  $mobile, $shopname,$actualpath,$shopemail,$shopphone);

    if ($res == USER_UPDATED_SUCCESSFULLY) {

           file_put_contents($path1,base64_decode($image));
           // echo "Successfully Uploaded";


        $response["error"] = false;
        $response["message"] = "User Updated Successfully";
    } else if ($res == USER_UPDATE_FAILED) {
        $response["error"] = true;
        $response["message"] = "Sorry! Error occurred in registration.";
    }
} else {
    $response["error"] = true;
    $response["message"] = "Sorry! mobile number is not valid or missing.";
}

echo json_encode($response);
