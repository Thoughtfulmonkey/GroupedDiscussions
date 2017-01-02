<?php

// All the discussion stuff
class Discussions {
	
	
	// List the discussions to allow access
	function listall($f3){
		
		// Log in check
		if ( is_null($f3->get('SESSION.uid')) ) $f3->reroute('/login');
		else{
			
			// Is user admin?
			if ( $f3->get('SESSION.type') == 0 ){
				
				// Find all of the forums
				$f3->set('forumlist', $f3->get('DB')->exec('SELECT title, fid FROM forum_meta'));
			
				// Render the discussion list template (depending on whether admin or not)
				echo Template::instance()->render('app/views/discussionadminlist.php');
				
			} else {
				
				// Find all of the forums
				$f3->set('forumlist', $f3->get('DB')->exec('SELECT title, fid FROM forum_meta'));
			
				// Render the discussion list template (depending on whether admin or not)
				echo Template::instance()->render('app/views/discussionlist.php');
			}
		}
	}
	
	
	// Landing on a discussion
	function land($f3){
		
		// Admin user?
		if ( $f3->get('SESSION.type') == 0 ){
			$this->subForumListing($f3);
		}
		else {
			$this->discussionRedirect($f3);
		}
	}
	
	
	// Handle admin user's access to a forum
	// - list the available sub-forum
	function subForumListing($f3){
		
		// Sanitise forum id on address bar
		$fid = $f3->get('PARAMS.fid');
		$fid = $f3->scrub($fid);
		
		// Get the forum details
		$f3->set('forumData', $f3->get('DB')->exec('
			SELECT `fid`, `grouptype`, `title`, `prompt`
			FROM `forum_meta`
			WHERE `fid`=:fid',
			array( 
				':fid'=>$fid
			)
		));
		
		// Find all of the sub-forums
		$f3->set('subforums', $f3->get('DB')->exec('
			SELECT 
				`sub_forum`.`sfid`, 
				COUNT(`membership`.`sfid`) AS "members" 
			FROM `sub_forum` 
				JOIN `membership` ON `sub_forum`.`sfid` = `membership`.`sfid` 
			WHERE 
				`sub_forum`.`fid` = :fid 
			GROUP BY 
				`sub_forum`.`sfid`',
			array( 
				':fid'=>$fid
			)
		));
		
		// Present the list
		echo Template::instance()->render('app/views/subforumlist.php');
	}
	
	
	// Handle discussion user's access to a forum
	// - redirect to a sub-forum
	function discussionRedirect($f3){
		
		// Is the user a member of this forum (search for sub-forum index)
		$f3->set('subindex', $f3->get('DB')->exec('
			SELECT `sub_forum`.`sfid` 
			FROM `user` 
				INNER JOIN `membership` ON `user`.`uid` = `membership`.`uid` 
				INNER JOIN `sub_forum` ON `membership`.`sfid` = `sub_forum`.`sfid` 
			WHERE 
				`sub_forum`.`fid` = :fid 
				AND `user`.`uid` = :uid',
			array( ':fid'=>$f3->get('PARAMS.fid'), ':uid'=>$f3->get('SESSION.uid') )
		));
		
		// Retrieve the forum prompt and peek setting
		$forum = $f3->get('DB')->exec('
			SELECT `fid`, `prompt`, `allow_peeking` 
			FROM `forum_meta`  
			WHERE 
				`fid` = :fid ',
			array( ':fid'=>$f3->get('PARAMS.fid') )
		);
		$f3->set('forum_meta', $forum[0]);
		
		// Yes, they are a member of this forum already  - slightly weird to set subindex (maybe confused at the time)
		if ( count($f3->get('subindex')) == 1 ){
			
			// Display the sub-forum
			$this->displaysubforum( $f3, $f3->get('subindex')[0]["sfid"], false);
		}
		else if ( count($f3->get('subindex')) == 0 ){
				
			// No, Determine the grouping type
			$forum = $f3->get('DB')->exec('
				SELECT * 
				FROM `forum_meta` 
					INNER JOIN `groupings` ON `forum_meta`.`grouptype` = `groupings`.`id` 
				WHERE 
					`forum_meta`.`fid` = :fid ',
				array( ':fid'=>$f3->get('PARAMS.fid') )
			);
			
			// Assign to a sub-forum as required
			$addedTo = Grouping::addToGroup($f3, $forum);
			
			// Display the sub-forum
			$this->displaysubforum($f3, $addedTo, false);
		}
		else {

			$f3->set('error', 'You seem to be a member of more than one sub-forum. This should not have happened.');
			//$f3->reroute('/error');
			echo Template::instance()->render('app/views/error.php');
		}
		
	}
	
	// Find all posts for chosen sub-forum and pass to view
	function displaysubforum($f3, $index, $peek){
		
		// Sanitise forum id on address bar
		$fid = $f3->get('PARAMS.fid');
		$fid = $f3->scrub($fid);
		
		// Find all posts for this sub-forum, and author details
		$f3->set('subforumposts', $f3->get('DB')->exec('
			SELECT `posts`.*, `user`.`username` 
			FROM `posts` 
				INNER JOIN `user` ON `posts`.`author` = `user`.`uid`
			WHERE
				`posts`.`sfid` = :sfid
			ORDER BY
				`created` ASC',
			array( ':sfid'=>$index )
		));
		
		$f3->set('fid', $fid);
		
		// Render the discussion list template
		if ($peek) echo Template::instance()->render('app/views/forumpeek.php');
		else echo Template::instance()->render('app/views/forumview.php');
	}
	
	
	// Process reply submission
	// All input sanitised by F3 and PDO database calls
	function addreply ($f3){
		
		// TODO: check if user is allowed to post to this forum
		
		// TODO: integrate nonce-style checking
		
		// Sanitise forum id on address bar
		$fid = $f3->get('PARAMS.fid');
		$fid = $f3->scrub($fid);
		
		// Find which sub-forum the user should post to
		$result = $f3->get('DB')->exec('
			SELECT `sub_forum`.`sfid` 
			FROM `user` 
				INNER JOIN `membership` ON `user`.`uid` = `membership`.`uid` 
				INNER JOIN `sub_forum` ON `membership`.`sfid` = `sub_forum`.`sfid` 
			WHERE 
				`sub_forum`.`fid` = :fid 
				AND `user`.`uid` = :uid',
			array( ':fid'=>$fid, ':uid'=>$f3->get('SESSION.uid') )
		);
		
		// TODO: what if nothing returned?
		
		// Insert the post data
		$f3->get('DB')->exec('
			INSERT INTO `posts`
				(`sfid`, `parent`, `author`, `content`, `created`)
			VALUES
				(:sfid, :parent, :uid, :content, CURRENT_TIMESTAMP)',
			array( 
				':sfid'=>$result[0]['sfid'],
				':parent'=>$f3->get('POST.pid'),
				':uid'=>$f3->get('SESSION.uid'),
				':content'=>$f3->get('POST.posttext')
			)
		);
		
		// Re-route back to discussion view
		$f3->reroute('/discussion/'.$fid);
	}

	
	// Call to peek at an adjacent discussion
	function peek($f3){
		
		// Sanitise direction on address bar
		$d = $f3->get('PARAMS.direction');
		$d = $f3->scrub($d);
		
		// Sanitise forum id on address bar
		// TODO: Store in session to prevent URL manipulation
		$fid = $f3->get('PARAMS.fid');
		$fid = $f3->scrub($fid);
		
		// Find my sub_forum id
		//  Duplicated from Discussions->land (think about making a function)
		$sub = $f3->get('DB')->exec('
			SELECT `sub_forum`.`sfid` 
			FROM `user` 
				INNER JOIN `membership` ON `user`.`uid` = `membership`.`uid` 
				INNER JOIN `sub_forum` ON `membership`.`sfid` = `sub_forum`.`sfid` 
			WHERE 
				`sub_forum`.`fid` = :fid 
				AND `user`.`uid` = :uid',
			array( ':fid'=>$f3->get('PARAMS.fid'), ':uid'=>$f3->get('SESSION.uid') )
		);
		
		// Is there an ID?
		if ( isset($sub[0]["sfid"]) ){
			
			// Find a sub-forum to show
			$peekIds = null;
			
			$allsfids = $f3->get('DB')->exec('
				SELECT `sfid` 
				FROM `sub_forum` 
				WHERE `fid`=:fid
				ORDER BY `sfid` ASC',
				array(
					':fid'=>$fid,
				)
			);

			// Loop over all of the sub-forum-Ids to find target
			// TODO: Ordered data so could use a more efficient search
			// TODO: Any chance of not finding?  Index out of bounds error?
			$target = 0;
			while ( $allsfids[$target]['sfid'] != $sub[0]['sfid']  ) { $target++; }

			// Find adjacent ID or wrap around
			$peekId = null;
			if ($d == "left"){
				
				if ( strcmp($sub[0]['sfid'], $allsfids[0]['sfid']) !==0 ) $peekId = $allsfids[$target-1]['sfid'];
				else $peekId = $allsfids[ count($allsfids)-1 ]['sfid'];
				
			}else{
				
				if ( strcmp($sub[0]['sfid'], $allsfids[count($allsfids)-1]['sfid']) !==0 ) $peekId = $allsfids[$target+1]['sfid'];
				else $peekId = $allsfids[0]['sfid'];
				
			}

			// Use first sfid returned. Should be adjacent if there is one, or wrap around if there isn't
			// Now to pull all of the discussions
			// TODO: forum may have different prompt. If so, need to pass
			$this->displaysubforum($f3, $peekId, true);

		}
		else {
			echo "<p>Problem finding forum</p>";  // TODO: Transparent BG - needs full page
		}
	}
	
	
	// Share a post amongst sibling sub-forums
	function promote($f3){
		
		// Admin user?
		if ( $f3->get('SESSION.type') == 0 ){
			
			// TODO: What if no post fields

			// Find the sub-forum ids
			$subForums = $f3->get('DB')->exec('
				SELECT `sfid`
				FROM `sub_forum`
				WHERE `fid` = :fid',
				array( 
					':fid'=>$f3->get('POST.forum') 
				)
			);
			
			// Find the sub-forum the post is in
			$postForum = $f3->get('DB')->exec('
				SELECT `sfid`, `content`
				FROM `posts`
				WHERE `pid`=:pid',
				array( 
					':pid'=>$f3->get('POST.postId') 
				)
			);
			
			// Loop to add to all others except original
			for ($i=0; $i<count($subForums); $i++){
				
				if ($subForums[$i]['sfid'] !=  $postForum[0]['sfid'] ){
					
					$f3->get('DB')->exec('
						INSERT INTO `posts`
							(`sfid`, `parent`, `author`, `content`, `flag`)
						VALUES
							(:sfid, 0, 1, :content, 1)',
						array( 
							':sfid'=>$subForums[$i]['sfid'],
							':content'=>$postForum[0]['content']				
						)
					);
				}
			}
			echo "post promoted";
		}
		else {
			echo "not authorised";
		}
	}
	
	
	//
	function subForumDirect($f3){
		
		// Admin user?
		if ( $f3->get('SESSION.type') == 0 ){
			
			// Sanitise forum id on address bar
			$fid = $f3->get('PARAMS.fid');
			$fid = $f3->scrub($fid);
			
			// Sanitise sub-forum id on address bar
			$sfid = $f3->get('PARAMS.sfid');
			$sfid = $f3->scrub($sfid);

			// Retrieve the forum prompt and peek setting
			$forum = $f3->get('DB')->exec('
				SELECT `fid`, `prompt`, `allow_peeking` 
				FROM `forum_meta`  
				WHERE 
					`fid` = :fid ',
				array( ':fid'=>$f3->get('PARAMS.fid') )
			);
			$f3->set('forum_meta', $forum[0]);
			
			$this->displaysubforum($f3, $sfid, "", false);
		}
		else {
			$f3->reroute('/discussion/'.$fid);
		}
	}
	
	
	//
	function subForumPostDirect($f3){
		
		// Admin user?
		if ( $f3->get('SESSION.type') == 0 ){
			
			// Sanitise forum id on address bar
			$fid = $f3->get('PARAMS.fid');
			$fid = $f3->scrub($fid);
			
			// Sanitise forum id on address bar
			$sfid = $f3->get('PARAMS.sfid');
			$sfid = $f3->scrub($sfid);

			// Insert the post data
			$f3->get('DB')->exec('
				INSERT INTO `posts`
					(`sfid`, `parent`, `author`, `content`, `created`)
				VALUES
					(:sfid, :parent, :uid, :content, CURRENT_TIMESTAMP)',
				array( 
					':sfid'=>$sfid,
					':parent'=>$f3->get('POST.pid'),
					':uid'=>$f3->get('SESSION.uid'),
					':content'=>$f3->get('POST.posttext')
				)
			);
		
			// Re-route back to discussion view
			$f3->reroute('/discussion/'.$fid.'/'.$sfid);
		}
		else {
			$f3->reroute('/discussion/'.$fid);
		}
	}
}
	
?>