<?php

// All the discussion stuff
class Grouping {
	
	
	// Main landing function
	static function addToGroup($f3, $forum){
		
		$addedTo = -1;
		
		// Only add to a group if not an admin
		if ( $f3->get('SESSION.type') != 0 ){
			
			$plugin = 'grouptypes\\'.$forum[0]['name'];
			$addedTo = $plugin::registerUser($f3, $forum);

		}
		
		return $addedTo;
	}
	
	
	// Get the parameter list for the chosen grouping option
	static function getparams($f3){
		
		// Sanitise forum id on address bar
		$opt = $f3->get('PARAMS.option');
		$opt = $f3->scrub($opt);
		
		// Retrieve and return param list
		$paramList = $f3->get('DB')->exec('
			SELECT structure
			FROM `groupings`
			WHERE
				`name`=:option',
			array( ':option'=>$opt )
		);
		
		if ( $paramList[0]["structure"] ) echo $paramList[0]["structure"];
		else echo "null";
	}
	
	
	// First landing point in creating a discussion
	//  choose the grouping type
	function chooseGrouping($f3){
		
		// Is user admin?
		if ( $f3->get('SESSION.type') == 0 ){
			
			// Find grouping types
			$f3->set('groupingoptions', $f3->get('DB')->exec('SELECT id, name FROM groupings'));
			
			// Display form to create new discussion
			echo Template::instance()->render('app/views/choosegrouping.php');
		}
		else {
			$f3->reroute('/discussion');
		}
		
	}
	
	// Define configuration page for grouping type
	function configGrouping($f3){
		
		// Sanitise grouping method on address bar
		$method = $f3->get('PARAMS.method');
		$method = $f3->scrub($method);
		
		// Set creation flag
		$f3->set('mode', 'create');
		
		// Find appropriate config view for grouping type
		$plugin = 'grouptypes\\'.$method;
		echo Template::instance()->render( $plugin::getConfigView($f3) );

	}
	
	
	// Define build page for each grouping type
	function buildGrouping($f3){
		
		// Sanitise grouping method on address bar
		$method = $f3->get('PARAMS.method');
		$method = $f3->scrub($method);
	
		// Process group specific config details
		$plugin = 'grouptypes\\'.$method;
		$groupingId = $plugin::buildGrouping($f3);
		
		// Process generic form details
		$this->buildDiscussionMeta($f3, $plugin::TYPE_ID, $groupingId);
	
	}
	
	
	// Assuming everything that needs to be done with the grouping has been
	// this function builds the discussion meta data
	// ** Maybe move to discussion controller
	// $groupingType = id for the type of grouping used
	// $groupingId = id in the appropriate grouping table
	function buildDiscussionMeta($f3, $groupingType, $groupingId){
		
		// Peeking switch
		$allowPeeking = false;
		if ( $f3->get('POST.peeking')=="allow" ) $allowPeeking = true;
		
		// Insert the forum meta data
		// - sub forums built as needed when users visit
		$f3->get('DB')->exec('
			INSERT INTO `forum_meta`
				(`grouptype`, `typeid`, `title`, `prompt`, `allow_peeking`)
			VALUES
				(:grouptype, :type, :title, :prompt, :peeking)',
			array( 
				':grouptype'=>$groupingType,
				':type'=>$groupingId,
				':title'=>$f3->get('POST.title'),
				':prompt'=>$f3->get('POST.prompt'),
				':peeking'=>$allowPeeking
			)
		);
		
		// Get the last inserted ID
		$result = $f3->get('DB')->exec('SELECT LAST_INSERT_ID()');
		$lid = $result[0]['LAST_INSERT_ID()'];
		
		// Generate a public ID
		IdGeneration::generateLabel($f3, $lid, "forum_meta", 10);
		
		// Redirect to the discussion root
		$f3->reroute('/discussion');
	}
	
	
	// Edit a discussion definition
	// - cannot change grouping type
	function edit($f3){
		
		// Sanitise forum id
		$publicfid = $f3->get('PARAMS.fid');
		$publicfid = $f3->scrub($publicfid);
		
		// Pull the forum details
		// - could be twice in a row. Any more efficient way?
		$f3->set('forumData', $f3->get('DB')->exec('
			SELECT `fid`, `publicId`, `grouptype`, `typeid`, `title`, `prompt`, `allow_peeking`, `name`
			FROM `forum_meta`
				JOIN `groupings` ON `forum_meta`.`grouptype` = `groupings`.`id`
			WHERE `publicId`=:publicfid',
			array( 
				':publicfid'=>$publicfid
			)
		));
		
		// Set editing flag
		$f3->set('mode', 'edit');
		
		// Select appropriate view for editing this grouping type
		$plugin = 'grouptypes\\'.$f3->get('forumData')[0]['name'];
		$view = $plugin::storeGroupingData($f3);
		echo Template::instance()->render($view);
		
	}
	
	
	// Updating details about a discussion
	function update ($f3){
		
		// Sanitise forum id
		$publicfid = $f3->get('PARAMS.fid');
		$publicfid = $f3->scrub($publicfid);
		
		// TODO: verify admin?
		//  should probably do something in index.php with admin only routing
		
		// Update the meta data
		$f3->get('DB')->exec('
			UPDATE `forum_meta`
			SET `title`=:title
			WHERE `publicId`=:publicfid',
			array( 
				':title'=>$f3->get('POST.title'),
				':publicfid'=>$publicfid,
			)
		);
		$f3->get('DB')->exec('
			UPDATE `forum_meta`
			SET `prompt`=:prompt
			WHERE `publicId`=:publicfid',
			array( 
				':prompt'=>$f3->get('POST.prompt'),
				':publicfid'=>$publicfid,
			)
		);
		// Peeking switch
		$allowPeeking = false;
		if ( $f3->get('POST.peeking')=="allow" ) $allowPeeking = true;
		$f3->get('DB')->exec('
			UPDATE `forum_meta`
			SET `allow_peeking`=:peeking
			WHERE `publicId`=:publicfid',
			array( 
				':peeking'=>$allowPeeking,
				':publicfid'=>$publicfid,
			)
		);
		
		
		// Grouping specific update
		// Is there a grouping option?
		if ( null !== $f3->get('POST.grouping') ){
			
			$plugin = 'grouptypes\\'.$f3->get('POST.grouping');
			$view = $plugin::updateGroupingData($f3, $publicfid);
		}
		
		// Reroute to discussion listing
		$f3->reroute('/discussion/'.$publicfid);
	}
	
	
}