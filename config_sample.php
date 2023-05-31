<?php
// To generate Keys & secret, got to
// Needed rights:
// - GET  /email/domain/${domain}/account/*
// - POST /email/domain/${domain}/account/*/changePassword

$applicationKey    = 'xxxxx';  // Application Key
$applicationSecret = 'xxxxxx'; // Application Secret
$consumerKey       = 'xxxxxx'; // Consumer Key
$ovhEndpoint = 'ovh-eu';      // Endpoint of API OVH Europe (List of available endpoints)
$domain = "yourdomaine.tld"; // your domain name
$serveur="SSL0.OVH.NET"; 
$dom = "@". $domain;
