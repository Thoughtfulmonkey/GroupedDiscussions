<label for="title">Title:</label><br>
<input name="title" type="text" 
	<check if="{{ @mode=='edit' }}">
		<true> value="{{ @forumData[0].title }}" </true>
	</check> 
><br><br>

<label for="prompt">Discussion Prompt:</label><br>
<textarea name="prompt">
	<check if="{{ @mode=='edit' }}">
		<true> {{ @forumData[0].prompt }} </true>
	</check>
</textarea><br>

<label for="peeking">Peeking:</label><br>
<input class="group_option" type="radio" name="peeking" value="allow" 
	<check if="{{ @mode=='edit' }}"><true><check if="{{ @forumData[0].allow_peeking }}"><true>checked="checked"</true></check></true></check>
>Allow peeking<br>
<input class="group_option" type="radio" name="peeking" value="prevent" 
	<check if="{{ @mode=='edit' }}"><true><check if="{{ !@forumData[0].allow_peeking }}"><true>checked="checked"</true></check></true><false>checked="checked"</false></check>
>No peeking<br>
<br>


