<!DOCTYPE html>
<head>
	<title>Forum</title>
	<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans" />
	<link rel="stylesheet" type="text/css" href="<?php echo $APPROOT; ?>/css/default.css">
	<script src="<?php echo $APPROOT; ?>/js/jquery-3.1.0.min.js"></script>
	<script src="<?php echo $APPROOT; ?>/js/tinymce/tinymce.min.js"></script>
	<script>
	
	</script>
</head>

<body>

	<?php echo $this->render('app/views/header.php',$this->mime,get_defined_vars(),0); ?>

	<div id="discussion_listing">
		<div id="edit_link"><a href="<?php echo $APPROOT; ?>/discussion/edit/<?php echo $forumData['0']['fid']; ?>">Edit</a></div>
		<h1><?php echo $forumData['0']['title']; ?></h1>
		<p><?php echo $this->raw($forumData['0']['prompt']); ?></p>
		
		<hr>
		
		<p>Sub forums:</p>
		<table class="data_table">
			<tr><th>Index</th><th>Members</th></tr>
			<?php foreach (($subforums?:array()) as $subforum): ?>
				<tr>
					<td><a href="<?php echo $APPROOT; ?>/discussion/<?php echo $forumData['0']['fid']; ?>/<?php echo $subforum['sfid']; ?>"><?php echo $subforum['sfid']; ?></a></td>
					<td><?php echo $subforum['members']; ?></td>
				</tr>
			<?php endforeach; ?>
		</table>
	</div>

</body>

</html>