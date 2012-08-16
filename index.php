<?php
require_once(dirname(__FILE__).'/config.php');
?>
<html>
<title>Login to change email password</title>
<body>
<h3>Login to change email password</h3>
<form action="modmail.php" method="post">
Your email : <input type="text" name="email">@<?php echo $domain; ?><br />
Your current password : <input type="password" name="passwordmail" /><br />
 <input type="submit" value="Valider" />
</form>
</html></body>

