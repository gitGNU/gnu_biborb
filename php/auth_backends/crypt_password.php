<html>
<body>
<form action='crypt_password.php' method='post'>
<label>Enter your password: </label><input type='text' name='password'/><input type='submit' value='Crypt!'/>
</form>
<?php
if(isset($_POST['password'])){
    echo "Encrypted password: ".crypt($_POST['password']);
}
?>
</body>
</html>