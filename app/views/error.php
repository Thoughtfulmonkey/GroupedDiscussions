<!DOCTYPE html>
<head>
	<title>Bad times</title>
</head>

<body>

	<check if="{{ @error }}">
		<true>
			<div>Error: {{ @error }}</div>
		</true>
		<false>
			<div>No error reported.</div>
		</false>
	</check>

</body>

</html>