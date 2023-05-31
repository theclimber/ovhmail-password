<?php
// ovhmail-password - Manage your OVH mails
// Licence: http://www.opensource.org/licenses/zlib-license.php
// Requires: php 5

require_once(dirname(__FILE__).'/config.php');

require __DIR__ . '/vendor/autoload.php';
use \Ovh\Api;

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
	if (strlen($newpass) >= 8 && $newpass == $newpass2 && $_POST["newpass"]==$newpass) {
	// Vérification du bon nouveau mot de passe (avec les deux champs puis on valide si ok )
        $ovh = new Api( $applicationKey,  // Application Key
                        $applicationSecret,  // Application Secret
                        'ovh-eu',      // Endpoint of API OVH Europe (List of available endpoints)
                        $consumerKey); // Consumer Key

        try {
            $result = $ovh->get("/email/domain/${domain}/account/${email_name}");
            if (!$result) {
                $errors[] = "Cannot access to account";
            }
            else {
                if ($result['isBlocked']) {
                    $errors[] = "Account is blocked";
                }
                else {
                    $result = $ovh->post("/email/domain/${domain}/account/${email_name}/changePassword", array(
                        'password' => $newpass
                    ));
                    if (!$result || !isset($result['id'])) {
                        $errors[] = "Error while creating change password task";
                    $errors[] = print_r($result, true);
                    }
                    else {
                        $success .= "<h3>Thank you.<br />Password has been modified.</h3>";
                        $success .= "<h3>The change will be visible maximally in 15 minutes.</h3>";
                    }
                }
            }
        } catch (Exception $ex) {
            $errors[] = "API Error: " . $ex->getMessage();
        }
	} elseif (strlen($newpass) > 0 && $newpass != $newpass2) {
	// ici le cas ou le premier nouveau mot de passe ne correspond pas au second
		$errors[] = "The two passwords are not equal, please check it";
	} elseif (strlen($newpass) > 0 && strlen($newpass) < 8) {
	// Si le mot de passe fait moins de 8 caractères on refuse 
		$errors[] = "Make sure your password has minimum 8 characters.";
	} elseif ($_POST["newpass"]!=$newpass) {
	// Si le mot de passe fait moins de 8 caractères on refuse 
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
