<?php
/**
 * Created by PhpStorm.
 * User: Eric
 * Date: 10/13/2017
 * Time: 9:49 PM
 */
include '../include/UserDbHandler.php';
$db = new UDbHandler();

require_once('../include/AfricasTalkingGateway.php');

$response = array();

if (isset($_POST['mobile']) && $_POST['mobile'] != '') {

    $name = $_POST['name'];

    $mobile = $_POST['mobile'];

    $token = $_POST['token'];

    $image="null";

    $otp = rand(100000, 999999);

    $res = $db->createUser($name,  $mobile, $otp,$token,$image);

    if ($res == USER_CREATED_SUCCESSFULLY) {

        // send sms
        africasTalking($mobile, $otp);

        $response["error"] = false;
        $response["message"] = "SMS request is initiated! You will be receiving it shortly.";
    } else if ($res == USER_CREATE_FAILED) {
        $response["error"] = true;
        $response["message"] = "Sorry! Error occurred in registration.";
    } else if ($res == USER_ALREADY_EXISTED) {
        $response["error"] = true;
        $response["message"] = "Mobile number already existed!";
    }
} else {
    $response["error"] = true;
    $response["message"] = "Sorry! mobile number is not valid or missing.";
}

echo json_encode($response);


function africasTalking($recipients,$otp){

// Specify your login credentials
    $username   = "erickogi";
    $apikey     = "b532cdf16df3b7d5dac53fee4e15ca1e55d14a7be6771bdc8306724e09ea88a1";
// NOTE: If connecting to the sandbox, please use your sandbox login credentials
// Specify the numbers that you want to send to in a comma-separated list
// Please ensure you include the country code (+254 for Kenya in this case)
    //   $recipients = "+254711XXXYYY,+254733YYYZZZ";
// And of course we want our recipients to know what we really do
    //   $message    = "I'm a lumberjack and its ok, I sleep all night and I work all day";
// Create a new instance of our awesome gateway class
    $gateway    = new AfricasTalkingGateway($username, $apikey);
// NOTE: If connecting to the sandbox, please add the sandbox flag to the constructor:
    /*************************************************************************************
     ****SANDBOX****
    $gateway    = new AfricasTalkingGateway($username, $apiKey, "sandbox");
     **************************************************************************************/
// Any gateway error will be captured by our custom Exception class below,
// so wrap the call in a try-catch block


    $otp_prefix = ':';

    //Your message to send, Add URL encoding here.
    $message = "Hello! Welcome to Mobisell. Your Verification Code is '$otp_prefix $otp'";
    //$message="BLUE.jxc.Mirr.g.thika.  Tecno 626466 ime :********k** .Virus :hrank3.3 Battery :**2 SMS ***3 CALL**";

    try
    {
        // Thats it, hit send and we'll take care of the rest.
        $results = $gateway->sendMessage($recipients, $message);

        foreach($results as $result) {
            // status is either "Success" or "error message"
            //  echo " Number: " .$result->number;
            //  echo " Status: " .$result->status;
            //   echo " MessageId: " .$result->messageId;
            //   echo " Cost: "   .$result->cost."\n";
        }
    }
    catch ( AfricasTalkingGatewayException $e )
    {
        // echo "Encountered an error while sending: ".$e->getMessage();
    }





}




?>