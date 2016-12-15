<!DOCTYPE html>
<head>
	<title>Login</title>
	<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans" />
	<link rel="stylesheet" type="text/css" href="{{ @APPROOT }}/css/default.css">
	<script src="{{ @APPROOT }}/js/jquery-3.1.0.min.js"></script>
</head>

<body>
	
	<check if="{{ @DEV }}">
		<include href="app/views/userchoose.php" />
	</check>

	<div id="login_panel">
		<check if="{{ @loginmessage }}">
			<div id="login_message">{{ @loginmessage }}</div>
		</check>

		<form method="POST" action="login">
			<label for="username">Username:</label>
			<input name="username" type="text"><br>
			<label for="password">Password:</label>
			<input name="password" type="password"><br>
			<input type="submit" value="Login">
		</form>
	</div>

</body>