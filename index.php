<?php
// ovhmail-password - Manage your OVH mails
// Licence: http://www.opensource.org/licenses/zlib-license.php
// Requires: php 5

require_once(dirname(__FILE__).'/config.php');
if($method == "apiv6") {
	require __DIR__ . '/vendor/autoload.php';
}

$errors = array();
$passwordmail = '';
$email_name = '';
$email = '';
$need_login = false;
if (!empty($_POST) && isset($_POST['passwordmail']) && isset($_POST['email'])) {
	$passwordmail = $_POST['passwordmail'] ;
	$email_name = $_POST['email'];
} else {
	$need_login = true;
}
$email = $email_name . $dom;
$mbox = @imap_open('{'.$serveur.':143}INBOX', "$email", "$passwordmail");
if (!$mbox) {
	if (!empty($_POST)) {
		$errors[] = "Authentication error";//.imap_last_error();
	}
	$need_login = true;
} else {
	imap_close($mbox);
	// Filtrage des données
	foreach ($_REQUEST as $key => $val) {
		$val = preg_replace("/[\'\"\\\?\~]/i",'', $val);
		$_REQUEST[$key] = $val;
	}

	$newpass = '';
	$newpass2 = '';
	if (isset($_POST['newpass'])) {
		$newpass = preg_replace("/[[\'\"\\\?\~]/i",'', $_POST["newpass"]);
	}
	if (isset($_POST['newpass2'])) {
		$newpass2 = preg_replace("/[[\'\"\\\?\~]/i",'', $_POST["newpass2"]);
	}

	$success = '';
	$minLength = 0;
	$maxLength = 0;
	if($method == "apiv6" || $method == "soapi") {
		$minLength = $passwordLength[$method]["min"];
		$maxLength = $passwordLength[$method]["max"];
	} else {
		$errors[] = "The configuration is incorrect. Please contact your administrator.";
	}
	if (empty($errors) && strlen($newpass) >= $minLength && strlen($newpass) <= $maxLength && $newpass == $newpass2 && $_POST["newpass"]==$newpass) {
	// Vérification du bon nouveau mot de passe (avec les deux champs puis on valide si ok )
		$successMsg = "<h3>Your password is being changed. The operation will take effect in 5 to 10 minutes.</h3>";
		switch($method) {
			case "soapi":
				$soap = new SoapClient('https://www.ovh.com/soapi/soapi-1.2.wsdl');

				//login
				try {
					$language = null;
					$multisession = false;
					$session = $soap->login($nic,$pass,$language,$multisession);
				} catch(SoapFault $fault) {
					$errors[] = "Error : login";//.$fault;
				}
				//popModifyPassword
				try {
					$result = $soap->popModifyPassword($session, $domain, $email_name, $newpass, false);
					$success = $successMsg;
				} catch(SoapFault $fault) {
					$errors[] = "Error : popModifyPassword";//.$fault;
				}
				//logout
				try {
					$result = $soap->logout($session);

				} catch(SoapFault $fault) {
					$errors[] = "Error : logout";//.$fault;
				}
				break;
			case "apiv6":
				try {
					$ovh = new \Ovh\Api( $appKey, $appSecret, $apiEndpoint, $consumerKey);
					$result = $ovh->post("/email/domain/$domain/account/$email_name/changePassword", array(
					    'password' => $newpass
					));
					$success = $successMsg;
				} catch(Exception $fault) {
					$errors[] = "An error occured during passwor change attempt.";//.$fault;
				}
				break;
		}
	} elseif (strlen($newpass) > 0 && $newpass != $newpass2) {
	// ici le cas ou le premier nouveau mot de passe ne correspond pas au second
		$errors[] = "The two passwords are not equal, please check it";
	} elseif (strlen($newpass) > 0 && (strlen($newpass) < $minLength || strlen($newpass) > $maxLength)) {
	// Si le mot de passe fait moins de x caractères ou plus de y caractères on refuse
		$errors[] = "Make sure your password length is between $minLength and $maxLength characters.";
	} elseif ($_POST["newpass"]!=$newpass) {
	// Si le mot de passe contient des caractères invalides on refuse
		$errors[] = "Password contains one or more of invalid characters:<ul><li>\'</li><li>\"</li><li>\\</li><li>\?</li><li>\~</li></ul>";
	}
}

$error_text = '';
if (!empty($errors)) {
	$error_text .= '<ul class="error-list">';
	foreach($errors as $err) {
		$error_text .= '<li>'.$err.'</li>';
	}
	$error_text .= '</ul>';
}

if ($need_login) {
	$title = "Welcome on OVH email management";
} else {
	$title = "Change the password of your email account";
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
	<link rel="stylesheet" type="text/css" title="normal" href="common.css">
	<title>Change the password of your email account</title>
	<meta content="text/html; charset=UTF-8" http-equiv="content-type">
</head>
<body>
<img id="logo" border="0" style="margin:0 11px" alt="OVH Mail" src="logo.jpg">
<div id="login-form">
<div class="boxtitle"><?php echo $title; ?></div>
	<div class="boxcontent">
<?php echo $error_text; ?>

<?php
if ($need_login) { ?>
		<form name="form" action="index.php" method="post">
			<table summary="" border="0">
			<tbody><tr><td class="title">
			<label for="email">Your email</label>
			</td>
			<td><input name="email" id="email" type="text" value="" />@<?php echo $domain; ?></td>
			</tr>
			<tr><td class="title"><label for="password">Your current password</label>
			</td>
			<td><input name="passwordmail" id="password" type="password" /></td>
			</tr>
			</tbody>
			</table>

			<p style="text-align:center;"><input type="submit" class="button mainaction" value="Authentication" /></p>
		</form>

<?php
} elseif (strlen($success) > 0 && empty($errors)) {
	print $success;
} else { ?>

		<form name="form "action="index.php" method="post">
			<input type="hidden"  name="passwordmail"  value="<?php echo $passwordmail; ?>">
			<input type="hidden"  name="email"  value="<?php echo $email_name; ?>">

			<table summary="" border="0">
			<tbody>
			<tr><td colspan=2>
				Your account:  <?php echo $email; ?><br /><br />
			</td></tr>
			<tr><td class="title">
			<label for="newpass">Your new password</label>
			</td>
			<td><input name="newpass" id="newpass" type="password" /></td>
			</tr>
			<tr><td class="title">
			<label for="newpass2">Confirm new password</label>
			</td>
			<td><input name="newpass2" id="newpass2" type="password" /></td>
			</tr>
			</tbody>
			</table>

			<p style="text-align:center;"><input type="submit" class="button mainaction" value="Send" /></p>
		</form>
<?php }
?>
	</div>
</div>
</body>
</html>
