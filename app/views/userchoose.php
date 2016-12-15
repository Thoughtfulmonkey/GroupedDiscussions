<div id="user_panel" style="width:150px; position:absolute; top:0px; right:0px; border: 1px solid #222; padding: 5px;">

	<p>Login as:</p>

	<p>
		<repeat group="{{ @userlist }}" value="{{ @user }}">
			<a href="login/{{ @user.uid  }}">{{ @user.username  }}</a><br>
		</repeat>
	</p>
	
</div>