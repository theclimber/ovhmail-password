<?php
require_once(dirname(__FILE__).'/config.php');

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
		$errors[] = "Authentication error";
	}
	$need_login = true;
} else {
	imap_close($mbox);
	// Filtrage des données

	foreach ($_REQUEST as $key => $val) {
		$val = preg_replace("/[^_A-Za-z0-9-\.&=]/i",'', $val);
		$_REQUEST[$key] = $val;
	}

	$newpass = '';
	$newpass2 = '';
	if (isset($_POST['newpass'])) {
		$newpass = preg_replace("/[^_A-Za-z0-9-\.]/i",'', $_POST["newpass"]);
	}
	if (isset($_POST['newpass2'])) {
		$newpass2 = preg_replace("/[^_A-Za-z0-9-\.]/i",'', $_POST["newpass2"]);
	}

	$success = '';
	if (strlen($newpass) >= 8 && $newpass == $newpass2) {
	// Vérification du bon nouveau mot de passe (avec les deux champs puis on valide si ok )
		$soap = new SoapClient('https://www.ovh.com/soapi/soapi-1.2.wsdl');

		//login
		try {
			$language = null;
			$multisession = false;
			$session = $soap->login($nic,$pass,$language,$multisession);
			$success .= "login successfull<br/>";
		} catch(SoapFault $fault) {
			$errors[] = "Error : ".$fault;
		}
		//popModifyPassword
		try {
			$result = $soap->popModifyPassword($session, $domain, $email_name, $newpass, false);
			$success .= "popModifyPassword successfull<br/>";
			$success .= print_r($result);
			$success .= "<br/>";
			$success .= "<h3>Merci.<br />Mot de passe modifi&eacute;.</h3>";
			$success .= "<h3>Il sera pris en compte d'ici une quinzaine de minutes</h3>";
		} catch(SoapFault $fault) {
			$errors[] = "Error : ".$fault;
		}
		//logout
		try {
			$result = $soap->logout($session);
			$success .= "logout successfull<br/>";
		} catch(SoapFault $fault) {
			$errors[] = "Error : ".$fault;
		}
	} elseif (strlen($newpass) > 0 && $newpass != $newpass2) {
	// ici le cas ou le premier nouveau mot de passe ne correspond pas au second
		$errors[] = "The two passwords are not equal, please check it";
	} elseif (strlen($newpass) > 0 && strlen($newpass) < 8) {
	// Si le mot de passe fait moins de 8 caractères on refuse 
		$errors[] = "Make sure your password has minimum 8 characters.";
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
