<?php

/**
 * Class to handle all db operations
 * This class will have CRUD methods for database tables
 *
 * @author Ravi Tamada
 * @link URL Tutorial link
 */
class UDbHandler {

    private $conn;

    function __construct() {
        require_once dirname(__FILE__) . '/DbConnect.php';
        // opening db connection
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    /* ------------- `odijo_tutor` table method ------------------ */

    /**
     * Creating new user
     * @param String $name User full name
     * @param String $email User login email id
     * @param String $mobile User mobile number
     * @param String $otp user verificaiton code
     */
    public function createUser($name,  $mobile, $otp,$token,$image) {
        $response = array();

        // First check if user already existed in db
        if (!$this->isUserExists($mobile)) {

            // Generating API key
            $api_key = $this->generateApiKey();


//            if($this->isUserExistsUnregistered($mobile)){
//                $user_id=$this->getUserId($mobile);
//                $otp_result=$this->createOtp($user_id,$otp,$mobile);
//                return USER_CREATED_SUCCESSFULLY;
//            }else {
                // insert query
                $stmt = $this->conn->prepare("INSERT INTO mduka_users(name, mobile, apikey, token, status,image) values( ?, ?, ?, ?,0, ?)");
                $stmt->bind_param("sssss", $name,  $mobile, $api_key, $token, $image);

                $result = $stmt->execute();

                $new_user_id = $stmt->insert_id;

                $stmt->close();

                // Check for successful insertion
                if ($result) {

                    $otp_result = $this->createOtp($mobile, $otp);

                    // User successfully inserted
                    return USER_CREATED_SUCCESSFULLY;
                } else {
                    // Failed to create user
                    return USER_CREATE_FAILED;
                }
           // }
        } else {

            // User with same email already existed in the db
            return USER_ALREADY_EXISTED;
        }

        return $response;
    }

    public function createOtp($user_id, $otp) {
         //$id=$this->getUserId($mobile);
       // echo $id;
        // delete the old otp if exists
        $stmt = $this->conn->prepare("DELETE FROM sms_codes where user_id = ?");
        $stmt->bind_param("s", $user_id);
        $stmt->execute();


        $stmt = $this->conn->prepare("INSERT INTO sms_codes(user_id, code, status) values(?, ?, 0)");
        $stmt->bind_param("ss", $user_id, $otp);

        $result = $stmt->execute();

        $stmt->close();

        return $result;
    }

    /**
     * Checking for duplicate user by mobile number
     * @param String $email email to check in db
     * @return boolean
     */
    private function isUserExists($mobile) {
        $stmt = $this->conn->prepare("SELECT id from mduka_users WHERE mobile = ? and status = 1");
        $stmt->bind_param("s", $mobile);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }
    private function isUserExistsUnregistered($mobile) {
        $stmt = $this->conn->prepare("SELECT id from mduka_users WHERE mobile = ? and status = 0");
        $stmt->bind_param("s", $mobile);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function activateUser($otp) {
//        $stmt = $this->conn->prepare("SELECT u.id, u.name, u.email, u.mobile, u.apikey, u.token, u.status, u.created_at FROM odijo_tutor u, sms_codes WHERE sms_codes.code = ? AND sms_codes.user_id = u.id");
//        $stmt->bind_param("s", $otp);
        $stmt = $this->conn->prepare("SELECT u.id, u.name, u.mobile, u.apikey ,u.token, u.status, u.created_at,u.image FROM mduka_users u, sms_codes WHERE sms_codes.code = ? AND sms_codes.user_id = u.mobile");
        $stmt->bind_param("s", $otp);


        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($id, $name,  $mobile, $apikey,$token, $status, $created_at,$image);

            $stmt->store_result();

            if ($stmt->num_rows > 0) {

                $stmt->fetch();

                // activate the user
                $this->activateUserStatus($mobile);

                $user = array();
                $user["name"] = $name;

                $user["mobile"] = $mobile;
                $user["apikey"] = $apikey;
                $user["token"] = $token;
                $user["status"] = $status;
                $user["created_at"] = $created_at;

                $user["image"]=$image;

                $stmt->close();

                return $user;
            } else {
                return NULL;
            }
        } else {
            return NULL;
        }

        return $result;
    }
    //Activating user registration status
    public function activateUserStatus($user_id){
        $stmt = $this->conn->prepare("UPDATE mduka_users set status = 1 where mobile = ?");
        $stmt->bind_param("s", $user_id);

        $stmt->execute();

        $stmt = $this->conn->prepare("UPDATE sms_codes set status = 1 where user_id = ?");
        $stmt->bind_param("s", $user_id);

        $stmt->execute();
    }
    
    //checking whether user is registered
     public function isUserRegisterd($mobile) {
        $stmt = $this->conn->prepare("SELECT id from mduka_users WHERE (mobile = ? and status = 1) ");
        $stmt->bind_param("s", $mobile);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }


    //getting a user's details based on mobile no/email
    public function getUserDetails($mobile){

        $stmt = $this->conn->prepare("SELECT `id`, `name`,  `mobile`, `apikey`,`token`, `status`, `created_at`,`image`,`shopname`,`shopemail`,`shopphone` FROM mduka_users  WHERE (mobile = ? and status = 1)  ");
        $stmt->bind_param("s", $mobile);

        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($id, $name,  $mobile, $apikey,$token, $status, $created_at,$image,$shopname,$shopemail,$shopphone);

            $stmt->store_result();

            if ($stmt->num_rows > 0) {

                $stmt->fetch();

                // activate the user
                //$this->activateUserStatus($id);

                $user = array();
                $user["name"] = $name;

                $user["mobile"] = $mobile;
                $user["apikey"] = $apikey;
                $user["token"] = $token;
                $user["status"] = $status;
                $user["created_at"] = $created_at;

                $user["image"] = $image;
                $user["shopname"] = $shopname;
                $user["shopemail"] = $shopemail;

                $user["shopphone"] = $shopphone;




                $stmt->close();

                return $user;
            } else {
                return NULL;
            }
        } else {
            return NULL;
        }

        return $result;



    }
    //getting user id
    public function getUserId($mobile){
        $stmt = $this->conn->prepare("SELECT id FROM mduka_users WHERE mobile = ? ");
        $stmt->bind_param("s",$mobile);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return array($result['id']);
    }
    
     //getting all tokens to send push to all devices
    public function getAllTokens(){
        $stmt = $this->conn->prepare("SELECT token FROM mduka_users");
        $stmt->execute(); 
        $result = $stmt->get_result();
        $tokens = array(); 
        while($token = $result->fetch_assoc()){
            array_push($tokens, $token['token']);
        }
        return $tokens; 
    }
 
    //getting a specified token to send push to selected device
    public function getTokenByEmail($email){
        $stmt = $this->conn->prepare("SELECT token FROM mduka_users  WHERE mobile = ? ");
        $stmt->bind_param("s",$email);
        $stmt->execute(); 
        $result = $stmt->get_result()->fetch_assoc();
        return array($result['token']);        
    }
 
    //getting all the registered devices from database 
    public function getAllDevices(){
        $stmt = $this->conn->prepare("SELECT * FROM mduka_users");
        $stmt->execute();
        $result = $stmt->get_result();
        return $result; 
    }


   // public function updateUser($name,$email,$mobile,$zone,$apikey){
     //   $stmt = $this->conn->prepare("UPDATE odijo_tutor set name = ?, email = ?, mobile = ?, zone = ?  WHERE apikey = ?");
     //   $stmt->bind_param("sssss",$name,$email,$mobile,$zone, $apikey);

       // $stmt->execute();

    //    $result = $stmt->execute();
//
    //    if( $result){


   //     return USER_UPDATED_SUCCESSFULLY;
   //      } else {
       // Failed to create user
  //        return USER_UPDATE_FAILED;
  //        }

 //   }
    
    
    
      public function updateUser($name,$mobile,$shopname,$image,$shopemail,$shopphone){
        $stmt = $this->conn->prepare("UPDATE mduka_users set `name` = ?,  `image` = ?, `shopname` = ?, `shopemail` = ?,`shopphone` = ?  WHERE mobile = ?");
        $stmt->bind_param("ssssss",$name,$image,$shopname,$shopemail,$shopphone, $mobile);

       // $stmt->execute();

        $result = $stmt->execute();

        if( $result){


        return USER_UPDATED_SUCCESSFULLY;
         } else {
       // Failed to create user
          return USER_UPDATE_FAILED;
          }

    }
    
    


    /**
     * Generating random Unique MD5 String for user Api key
     */
    private function generateApiKey() {
        return md5(uniqid(rand(), true));
    }
}
?>