<div id="user_panel" style="width:150px; position:absolute; top:0px; right:0px; border: 1px solid #222; padding: 5px;">

	<p>Login as:</p>

	<p>
		<?php foreach (($userlist?:array()) as $user): ?>
			<a href="login/<?php echo $user['uid']; ?>"><?php echo $user['username']; ?></a><br>
		<?php endforeach; ?>
	</p>
	
</div>