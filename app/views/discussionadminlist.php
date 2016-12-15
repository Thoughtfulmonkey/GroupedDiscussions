<!DOCTYPE html>
<head>
	<title>Forum list</title>
	<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans" />
	<link rel="stylesheet" type="text/css" href="{{ @APPROOT }}/css/default.css">
	<script src="{{ @APPROOT }}/js/jquery-3.1.0.min.js"></script>
	<style>
	
		#create_block{
			margin: 0 auto;
			width: 500px;
			border: 1px solid #ccc;
			padding: 15px;
			text-align: center;
			background-color: #efe;
		}
	
	</style>
</head>

<body>

	<include href="app/views/header.php" />

	<div id="create_block">
		<a href="discussion/new">+ Create new discussion</a>
	</div>
	
	<div id="discussion_listing">

		<h2>Choose a discussion</h2>
	
		<repeat group="{{ @forumlist }}" value="{{ @forum }}">
			<div class="discussion_block">
				<a href="discussion/{{ @forum.fid  }}">{{ @forum.title  }}</a><br>
			</div>
		</repeat>
		
	</div>
	
</body>

</html>