<!DOCTYPE html>
<head>
	<title>Bad times</title>
</head>

<body>

	<?php if ($error): ?>
		
			<div>Error: <?php echo $error; ?></div>
		
		<?php else: ?>
			<div>No error reported.</div>
		
	<?php endif; ?>

</body>

</html>