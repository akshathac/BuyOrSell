<?php
/**
 * Created by PhpStorm.
 * User: akshatha
 * Date: 12/15/2016
 * Time: 9:58 PM
 */
$code = $_GET['code'];
$host = "mydbinstance.cmsecos5l0vz.us-east-1.rds.amazonaws.com:3306";
$dbname = "ShoppingCart";
$dsn = "mysql:host=$host;dbname=$dbname";
$username = 'Aksh30';
$password = 'Krishna30';
$dbh = new PDO($dsn, $username, $password);

$tableName = 'user';
$stmt = $dbh->prepare("SELECT * FROM user where authorization_code = :authorization_code");
$stmt->execute(array('authorization_code' => $code));
$result = $stmt->fetchAll();

if(!empty($result)){
    $id = $result[0]['id'];
    $stmt = $dbh->prepare("UPDATE user set status = :status where id = :id");
    $stmt->execute(array('id'=>$id,'status'=>'Active'));
    header("Location: login.html"); /* Redirect browser */
    exit();
}else{
    echo "<h3>ERROR: THIS LINK IS EXPIRED</h3>";
}

