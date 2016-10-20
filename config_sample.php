<?php
// common parameters
$domain = ""; // your domain name
$method = "apiv6"; // apiv6 or soapi(not recommended anymore)

// soapi parameters
$nic = "********"; // your OVH nic-handle
$pass = "*********"; // your OVH password

// apiv6 parameters (see https://api.ovh.com)
$appKey = "****************"; // your application key
$appSecret = "********************************"; // your application secret key
$consumerKey = "********************************"; // your consumer key
$apiEndpoint = "ovh-eu"; // see https://github.com/ovh/php-ovh/#supported-apis

// other
$serveur="SSL0.OVH.NET";
$dom = "@". $domain;
$passwordLength = array (
  "soapi" => array(
    "min" => 6,
    "max" => 12
  ),
  "apiv6" => array(
    "min" => 9,
    "max" => 30
  )
);
?>
