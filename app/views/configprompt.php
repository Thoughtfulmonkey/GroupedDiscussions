<!DOCTYPE html>
<head>
	<title>New Discussion</title>
	<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans" />
	<link rel="stylesheet" type="text/css" href="{{ @APPROOT }}/css/default.css">
	<script src="{{ @APPROOT }}/js/jquery-3.1.0.min.js"></script>
	<script src="{{ @APPROOT }}/js/tinymce/tinymce.min.js"></script>
	<script>
		
		<check if="{{ @mode=='edit' }}">
			<true>var optionId = {{@groupingData.numoptions+1}};</true>
			<false>var optionId = 1;</false>
		</check>
		
		$(document).ready( function(){
			
			$('#add_option').click( function(){
				
				var optionText = $('#option_text').val();
				
				var html = '<input name="optiontxt_'+optionId+'" type="text" class="appended_input" value="'+optionText+'"><div class="append_button negative">Del</div>';
				
				var option = document.createElement('div');
				$(option)
					.html(html)
					.attr("id", "option_"+optionId)
					.appendTo('#prompt_container');
					
				
				// Listener for the close button
				$(option).find('.append_button').click(function(){
					
					$(this).parent().remove();
				});
				
				// Increment counter
				optionId++;
				
				// Clear the input
				$('#option_text').val("");
				
			});
			
			// Listener for the close button
			$("#prompt_container").find('.append_button').click(function(){
				
				$(this).parent().remove();
			});
			
		});
		
	</script>
	<style>

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
		
		<form id="new_form" method='post' 
			<check if="{{ @mode=='edit' }}">
				<true> action='{{ @APPROOT }}/discussion/update/{{ @forumData[0].publicId }}' </true>
				<false>	action='{{ @APPROOT }}/build_grouping/prompt' </false>
			</check>
		>
			
			<include href="app/views/discussionformcommon.php" />
			
			<input type="hidden" name="grouping" value="prompt">
			
			<label for="prompt">Option Prompt:</label><br>
			<textarea name="optPrompt">
				<check if="{{ @mode=='edit' }}">
					<true> {{ @groupingData.option_prompt }} </true>
				</check>
			</textarea><br>
			
			Options:<br>
			<div id="prompt_container">
				<repeat group="{{ @groupingData.options }}" value="{{ @option }}" counter="{{@ctr}}">
					<div><input name="optiontxt_{{@ctr}}" type="text" class="appended_input" value="{{ @option }}"><div class="append_button negative">Del</div></div>
				</repeat>
			</div>
			<input id="option_text" type="text" class="appended_input"><div id="add_option" class="append_button positive">Add</div>
			<br><br>
			
			<label for="max">Maximum group size:</label><br>
			<input name="max" type="text" 
				<check if="{{ @mode=='edit' }}">
					<true> value="{{ @groupingData.max }}" </true>
				</check>
			><br><br>
			
			<check if="{{ @mode=='edit' }}">
				<true> <br><input type="submit" value="Update"> </true>
				<false>	<br><input type="submit" value="Create"> </false>
			</check>
		
		</form>
	
	</div>
	
	<script>
		tinymce.init({ selector:'textarea' });
	</script>

</body>

</html>