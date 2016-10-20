# ovhmail-password

This is a small script to setup on your OVH shared hosting server to manage email accounts passwords.

You have the choice between two methods:
* [soapi](https://www.ovh.com/soapi) (deprecated)
* [apiv6](https://api.ovh.com/): you will have to setup the access keys to the API [here](https://api.ovh.com/g934.first_step_with_api) or [here](https://api.ovh.com/createToken/index.cgi?).

*IMPORTANT*: We strongly recommend you make this script available through an HTTPS connection. Otherwise the passwords would travel in clear over the network.

## Installation

* Create a config.php file. Copy the config_sample.php file to config.php and edit the file to set your parameters.

Example configuration file with apiv6 method :

```php
<?php
...
$domain = ""; // your domain name
$method = "apiv6";
$appKey = "****************"; // your application key
$appSecret = "********************************"; // your application secret key
$consumerKey = "********************************"; // your consumer key
$apiEndpoint = "ovh-eu";
...
?>
```

* If you use the recommended apiv6 method, get the [php-ovh](https://github.com/ovh/php-ovh/releases) wrapper for OVH APIs. Unpack the  archive content along this project files.

* Copy all the files in a folder of your shared hosting server. For example :

```
mail/common.css
mail/config.php
mail/config_sample.php
mail/index.php
mail/logo.jpg
mail/README.md
(+ php-ovh resources if using apiv6 method)
```

* Surf with your favorite browser to yourdomain.tld/mail

That's it ! Have fun
