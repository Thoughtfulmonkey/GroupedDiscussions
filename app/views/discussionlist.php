<!DOCTYPE html>
<head>
	<title>Forum list</title>
	<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans" />
	<link rel="stylesheet" type="text/css" href="{{ @APPROOT }}/css/default.css">
	<script src="{{ @APPROOT }}/js/jquery-3.1.0.min.js"></script>
</head>

<body>

	<include href="app/views/header.php" />

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