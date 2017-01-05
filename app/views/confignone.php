<!DOCTYPE html>
<head>
	<title>New Discussion</title>
	<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans" />
	<link rel="stylesheet" type="text/css" href="{{ @APPROOT }}/css/default.css">
	<script src="{{ @APPROOT }}/js/jquery-3.1.0.min.js"></script>
	<script src="{{ @APPROOT }}/js/tinymce/tinymce.min.js"></script>
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

	<include href="app/views/header.php" />
	
	<div id="form_panel">
	
		<h2>
			<check if="{{ @mode=='edit' }}">
				<true>Edit Discussion</true>
				<false>New Discussion</false>
			</check>
		</h2>
		
		<form id="new_form" method='post' 
			<check if="{{ @mode=='edit' }}">
				<true> action='{{ @APPROOT }}/discussion/update/{{ @forumData[0].publicId }}' </true>
				<false>	action='{{ @APPROOT }}/build_grouping/none' </false>
			</check>
		>
			
			<include href="app/views/discussionformcommon.php" />
			
			<br>
			
			<check if="{{ @mode=='edit' }}">
				<true> <br><input type="submit" value="Update"> </true>
				<false>	<br><input type="submit" value="Create"> </false>
			</check>
		
		</form>
	
	</div>
	
	<script>
		tinymce.init({ selector:'textarea' });
	</script>

</body>

</html>