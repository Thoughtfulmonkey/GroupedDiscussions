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
		
		// Retrieve the forum prompt
		$forum = $f3->get('DB')->exec('
			SELECT `prompt` 
			FROM `forum_meta`  
			WHERE 
				`fid` = :fid ',
			array( ':fid'=>$f3->get('PARAMS.fid') )
		);
		
		// Yes, they are a member of this forum already
		if ( count($f3->get('subindex')) == 1 ){
			
			// Display the sub-forum
			$this->displaysubforum( $f3, $f3->get('subindex')[0]["sfid"], $forum[0]["prompt"]);
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
			$this->displaysubforum($f3, $addedTo, $forum[0]["prompt"]);
		}
		else {

			$f3->set('error', 'You seem to be a member of more than one sub-forum. This should not have happened.');
			//$f3->reroute('/error');
			echo Template::instance()->render('app/views/error.php');
		}
		
	}
	
	// Find all posts for chosen sub-forum and pass to view
	function displaysubforum($f3, $index, $prompt){
		
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
		
		$f3->set('prompt', $prompt);
		
		// Render the discussion list template
		echo Template::instance()->render('app/views/forumview.php');
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
	
	// Request to create new discussion
	function definenew ($f3){
		
		// Is user admin?
		if ( $f3->get('SESSION.type') == 0 ){
			
			// Find grouping types
			$f3->set('groupingoptions', $f3->get('DB')->exec('SELECT id, name FROM groupings'));
			
			// Display form to create new discussion
			echo Template::instance()->render('app/views/discussionnew.php');
		}
		else {
			$f3->reroute('/discussion');
		}
	}
	
	// Submission of the new discussion form
	function submitnew ($f3){
		
		// Is user admin?
		if ( $f3->get('SESSION.type') == 0 ){
		
			// TODO: Sanitise everything
			$groupingName = $f3->get('POST.grouping');
		
			// Retrieve grouping type id
			$grouping = $f3->get('DB')->exec('
				SELECT id, structure
				FROM `groupings`
				WHERE
					`name`=:option',
				array( ':option'=> $groupingName)
			);
		
			// TODO: re-route on error (no option found)

			// Processing parameters (if any)
			$groupingId = 0;
			if ( $grouping[0]["structure"] ){
			
				// Process parameter list for SQL query elements
				$j = json_decode( $grouping[0]["structure"] );
				$pString = "";
				$pString = "";
				$vList = [];
				for ($i=0; $i<count($j->params); $i++){
					
					$pString = $pString."`".$j->params[$i]->name."`";
					$vString = $vString."?";
					
					//$vList[$j->params[$i]->name] = $f3->get('POST.'.$j->params[$i]->name);
					array_push( $vList, $f3->get('POST.'.$j->params[$i]->name) );
					
					if ($i<count($j->params)-1) {
						$pString = $pString.", ";
						$vString = $vString.", ";
					}
				}
				
				// Create an entry in grouping table for this forum
				//  $groupingName chooses table for this grouping's params
				//  $pString is the list of parameters separated by commas
				//  $vString is a series of question marks - one for each parameter
				//  $vList is an array of parameters - to substitue the question marks
				$f3->get('DB')->exec('
					INSERT INTO `grouping_'.$groupingName.'`
						('.$pString.')
					VALUES
						('.$vString.')',
					$vList
				);

				
				// Find last inserted id
				$result = $f3->get('DB')->exec('SELECT LAST_INSERT_ID()');
				$groupingId = $result[0]['LAST_INSERT_ID()'];
			}

			// Insert the forum meta data
			// - sub forums built as needed when users visit
			$f3->get('DB')->exec('
				INSERT INTO `forum_meta`
					(`grouptype`, `typeid`, `title`, `prompt`)
				VALUES
					(:grouptype, :type, :title, :prompt)',
				array( 
					':grouptype'=>$grouping[0]["id"],
					':type'=>$groupingId,
					':title'=>$f3->get('POST.title'),
					':prompt'=>$f3->get('POST.prompt')
				)
			);			

		}
		
		// Either way, redirect to the discussion root
		$f3->reroute('/discussion');
	}
}
	
?>