<!DOCTYPE html>
<head>
	<title>Forum</title>
	<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans" />
	<link rel="stylesheet" type="text/css" href="{{ @APPROOT }}/css/default.css">
	<script src="{{ @APPROOT }}/js/jquery-3.1.0.min.js"></script>
	<script src="{{ @APPROOT }}/js/tinymce/tinymce.min.js"></script>
	<script>
	
	</script>
	<style>
		
	</style>
</head>

<body>

	<include href="app/views/header.php" />

	<div id="form_panel">
	
		<h2>Join the Discussion</h2>
	
		<p>{{ @prompt|raw }}</p>
	
		<form id="new_form" method='post'>
	
			<select name="promptChoice" class="big_select">
			<repeat group="{{ @options }}" value="{{ @option }}">
				<option value="{{ @option }}">{{ @option }}</option>
			</repeat>
			</select>
		
			<br><br><input type="submit" value="Choose">
	
		</form>
	
	</div>
	
</body>

</html>