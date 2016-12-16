<!DOCTYPE html>
<head>
	<title>New Discussion</title>
	<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans" />
	<link rel="stylesheet" type="text/css" href="<?php echo $APPROOT; ?>/css/default.css">
	<script src="<?php echo $APPROOT; ?>/js/jquery-3.1.0.min.js"></script>
	<script src="<?php echo $APPROOT; ?>/js/tinymce/tinymce.min.js"></script>
	<script>
		
	</script>
	<style>
		#form_panel{
			width: 500px;
			margin: 0 auto;
			margin-top: 50px;
			padding: 15px;
			border: 1px solid #ccc;
		}
	</style>
</head>

<body>

	<?php echo $this->render('app/views/header.php',$this->mime,get_defined_vars(),0); ?>
	
	<div id="form_panel">
	
		<h2>New Discussion</h2>
		
		<form id="new_form" method='post' action='<?php echo $APPROOT; ?>/build_grouping/none'>
			
			<?php echo $this->render('app/views/discussionformcommon.php',$this->mime,get_defined_vars(),0); ?>
			
			<br><input type="submit" value="Create">
		
		</form>
	
	</div>
	
	<script>
		tinymce.init({ selector:'textarea' });
	</script>

</body>

</html>