<!DOCTYPE html>
<head>
	<title>Forum</title>
	<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans" />
	<link rel="stylesheet" type="text/css" href="{{ @APPROOT }}/css/default.css">
	<script src="{{ @APPROOT }}/js/jquery-3.1.0.min.js"></script>
	<script src="{{ @APPROOT }}/js/tinymce/tinymce.min.js"></script>
	<script>
	
	</script>
</head>

<body>

	<include href="app/views/header.php" />

	<div id="discussion_listing">
		<h1>{{ @forumData[0].title }}</h1>
		<p>{{ @forumData[0].prompt | raw }}</p>
		
		<hr>
		
		<p>Sub forums:</p>
		<table class="data_table">
			<tr><th>Index</th><th>Members</th></tr>
			<repeat group="{{ @subforums }}" value="{{ @subforum }}">
				<tr>
					<td><a href="{{ @APPROOT }}/discussion/{{ @forumData[0].fid }}/{{ @subforum.sfid }}">{{ @subforum.sfid }}</a></td>
					<td>{{ @subforum.members }}</td>
				</tr>
			</repeat>
		</table>
	</div>

</body>

</html>