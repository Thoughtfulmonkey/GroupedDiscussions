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
				
				// Update the submission URL
				$('#new_form').attr("action", "<?php echo $APPROOT; ?>/config_grouping/"+choice);
				
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
	
		<h2>Choose a Grouping Method</h2>
		
		<form id="new_form" method='post' action='<?php echo $APPROOT; ?>/config_grouping/<?php echo $groupingoptions['0']['name']; ?>'>
			
			<?php foreach (($groupingoptions?:array()) as $grouping): ?>
				<input class="group_option" type="radio" name="grouping" value="<?php echo $grouping['name']; ?>"><?php echo $grouping['name']; ?><br>
			<?php endforeach; ?>
			
			<br><input type="submit" value="Create">
		
		</form>
	
	</div>
	
	<script>
		tinymce.init({ selector:'textarea' });
	</script>
	
</body>

</html>