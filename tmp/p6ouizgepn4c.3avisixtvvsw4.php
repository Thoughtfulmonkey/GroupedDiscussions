<!DOCTYPE html>
<head>
	<title>New Discussion</title>
	<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans" />
	<link rel="stylesheet" type="text/css" href="<?php echo $APPROOT; ?>/css/default.css">
	<script src="<?php echo $APPROOT; ?>/js/jquery-3.1.0.min.js"></script>
	<script src="<?php echo $APPROOT; ?>/js/tinymce/tinymce.min.js"></script>
	<script>
		// Ajax call to load options on radio button choice
		$(document).ready( function(){
			
			$('.group_option').on("click", function(){
				
				var choice =  $(this).attr("value");
				
				// Call to retrieve options
				$.ajax({
					url: "<?php echo $APPROOT; ?>/grouping/params/"+choice, 
					success: function(result){
						
						// Wipe param panel
						$('#param_panel').empty();
						
						// Add inputs based on param list
						if ( result!="null" ){
							var json = JSON.parse(result);
							for (var i=0; i<json.params.length; i++){
								var name = json.params[i].name;
								var p = $('<label for="'+name+'">'+name+'</label><input name="'+name+'" type="text"><br><br>');
								$('#param_panel').append(p);
							}
						}
					}
				});
				
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

	<?php echo $this->render('app/views/header.php',$this->mime,get_defined_vars(),0); ?>

	<div id="form_panel">
	
		<h2>New Discussion</h2>
		
		<form method='post'>
			
			<label for="title">Title:</label>
			<input name="title" type="text"><br><br>
			
			<label for="prompt">Prompt:</label><br>
			<textarea name="prompt"></textarea><br>
			
			<label for="grouping">Grouping:</label><br>
			<?php foreach (($groupingoptions?:array()) as $grouping): ?>
				<input class="group_option" type="radio" name="grouping" value="<?php echo $grouping['name']; ?>"><?php echo $grouping['name']; ?><br>
			<?php endforeach; ?>
			
			<div id="param_panel">
			</div>
			
			<br><input type="submit" value="Create">
		
		</form>
	
	</div>
	
	<script>
		tinymce.init({ selector:'textarea' });
	</script>
	
</body>

</html>