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
		
		<check if="{{ @sequence=='select' }}">
			<true>
			
				<form id="new_form" method='post' 
					<check if="{{ @mode=='edit' }}">
						<true> action='{{ @APPROOT }}/discussion/update/{{ @forumData[0].publicId }}' </true>
						<false>	action='{{ @APPROOT }}/config_grouping/activity' </false>
					</check>
				>
			
					<label for="sourceForum">Choose the discussion to base activity on:</label><br>
					
					<select name="sourceForum">
					<repeat group="{{ @forumlist }}" value="{{ @forum }}">
						<option value="{{ @forum.publicId }}">{{ @forum.title }}</option>
					</repeat>
					</select>
					
					<br><br><input type="submit" value="Select">
					
				</form>
			
			</true>
			
			<false>
		
				<form id="new_form" method='post' 
					<check if="{{ @mode=='edit' }}">
						<true> action='{{ @APPROOT }}/discussion/update/{{ @forumData[0].publicId }}' </true>
						<false>	action='{{ @APPROOT }}/build_grouping/activity' </false>
					</check>
				>
					
					<include href="app/views/discussionformcommon.php" />
					
					<input type="hidden" name="grouping" value="activity">
					<input type="hidden" name="sourceForum" value="{{ @POST.sourceForum }}">
					
					<label for="min">Minimum group size</label>
					<input name="min" type="text" 
						<check if="{{ @mode=='edit' }}">
							<true> value="{{ @groupingData[0].min }}" </true>
						</check>
					><br>
					
					<label for="max">Maximum group size</label>
					<input name="max" type="text" 
						<check if="{{ @mode=='edit' }}">
							<true> value="{{ @groupingData[0].max }}" </true>
						</check>
					><br><br>
					
					Forum Details:<br>
					<check if="{{ @posters_counted>0 }}">
						<true>
							<table class="data_table">
								<tr> <td></td> <th>Minimum</th> <th>Maximum</th> <th>Average</th> </tr>
								<tr> <th>Posts</th> <td>{{ @MinF }}</td> <td>{{ @MaxF }}</td> <td>{{ @AvgF }}</td> </tr>
								<tr> <th>Avg. Length</th> <td>{{ @MinL }}</td> <td>{{ @MaxL }}</td> <td>{{ @AvgL }}</td> </tr>
							</table>
						</true>
						<false><i>There are no posts in the chosen discussion</i></false>
					</check>
					<br>
					
					<label for="postCut">Posts cut-off</label>
					<input name="postCut" type="text" 
						<check if="{{ @mode=='edit' }}">
							<true> value="{{ @groupingData[0].postCut }}" </true>
						</check>
					><br>
					
					<label for="lengthCut">Length cut-off</label>
					<input name="lengthCut" type="text" 
						<check if="{{ @mode=='edit' }}">
							<true> value="{{ @groupingData[0].lengthCut }}" </true>
						</check>
					><br><br>
					
					<check if="{{ @mode=='edit' }}">
						<true> <br><input type="submit" value="Update"> </true>
						<false>	<br><input type="submit" value="Create"> </false>
					</check>
				
				</form>
			</false>
			
		</check>
	
	</div>
	
	<script>
		tinymce.init({ selector:'textarea' });
	</script>

</body>

</html>