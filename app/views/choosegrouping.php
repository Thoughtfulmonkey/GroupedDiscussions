<!DOCTYPE html>
<head>
	<title>New Discussion</title>
	<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans" />
	<link rel="stylesheet" type="text/css" href="{{ @APPROOT }}/css/default.css">
	<script src="{{ @APPROOT }}/js/jquery-3.1.0.min.js"></script>
	<script src="{{ @APPROOT }}/js/tinymce/tinymce.min.js"></script>
	<script>
		// Ajax call to load options on radio button choice
		$(document).ready( function(){
			
			$('.group_option').on("click", function(){
				
				var choice =  $(this).attr("value");
				
				// Update the submission URL
				$('#new_form').attr("action", "{{ @APPROOT }}/config_grouping/"+choice);
				
			});
			
			// Set first grouping option as active
			$('.group_option').first().attr("checked", "checked");
			
		});
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
	
		<h2>Choose a Grouping Method</h2>
		
		<form id="new_form" method='post' action='{{ @APPROOT }}/config_grouping/{{ @groupingoptions[0].name }}'>
			
			<repeat group="{{ @groupingoptions }}" value="{{ @grouping }}">
				<input class="group_option" type="radio" name="grouping" value="{{ @grouping.name }}">{{ @grouping.name }}<br>
			</repeat>
			
			<br><input type="submit" value="Create">
		
		</form>
	
	</div>
	
	<script>
		tinymce.init({ selector:'textarea' });
	</script>
	
</body>

</html>