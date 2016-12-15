<!DOCTYPE html>
<head>
	<title>Forum</title>
	<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans" />
	<link rel="stylesheet" type="text/css" href="{{ @APPROOT }}/css/default.css">
	<script src="{{ @APPROOT }}/js/jquery-3.1.0.min.js"></script>
	<script src="{{ @APPROOT }}/js/tinymce/tinymce.min.js"></script>
	<script>
	
		function addTinyMCE( ref ){
			
			// Remove any other text areas
			// TODO: probably needs a check if empty so work isn't lost
			$('form').remove();
			
			// Extract post id
			var pid = $(ref).attr('id').substring(4);
			
			// Add the text area
			$(ref).parent().append("<form method='post'><textarea name='posttext'></textarea><input type='hidden' name='pid' value='"+pid+"'><input type='button' id='cancel' value='Cancel'/><input type='submit' value='Post'/></form>");
			
			// Make the tinymce
			tinymce.init({ selector:'textarea' });
			
			// Action of the cancel button
			$('#cancel').click(function(){
				$('form').remove();
			});
		}
	
		// Document loads
		$(document).ready(function(){
					
			// Re-order the posts to correct nested structure
			$('.post').each(function(){

				var parent = $(this).data("parent");
			
				// Has a parent?
				if ( parent != "" ){
					
					// Move in DOM (detach not strictly necessary, but just for safety)
					$(this).detach().insertAfter('#pst_'+parent);
					
					// Indent when moved
					// TODO: if not supplied in date order, then this could break
					var parentIndent = $('#pst_'+parent).css('margin-left').replace(/px$/, '');	// Get margin and strip 'px'
					parentIndent = ( parseInt(parentIndent) + 40 ) + 'px';						// Convert to int, add indent, re-add 'px'
					$(this).css('margin-left', parentIndent);									// Update the indent
				}
				
			});
			
			// A reply button was clicked
			$('.reply_button').click(function(){
				
				addTinyMCE( this );
			});
			
			// The root post button was clicked
			$('#rpy_x').click(function(){
				addTinyMCE( this );
			});
			
		});
	
	</script>
</head>

<body>

	<include href="app/views/header.php" />

	<div id="root_post">
		<div id="root_prompt">{{ @prompt | raw }}</div>
		<div id="rpy_x" class="root_button">Post</div>
	</div>

	<div id="discussion_list">

			<repeat group="{{ @subforumposts }}" value="{{ @post }}">
				<div class="post" id="pst_{{ @post.pid }}" data-parent="{{ @post.parent }}">
					<div class="post_head">
						<div class="post_author">{{ @post.username  }}</div>
						<div class="post_date">{{ @post.created }}</div>
					</div>
					<div class="post_body">
						<p>{{ @post.content | raw }}</p>
					</div>
					<div class="post_footer">
						<div class="reply_button" id="rpy_{{ @post.pid }}">Reply</div>
					</div>
				</div>
			</repeat>
		
	</div>
	
</body>

</html>