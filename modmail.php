<?php
require_once(dirname(__FILE__).'/config.php');

$errors = array();
if (isset($_POST)) {
	$passwordmail = $_POST['passwordmail'] ;
	$email_name = $_POST['email'];
	$email = $email_name . $dom;
	$mbox = @imap_open('{'.$serveur.':143}INBOX', "$email", "$passwordmail");
	if (!$mbox) {
		echo "Authentication error<br /><a href='index.php'>Back</a>";
		exit;
	}
	imap_close($mbox);
} else {
	$errors[] = "Forbidden";
}

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

$error_text = '';
if (!empty($errors)) {
	$error_text .= '<ul class="error-list">';
	foreach($errors as $err) {
		$error_text .= '<li>'.$err.'</li>';
	}
	$error_text .= '</ul>';
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<title>Change the password of your email account</title>
<body>

<?php
if (strlen($success) > 0 && empty($errors)) {
	print $success;
} else { ?>

<h3>Change the password of your email account</h3>
<?php echo $error_text; ?>
<form action="modmail.php" method="post">
Your account:  <?php echo $email; ?><br /><br />
Your new password: <br />
<input type="password" name="newpass" size="30" maxlength="50" id="newpass" value="">
(minimum 8 characters)<br /><br />
Confirm new password: <br />
<input type="password" name="newpass2" size="30" maxlength="20" id="newpass2" value="">
<br /><br />
<input type="submit" value="Send" />
<input type="hidden"  name="passwordmail"  value="<?php echo $passwordmail; ?>">
<input type="hidden"  name="email"  value="<?php echo $email_name; ?>">
</form>
</body>
</html>

<?php }
?>
