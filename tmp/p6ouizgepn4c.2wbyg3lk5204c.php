<!DOCTYPE html>
<head>
	<title>Login</title>
	<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans" />
	<link rel="stylesheet" type="text/css" href="<?php echo $APPROOT; ?>/css/default.css">
	<script src="<?php echo $APPROOT; ?>/js/jquery-3.1.0.min.js"></script>
</head>

<body>
	
	<?php if ($DEV): ?>
		<?php echo $this->render('app/views/userchoose.php',$this->mime,get_defined_vars(),0); ?>
	<?php endif; ?>

	<div id="login_panel">
		<?php if ($loginmessage): ?>
			<div id="login_message"><?php echo $loginmessage; ?></div>
		<?php endif; ?>

		<form method="POST" action="login">
			<label for="username">Username:</label>
			<input name="username" type="text"><br>
			<label for="password">Password:</label>
			<input name="password" type="password"><br>
			<input type="submit" value="Login">
		</form>
	</div>

</body>