<!DOCTYPE html>
<head>
	<title>Forum</title>
	<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans" />
	<link rel="stylesheet" type="text/css" href="{{ @APPROOT }}/css/default.css">
	<script src="{{ @APPROOT }}/js/jquery-3.1.0.min.js"></script>
	<script src="{{ @APPROOT }}/js/tinymce/tinymce.min.js"></script>
	<script>
	
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
			
		});
		
	
	</script>
</head>

<body>
	
	<div id="peek_heading">Peeking. You cannot reply to these posts</div>
	
	<div id="discussion_list">

			<repeat group="{{ @subforumposts }}" value="{{ @post }}">
				<div class="post" id="pst_{{ @post.publicId }}" data-parent="{{ @post.parent }}">
					<div class="post_head">
						<div class="post_author">{{ @post.username  }}</div>
						<div class="post_date">{{ @post.created }}</div>
					</div>
					<div class="post_body">
						<p>{{ @post.content | raw }}</p>
					</div>
					<div class="post_footer">
					</div>
				</div>
			</repeat>
		
	</div>
	
</body>

</html>