<?php
// ovhmail-password - Manage your OVH mails
// Licence: http://www.opensource.org/licenses/zlib-license.php
// Requires: php 5
 
require_once(__DIR__.'/config.php');
 
error_reporting(DEBUG);
 
$need_login = true;
$form_error = array();
 
function add_field_error($name, $message)
{
    global $form_error;
 
    if (is_array($name)) {
        $name = 'global_error_'.implode('_', $name);
    }
 
    if (!isset($form_error[$name])) {
        $form_error[$name] = array();
    }
 
    $form_error[$name][] = $message;
}
 
function has_field_error($name)
{
    global $form_error;
 
    return !empty($form_error[$name]);
}
 
function get_field_error($name)
{
    global $form_error;
 
    return has_field_error($name) ? $form_error[$name] : array();
}
 
function get_field_errors($name)
{
    return get_field_error($name);
}
 
function is_valid_form()
{
    global $form_error;
 
    return empty($form_error);
}
 
function valid_field($name, Closure $validator, $message, $http_method = 'POST')
{
    $datas = $http_method == 'POST' ? $_POST : $_GET;
 
    $value = isset($datas[$name]) ? $datas[$name] : null;
 
    if (false === $validator($value)) {
        add_field_error($name, $message);
    }
}
 
function valid_fields(array $fields, Closure $validator, $message, $http_method = 'POST')
{
    $datas = $http_method == 'POST' ? $_POST : $_GET;
    $param = array();
 
    foreach ($fields as $name) {
        $param[$name] = isset($datas[$name]) ? $datas[$name] : null;
    }
 
    if (false === $validator($param)) {
        add_field_error($fields, $message);
    }
}
 
function is_global_error($name)
{
    return preg_match('/^global_error_/', $name);
}
 
function print_field_error($name)
{
    if (has_field_error($name)) {
        echo '<ul class="error">';
        foreach (get_field_error($name) as $error) {
            echo sprintf('<li>%s</li>', htmlspecialchars($error));
        }
        echo '</ul>';
    }
}
 
function print_global_errors()
{
    global $form_error;
 
    $datas = array();
 
    foreach ($form_error as $k => $v) {
        if (is_global_error($k)) {
            $datas[] = $v;
        }
    }
 
    if (!empty($v)) {
        echo '<ul class="error">';
        foreach ($datas as $error) {
            echo sprintf('<li>%s</li>', htmlspecialchars($error[0]));
        }
        echo '</ul>';
 
    }
}
 
function filter_var_password($password)
{
    return preg_replace('/[^_A-Za-z0-9-\.&=]/i', '', $password);
}
 
if (!empty($_POST)) {
    $need_login = false;
 
    valid_field(
        'email',
        function($email) use ($dom) {
            return filter_var($email.$dom, FILTER_VALIDATE_EMAIL);
        },
        'Vous devez indiquer un email valide.'
    );
 
    valid_field(
        'passwordmail',
        function($passwordmail) {
            return trim($passwordmail) !== '';
        },
        'Vous devez indiquer un mot de passe.'
    );
 
    if (is_valid_form()) {
        valid_fields(
            array('email', 'passwordmail'),
            function($datas) use ($dom, $serveur) {
                $datas['email'].= $dom;
 
                $imap_connection = @imap_open('{'.$serveur.':143}INBOX', $datas['email'], $datas['passwordmail']);
 
                if ($imap_connection) {
                    imap_close($imap_connection);
 
                    return true;
                }
 
                return false;
            },
            'Les identifiants ne sont pas corrects.'
        );
 
        if (is_valid_form()) {
            $form_error = array();
 
            valid_field(
                'newpass',
                function($newpass) {
                    $newpass = filter_var_password($newpass);
 
                    return strlen($newpass) >= 8;
                },
                'Vous devez indiquer un mot de passe de 8 caractères minimum.'
            );
 
            valid_fields(
                array('newpass', 'newpass2'),
                function($datas) {
                    return $datas['newpass'] === $datas['newpass2'];
                },
                'Les mots de passe doivent être identiques.'
            );
 
            if (is_valid_form()) {
                try {
                    $session = $soap->login($nic, $pass, $language = null, $multisession = false);
                    $soap->popModifyPassword($session, $domain, $_POST['email'], filter_var_password($_POST['newpass']), false);
                    $soap->logout($session);
                } catch (SoapFault $fault) {
                    $error = 'Echec : '.$fault->getMessage();
                }
            }
        } else {
            $need_login = true;
        }
    } else {
        $need_login = true;
    }
}
 
$title = $need_login ? 'Welcome on OVH email management' : 'Change the password of your email account';
 
$passwordmail = isset($_POST['passwordmail']) ? htmlspecialchars($_POST['passwordmail']) : '';
$email        = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '';
$newpass      = isset($_POST['newpass']) ? htmlspecialchars($_POST['newpass']) : '';
$newpass2     = isset($_POST['newpass2']) ? htmlspecialchars($_POST['newpass2']) : '';
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
 
<?php if(!empty($error)): ?>
    <p><?php echo htmlspecialchars($error); ?>
<?php endif; ?>
 
<?php print_global_errors(); ?>
 
<?php if ($need_login): ?>
    <form name="form" action="index.php" method="post">
        <table summary="" border="0">
            <tbody>
                <tr>
                    <td class="title">
                        <label for="email">Your email</label>
                    </td>
                    <td>
                        <input name="email" id="email" type="text" value="<?php echo $email; ?>" />@<?php echo $domain; ?>
                        <?php print_field_error('email'); ?>
                    </td>
                </tr>
                <tr>
                    <td class="title">
                        <label for="password">Your current password</label>
                    </td>
                    <td>
                        <input name="passwordmail" id="password" value="<?php echo $passwordmail; ?>" type="password" />
                        <?php print_field_error('passwordmail'); ?>
                    </td>
                </tr>
            </tbody>
        </table>
 
        <p style="text-align:center;"><input type="submit" class="button mainaction" value="Authentication" /></p>
 
    </form>
<?php else: ?>
    <form name="form "action="index.php" method="post">
        <input type="hidden"  name="passwordmail"  value="<?php echo $passwordmail; ?>">
        <input type="hidden"  name="email"  value="<?php echo $email_name; ?>">
 
        <table summary="" border="0">
            <tbody>
                <tr>
                    <td colspan=2>
                        Your account:  <?php echo $email; ?><br /><br />
                    </td>
                </tr>
                <tr>
                    <td class="title">
                        <label for="newpass">Your new password</label>
                    </td>
                    <td>
                        <input name="newpass" id="newpass" type="password" value="<?php echo $newpass; ?>" /></td>
                </tr>
                <tr>
                    <td class="title">
                        <label for="newpass2">Confirm new password</label>
                    </td>
                    <td>
                        <input name="newpass2" id="newpass2" type="password" value="<?php echo $newpass2; ?>" />
                    </td>
                </tr>
            </tbody>
        </table>
 
        <p style="text-align:center;"><input type="submit" class="button mainaction" value="Send" /></p>
    </form>
<?php endif; ?>
    </div>
</div>
</body>
</html>
