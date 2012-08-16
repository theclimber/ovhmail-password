<?php
require_once(dirname(__FILE__).'/config.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<title>Login to change email password</title>
<body>
<h3>Login to change email password</h3>
<form action="modmail.php" method="post">
Your email : <input type="text" name="email">@<?php echo $domain; ?><br />
Your current password : <input type="password" name="passwordmail" /><br />
 <input type="submit" value="Valider" />
</form>
</body>
</html>

