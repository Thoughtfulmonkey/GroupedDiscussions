<label for="title">Title:</label>
<input name="title" type="text" 
	<?php if ($mode=='edit'): ?>
		 value="<?php echo $forumData['0']['title']; ?>" 
	<?php endif; ?> 
><br><br>

<label for="prompt">Prompt:</label><br>
<textarea name="prompt">
	<?php if ($mode=='edit'): ?>
		 <?php echo $forumData['0']['prompt']; ?> 
	<?php endif; ?>
</textarea><br>

<label for="peeking">Peeking:</label>
<input class="group_option" type="radio" name="peeking" value="allow" 
	<?php if ($mode=='edit'): ?><?php if ($forumData['0']['allow_peeking']): ?>checked="checked"<?php endif; ?><?php endif; ?>
>Allow peeking 
<input class="group_option" type="radio" name="peeking" value="prevent" 
	<?php if ($mode=='edit'): ?><?php if (!$forumData['0']['allow_peeking']): ?>checked="checked"<?php endif; ?><?php endif; ?>
>No peeking<br>
<br>


