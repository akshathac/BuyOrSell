<?php
/**
 * Created by PhpStorm.
 * User: akshatha
 * Date: 12/3/2016
 * Time: 7:45 PM
 */
date_default_timezone_set('UTC');
session_start();
$_SESSION['auth'] = "true";

extract($_POST);
extract($_GET);
$host = "mydbinstance.cmsecos5l0vz.us-east-1.rds.amazonaws.com:3306";
$dbname = "ShoppingCart";
$dsn = "mysql:host=$host;dbname=$dbname";
$username = 'Aksh30';
$password = 'Krishna30';
$dbh = new PDO($dsn, $username, $password);

function generate_alphnumeric_code(){
    $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
    $string = '';
    $max = strlen($characters) - 1;
    for ($i = 0; $i < 10; $i++) {
        $string .= $characters[mt_rand(0, $max)];
    }
    return $string;
}
function send_confirmation_email($email,$authorization){
    $message = "Please click the below link to confirm. <br/> <h4> <a href='http://shoppingcart-project.us-east-1.elasticbeanstalk.com/php/confirmation.php?code=$authorization'>Click Here</a> </h4> ";
// In case any of our lines are larger than 70 characters, we should use wordwrap()
    $message = wordwrap($message, 70, "\r\n");
// Send
    $headers = 'From: webmaster@example.com' . "\r\n" .
        'Reply-To: webmaster@example.com' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();
    mail($email, 'Confirmation email', $message,$headers);
    send_signup_email($email);
}

function send_signup_email($email){
    $message = "New user signed up with $email address";
// In case any of our lines are larger than 70 characters, we should use wordwrap()
    $message = wordwrap($message, 70, "\r\n");
// Send
    $headers = 'From: webmaster@example.com' . "\r\n" .
        'Reply-To: webmaster@example.com' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();
    mail("akshathac91+10@gmail.com", 'Confirmation email', $message,$headers);
}
function check_login(){
    if(!isset($_SESSION['USER_ID'])) {
        if(!empty($_GET))
        echo json_encode(array("success" => false, "result" => "authentication failed"));
        else header("Location:http://shoppingcart-project.us-east-1.elasticbeanstalk.com/login.html");
        exit;
    }else{
        $tableName = 'user';
        $sql = "Select * from $tableName where id = ".$_SESSION['USER_ID'];
        $stmt = $dbh->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        if(empty($result)){
            header("Location:http://shoppingcart-project.us-east-1.elasticbeanstalk.com/login.html");
        }
    }
}
if(isset($registration_form) && $registration_form == 'true') {
    $authorization = generate_alphnumeric_code();
    $tableName = 'user';
    $sql = "INSERT INTO $tableName (fullName,username,password,address,phone,authorization_code) VALUES (:fullName,:username,:password,:address,:phone,:authorization_code)";
    $stmt = $dbh->prepare($sql);
    $stmt->execute(array(
        'username'=> $email,
        'fullName' => $firstName,
        'password'=> md5($password),
        'address'=>$address,
        'phone'=>$phone,
        'authorization_code'=>$authorization));

    send_confirmation_email($email,$authorization);
    header("Location: http://shoppingcart-project.us-east-1.elasticbeanstalk.com/login.html");
    exit;

}elseif(isset($login_form) && $login_form == 'true'){
    if (isset($login_email) && isset($login_password) ) {
        $name = strip_tags(trim($login_email));
        $password = strip_tags(trim($login_password));
        $stmt = $dbh->prepare("SELECT * FROM user where username = :email and password = :password and status = :status");
        $stmt->execute(array('email' => $name,'password'=>md5($password),'status'=>'Active'));
        $result = $stmt->fetchAll();
        if(empty($result)){
            echo "<h3>Authentication failed</h3>";
            header("Location: http://shoppingcart-project.us-east-1.elasticbeanstalk.com/login.html");
            exit;
        }else{
            $_SESSION['USER_ID'] = $result[0]['id'];
            header("Location: http://shoppingcart-project.us-east-1.elasticbeanstalk.com/index.html"); /* Redirect browser */
            exit();
        }

        }
}
check_login();
if(isset($product_description) && $product_description == 'true') {
    $tableName = 'sellingItemList';
    $stmt = $dbh->prepare("INSERT INTO $tableName (user_id,category,title,description, image_path,cost) VALUES (:user_id,:category,:title,:description,:image_path,:cost)");
    $stmt->execute(array(
    'user_id'=> $_SESSION['USER_ID'],
    'category'=>$category,
    'title'=>$title,
    'description'=>$description,
    'image_path'=>$file_name,
    'cost'=>$price)
    );
       header("Location: http://shoppingcart-project.us-east-1.elasticbeanstalk.com/index.html"); /* Redirect browser */
       exit();
}
elseif(isset($action) && $action=="search_by_category"){
    $tableName = 'sellingItemList';
    $stmt = $dbh->prepare("SELECT * from $tableName where category = :category");
    $stmt->execute(array('category' => "$category_name"));
    $result = $stmt->fetchAll();
    echo json_encode(array("success"=>true,"result"=>$result));
}
elseif(isset($action) && $action=="user_activity"){
    $tableName = 'sellingItemList';
    $stmt = $dbh->prepare("SELECT * from $tableName where user_id = :user_id");
    $stmt->execute(array('user_id' => $_SESSION['USER_ID']));
    $result = $stmt->fetchAll();
    echo json_encode(array("success"=>true,"result"=>$result));
}elseif(isset($action) && $action=="add_to_cart"){
    $tableName = 'cart';
    $stmt = $dbh->prepare("INSERT INTO $tableName (user_id,product_id) VALUES (:user_id,:product_id)");
    $stmt->execute(array(
        'user_id'=> $_SESSION['USER_ID'],
        'product_id'=>$product_id)
    );
    echo json_encode(array("success" => true));
}elseif(isset($action) && $action=="get_cart_items") {
    $tableName = 'sellingItemList';
    $result = array();
    //Get all product added to cart
    $stmt = $dbh->prepare("SELECT * from cart where user_id =".$_SESSION['USER_ID']);
    $stmt->execute();
    $returned_arr = $stmt->fetchAll();
    foreach($returned_arr as $product_id_arr){
        array_push($result,$product_id_arr['product_id']);
    }
    $product_id = implode(",",$result);
    $stmt = $dbh->prepare("SELECT * from $tableName where product_id in (".$product_id.")");
    $stmt->execute();
    $result = $stmt->fetchAll();
    echo json_encode(array("success" => true, "result" => $result));
}elseif(isset($action) && $action=="remove_from_cart") {
    $tableName = 'cart';
    $stmt = $dbh->prepare("Delete from $tableName where user_id = :user_id and product_id = :product_id");
    $stmt->execute(array(
            'user_id' => $_SESSION['USER_ID'],
            'product_id' => $product_id)
    );
    $tableName = 'sellingItemList';
    $result = array();
    //Get all product added to cart
    $stmt = $dbh->prepare("SELECT * from cart where user_id = :user_id");
    $stmt->execute(array('user_id' => $_SESSION['USER_ID']));
    $returned_arr = $stmt->fetchAll();
    foreach($returned_arr as $product_id_arr){
        array_push($result,$product_id_arr['product_id']);
    }
    $table_product_id = implode(",",$result);
    $stmt = $dbh->prepare("SELECT * from $tableName where product_id in (".$table_product_id.")");
    $stmt->execute();
    $result = $stmt->fetchAll();
    echo json_encode(array("success" => true, "result" => $result));
}
elseif(isset($action) && $action=="remove_from_sellinglist") {
    $tableName = 'sellingItemList';
    $stmt = $dbh->prepare("Delete from $tableName where user_id = :user_id and product_id = :product_id");
    $stmt->execute(array(
            'user_id' => $_SESSION['USER_ID'],
            'product_id' => $product_id)
    );
    $tableName = 'sellingItemList';
    $stmt = $dbh->prepare("SELECT * from $tableName where user_id = :user_id");
    $stmt->execute(array('user_id' => $_SESSION['USER_ID']));
    $result = $stmt->fetchAll();
    echo json_encode(array("success" => true, "result" => $result));
}
elseif(isset($action) && $action == "clear_the_session"){
    session_unset();
}
elseif(isset($action) && $action=="search_by_value"){
    $tableName = 'sellingItemList';
    $stmt = $dbh->prepare("SELECT * from $tableName where title like :category");
    $stmt->execute(array('category' => "%$product_name%"));
    $result = $stmt->fetchAll();
    echo json_encode(array("success"=>true,"result"=>$result));
}else{
    header("Location:http://shoppingcart-project.us-east-1.elasticbeanstalk.com/login.html");
    exit();
}
?>