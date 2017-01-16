<?php
/**
 * Created by PhpStorm.
 * User: akshatha
 * Date: 12/5/2016
 * Time: 11:48 PM
 */
require 'C:/Users/akshatha/PhpstormProjects/BuyOrSell/aws/aws-autoloader.php';
$file_name = $_FILES["file_upload"]["name"];
$ext = pathinfo($file_name, PATHINFO_EXTENSION);
$new_file_name = md5(uniqid(rand(), true)).".".$ext;

use Aws\S3\S3Client;

$bucket = //Bucket name;
$keyname = $file_name;
// $filepath should be absolute path to a file on disk
$filepath = $_FILES["file_upload"]["tmp_name"];
$content_type = $_FILES['file_upload']['type'];

// Instantiate the client.
$s3 = S3Client::factory(array(
    'region'  => 'us-east-1',
    'version' => 'latest',
    'credentials' => [
    'key'    => //AWS Key,
    'secret' => //AWS Secret key,
],
        'http'    => [
            'verify' => 'C:\Users\akshatha\PhpstormProjects\BuyOrSell\cacert.pem'
        ])
);

// Upload a file.
$result = $s3->putObject(array(
    'Bucket'       => $bucket,
    'Key'          => $new_file_name,
    'SourceFile'   => $filepath,
    'ContentType'  => $content_type,
    'ACL'          => 'public-read',
    'StorageClass' => 'REDUCED_REDUNDANCY',
));

echo json_encode(array("result_link"=>urlencode($result['ObjectURL'])));
