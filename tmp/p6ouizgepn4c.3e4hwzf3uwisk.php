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
	
		<h2>
			<?php if ($mode=='edit'): ?>
				Edit Discussion
				<?php else: ?>New Discussion
			<?php endif; ?>
		</h2>
		
		<form id="new_form" method='post' 
			<?php if ($mode=='edit'): ?>
				 action='<?php echo $APPROOT; ?>/discussion/update/<?php echo $forumData['0']['fid']; ?>' 
				<?php else: ?>	action='<?php echo $APPROOT; ?>/build_grouping/silo' 
			<?php endif; ?>
		>
			
			<?php echo $this->render('app/views/discussionformcommon.php',$this->mime,get_defined_vars(),0); ?>
			
			<input type="hidden" name="grouping" value="silo">
			
			<label for="min">Minimum group size</label>
			<input name="min" type="text" 
				<?php if ($mode=='edit'): ?>
					 value="<?php echo $groupingData['0']['min']; ?>" 
				<?php endif; ?>
			><br><br>
			
			<label for="max">Maximum group size</label>
			<input name="max" type="text" 
				<?php if ($mode=='edit'): ?>
					 value="<?php echo $groupingData['0']['max']; ?>" 
				<?php endif; ?>
			><br><br>
			
			<?php if ($mode=='edit'): ?>
				 <br><input type="submit" value="Update"> 
				<?php else: ?>	<br><input type="submit" value="Create"> 
			<?php endif; ?>
		
		</form>
	
	</div>
	
	<script>
		tinymce.init({ selector:'textarea' });
	</script>

</body>

</html>