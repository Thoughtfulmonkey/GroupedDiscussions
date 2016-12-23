<!DOCTYPE html>
<head>
	<title>Forum</title>
	<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans" />
	<link rel="stylesheet" type="text/css" href="<?php echo $APPROOT; ?>/css/default.css">
	<script src="<?php echo $APPROOT; ?>/js/jquery-3.1.0.min.js"></script>
	<script src="<?php echo $APPROOT; ?>/js/tinymce/tinymce.min.js"></script>
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
		
		// Load an adjacent forum
		function peeking( direction ){
			
			$('#peek_overlay').empty();
			$('#peek_overlay').removeClass();
			$('#peek_overlay').addClass('peek_'+direction);
			$('#peek_overlay').append('<iframe src="http://localhost/disc/peek/<?php echo $fid; ?>/'+direction+'"></iframe>');
			$('#peek_overlay').show();
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
			
			
		});
		
	
	</script>
</head>

<body>

	<?php echo $this->render('app/views/header.php',$this->mime,get_defined_vars(),0); ?>

	<div id="peek_overlay">
	</div>
	
	<div id="root_post">
		<div id="root_prompt"><?php echo $this->raw($prompt); ?></div>
		<div id="rpy_x" class="root_button">Post</div>
	</div>

	<div id="discussion_list">

			<?php foreach (($subforumposts?:array()) as $post): ?>
				<div class="post" id="pst_<?php echo $post['pid']; ?>" data-parent="<?php echo $post['parent']; ?>">
					<div class="post_head">
						<div class="post_author"><?php echo $post['username']; ?></div>
						<div class="post_date"><?php echo $post['created']; ?></div>
					</div>
					<div class="post_body">
						<p><?php echo $this->raw($post['content']); ?></p>
					</div>
					<div class="post_footer">
						<div class="reply_button" id="rpy_<?php echo $post['pid']; ?>">Reply</div>
					</div>
				</div>
			<?php endforeach; ?>
		
	</div>
	
	<div id="peek_left_tab" class="peek_tab">&lt;&lt;</div>
	<div id="peek_right_tab" class="peek_tab">&gt;&gt;</div>
	
	
	
</body>

</html>