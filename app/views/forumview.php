<!DOCTYPE html>
<head>
	<title>Forum</title>
	<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans" />
	<link rel="stylesheet" type="text/css" href="{{ @APPROOT }}/css/default.css">
	<script src="{{ @APPROOT }}/js/jquery-3.1.0.min.js"></script>
	<script src="{{ @APPROOT }}/js/tinymce/tinymce.min.js"></script>
	<script>
	
		var peekState = 0;

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
		
		<check if="{{ (@forum_meta.allow_peeking) && (@SESSION.type!=0) }}"><true>
			// Load an adjacent forum
			function peeking( direction ){
				
				$('#peek_overlay').empty();
				$('#peek_overlay').removeClass();
				$('#peek_overlay').addClass('peek_'+direction);
				$('#peek_overlay').append('<iframe src="http://localhost/disc/peek/{{ @forum_meta.publicId }}/'+direction+'"></iframe>');
				$('#peek_overlay').show();
			}
		</true></check>
	
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
			
			<check if="{{ @SESSION.type==0 }}"><true>
			
				// A promote button was clicked (only admin can promote)
				$('.promote_button').click(function(){
					
					var promoId = $(this).attr('id').substring(4);
				
					$.post("{{ @APPROOT }}/promote",
						{
							postId: promoId,
							forum: "{{ @forum_meta.publicId }}"
						},
						function(data, status){
							// TODO: Check status first
							alert(data);
						}
					);
					
				});
			
			</true></check>
			
			<check if="{{ (@forum_meta.allow_peeking) && (@SESSION.type!=0) }}"><true>
				// Peeking click
				$('#peek_left_tab').click( function(){ 
				
					if (peekState == -1){
						$('#peek_left_tab').css("left", "0px");
						$('#peek_overlay').empty();
						$('#peek_overlay').removeClass();
						$('#peek_overlay').hide();
						peekState = 0;
					}
					else{
						peeking("left");
						$('#peek_left_tab').css("left", "605px");
						if (peekState == 1) $('#peek_right_tab').css("right", "0px");
						peekState = -1;
					}
					
				});
				$('#peek_right_tab').click( function(){ 
					if (peekState == 1){
						$('#peek_right_tab').css("right", "0px");
						$('#peek_overlay').empty();
						$('#peek_overlay').removeClass();
						$('#peek_overlay').hide();
						peekState = 0;
					}
					else{
						peeking("right");
						$('#peek_right_tab').css("right", "605px");
						if (peekState == -1) $('#peek_left_tab').css("left", "0px");
						peekState = 1;
					} 
				});
			</true></check>
			
			
		});
	
	</script>
</head>

<body>

	<include href="app/views/header.php" />

	<check if="{{ (@forum_meta.allow_peeking) && (@SESSION.type!=0) }}"><true>
		<div id="peek_overlay">
		</div>
	</true></check>
	
	<div id="root_post">
		<div id="root_prompt">{{ @forum_meta.prompt | raw }}</div>
		<div id="rpy_x" class="root_button">Post</div>
	</div>

	<div id="discussion_list">

			<repeat group="{{ @subforumposts }}" value="{{ @post }}">
				<div class="post <check if="@post.flag==1"><true>promoted</true></check>" id="pst_{{ @post.publicId }}" data-parent="{{ @post.parent }}">
					<div class="post_head">
						<check if="@post.flag==1">
							<true><div class="post_author">Promoted</div></true>
							<false><div class="post_author">{{ @post.username  }}</div></false>
						</check>
						<div class="post_date">{{ @post.created }}</div>
					</div>
					<div class="post_body">
						<p>{{ @post.content | raw }}</p>
					</div>
					<div class="post_footer">
						<div class="reply_button" id="rpy_{{ @post.publicId }}">Reply</div>
						<check if="{{ @SESSION.type==0 }}"><true>
							<div class="promote_button" id="pro_{{ @post.publicId }}">Promote</div>
						</true></check>
					</div>
				</div>
			</repeat>
		
	</div>
	
	<check if="{{ (@forum_meta.allow_peeking) && (@SESSION.type!=0) }}"><true>
		<div id="peek_left_tab" class="peek_tab">&lt;&lt;</div>
		<div id="peek_right_tab" class="peek_tab">&gt;&gt;</div>
	</true></check>
	
	
	
</body>

</html>