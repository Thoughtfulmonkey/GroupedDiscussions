<?php

// All the discussion stuff
class Grouping {
	
	// TODO: Verify in DB somehow (maybe build DB from these)
	const TYPE_NONE = 0;
	const TYPE_SILO = 1;
	
	// Main landing function
	static function addToGroup($f3, $forum){
		
		$addedTo = -1;
		
		// Only add to a group if not an admin
		if ( $f3->get('SESSION.type') != 0 ){
			
			// Switch based on group type
			switch( $forum[0]['name'] ){
				case 'none':
					$addedTo = Grouping::singleForum($f3, $forum);
					break;
				case 'silo':
					$addedTo = Grouping::addToSilo($f3, $forum);
					break;
				default:
					// Same as none? Or return error?
			}
		}
		
		return $addedTo;
	}
	
	
	// Group users into silos
	// Do not exceed maximum number
	// TODO: use multiple active silos to reduce chance of loners
	static function addToSilo($f3, $forum){
	
		// Get maximum number
		$details = $f3->get('DB')->exec('
			SELECT * 
			FROM `grouping_'.$forum[0]['name'].'` 
			WHERE 
				`id` = :gid ',
			array( ':gid'=>$forum[0]['typeid'] )
		);	
		$max = $details[0]['max'];
		
		// Search for groups with less than maximum
		$suitableGroups = $f3->get('DB')->exec('
			SELECT `sub_forum`.`sfid`, COUNT(`membership`.`sfid`) AS members
			FROM `sub_forum` 
				INNER JOIN `membership` ON `sub_forum`.`sfid` = `membership`.`sfid` 
			WHERE
				`fid`=:fid
			GROUP BY 
				`membership`.`sfid`
			HAVING
				COUNT(`membership`.`sfid`)<:max',
			array( ':fid'=>$forum[0]['fid'], ':max'=>$max )
		);
		
		// If no suitable groups exist, then create one
		// TODO: Use transaction or rollback in F3 to prevent lots of sub-forums if errors
		if ( count($suitableGroups)==0 ){

			// Create a sub-forum
			$f3->get('DB')->exec('
				INSERT INTO `sub_forum`
					(`fid`)
				VALUES
					(:fid)',
				array( ':fid'=>$forum[0]['fid'] )
			);
			
			// Re-search for the newly added group (note left join)
			$suitableGroups = $f3->get('DB')->exec('
				SELECT `sub_forum`.`sfid`, COUNT(`membership`.`sfid`) AS members
				FROM `sub_forum` 
					LEFT JOIN `membership` ON `sub_forum`.`sfid` = `membership`.`sfid` 
				WHERE
					`fid`=:fid
				GROUP BY 
					`membership`.`sfid`
				HAVING
					COUNT(`membership`.`sfid`)<:max',
				array( ':fid'=>$forum[0]['fid'], ':max'=>$max )
			);
		}

		// Add to a suitable group
		// Either found one with less than max users, or created new
		$f3->get('DB')->exec('
			INSERT INTO `membership`
				(`uid`, `sfid`)
			VALUES
				(:uid, :sfid)',
			array( ':uid'=>$f3->get('SESSION.uid'), ':sfid'=>$suitableGroups[0]['sfid'] )
		);

		return $suitableGroups[0]['sfid'];
	}
	
	
	// Single forum for everyone
	// See if a single sub-forum entry exists, and create if not
	static function singleForum($f3, $forum){
		
		// Search for groups with less than maximum
		$subForum = $f3->get('DB')->exec('
			SELECT `sub_forum`.`sfid`
			FROM `sub_forum` 
				INNER JOIN `membership` ON `sub_forum`.`sfid` = `membership`.`sfid` 
			WHERE
				`fid`=:fid
			GROUP BY 
				`membership`.`sfid`',
			array( ':fid'=>$forum[0]['fid'] )
		);
		
		// If no suitable groups exist, then create one
		// TODO: Use transaction or rollback in F3 to prevent lots of sub-forums if errors
		if ( count($subForum)==0 ){

			// Create a sub-forum
			$f3->get('DB')->exec('
				INSERT INTO `sub_forum`
					(`fid`)
				VALUES
					(:fid)',
				array( ':fid'=>$forum[0]['fid'] )
			);
			
			// Re-search for the newly added group (note left join)
			$subForum = $f3->get('DB')->exec('
				SELECT `sub_forum`.`sfid`
				FROM `sub_forum` 
					LEFT JOIN `membership` ON `sub_forum`.`sfid` = `membership`.`sfid` 
				WHERE
					`fid`=:fid
				GROUP BY 
					`membership`.`sfid`',
				array( ':fid'=>$forum[0]['fid'] )
			);
		}
		
		// Add to a suitable group
		// Either found one with less than max users, or created new
		$f3->get('DB')->exec('
			INSERT INTO `membership`
				(`uid`, `sfid`)
			VALUES
				(:uid, :sfid)',
			array( ':uid'=>$f3->get('SESSION.uid'), ':sfid'=>$subForum[0]['sfid'] )
		);
		
		return $subForum[0]['sfid'];
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
		
		// Sanitise forum id on address bar
		$method = $f3->get('PARAMS.method');
		$method = $f3->scrub($method);
		
		// Set creation flag
		$f3->set('mode', 'create');
		
		switch ($method){
			case "none":
				echo Template::instance()->render('app/views/confignone.php');
				break;
			case "silo":
				echo Template::instance()->render('app/views/configsilo.php');
				break;
			default:
				// Redirect to the discussion root, not sure what else to do
				$f3->reroute('/discussion');
		}

	}
	
	
	// Define build page for each grouping type
	function buildGrouping($f3){
		
		// Sanitise grouping method on address bar
		$method = $f3->get('PARAMS.method');
		$method = $f3->scrub($method);
		
		switch ($method){
			case "none":
				$this->buildDiscussionMeta($f3, Grouping::TYPE_NONE, null);
				break;
			case "silo":
				$this->buildSiloGrouping($f3);
				break;
			default:
				// Redirect to the discussion root, not sure what else to do
				$f3->reroute('/discussion');
		}
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
		
		// Redirect to the discussion root
		$f3->reroute('/discussion');
	}
	
	
	// Edit a discussion definition
	// - cannot change grouping type
	function edit($f3){
		
		// Sanitise forum id
		$fid = $f3->get('PARAMS.fid');
		$fid = $f3->scrub($fid);
		
		// Pull the forum details
		// - could be twice in a row. Any more efficient way?
		$f3->set('forumData', $f3->get('DB')->exec('
			SELECT `fid`, `grouptype`, `typeid`, `title`, `prompt`, `allow_peeking`, `name`
			FROM `forum_meta`
				JOIN `groupings` ON `forum_meta`.`grouptype` = `groupings`.`id`
			WHERE `fid`=:fid',
			array( 
				':fid'=>$fid
			)
		));
		
		// Set editing flag
		$f3->set('mode', 'edit');
		
		// Is there a grouping option?
		if ( $f3->get('forumData')[0]['name'] == "none" ){
			echo Template::instance()->render('app/views/confignone.php');
		} else {
			
			// Load the grouping config
			$f3->set('groupingData', $f3->get('DB')->exec('
				SELECT *
				FROM `grouping_'.$f3->get('forumData')[0]['name'].'`
				WHERE `id`=:typeid',
				array( 
					':typeid'=>$f3->get('forumData')[0]['typeid']
				)
			));
			
			// Stick with set grouping method
			switch ( $f3->get('forumData')[0]['name'] ){
				case "silo":
					echo Template::instance()->render('app/views/configsilo.php');
					break;
				default:
					// Redirect to the discussion root, not sure what else to do
					// - should probably be error (but shouldn't reach this)
					$f3->reroute('/discussion');
			}
		}
		
	}
	
	
	// Updating details about a discussion
	function update ($f3){
		
		// Sanitise forum id
		$fid = $f3->get('PARAMS.fid');
		$fid = $f3->scrub($fid);
		
		// TODO: verify admin?
		//  should probably do something in index.php with admin only routing
		
		// Update the meta data
		$f3->get('DB')->exec('
			UPDATE `forum_meta`
			SET `title`=:title
			WHERE `fid`=:fid',
			array( 
				':title'=>$f3->get('POST.title'),
				':fid'=>$fid,
			)
		);
		$f3->get('DB')->exec('
			UPDATE `forum_meta`
			SET `prompt`=:prompt
			WHERE `fid`=:fid',
			array( 
				':prompt'=>$f3->get('POST.prompt'),
				':fid'=>$fid,
			)
		);
		// Peeking switch
		$allowPeeking = false;
		if ( $f3->get('POST.peeking')=="allow" ) $allowPeeking = true;
		$f3->get('DB')->exec('
			UPDATE `forum_meta`
			SET `allow_peeking`=:peeking
			WHERE `fid`=:fid',
			array( 
				':peeking'=>$allowPeeking,
				':fid'=>$fid,
			)
		);
		
		
		// Grouping specific update
		// Is there a grouping option?
		if ( null !== $f3->get('POST.grouping') ){
			
			// Stick with set grouping method
			switch( $f3->get('POST.grouping') ){
				case "silo":
					$this->updateSiloGrouping($f3, $fid);
					break;
				default:
					// Redirect to the discussion root, not sure what else to do
					// - should probably be error (but shouldn't reach this)
					$f3->reroute('/discussion');
			}
		}
		
		// Reroute to discussion listing
		$f3->reroute('/discussion/'.$fid);
	}
	
	
	// Fill grouping config table from form data
	//  Requires correct JSON structure set in grouping table
	//  Replaced by individual function - in case they need to do something fancy
	function groupingParamPump($f3, $groupingName){
		
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
		$groupingId = null;
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
		
		// Build meta data entry
		$this->buildDiscussionMeta($f3, $grouping[0]["id"], $groupingId);
	}
	
	
	// Specific data configuration for silo grouping type
	function buildSiloGrouping($f3){
		
		$f3->get('DB')->exec('
			INSERT INTO `grouping_silo`
				(`min`, `max`)
			VALUES
				(:min, :max)',
			array( 
				':min'=>$f3->get('POST.min'),
				':max'=>$f3->get('POST.max')
			)
		);

		// Find last inserted id
		$result = $f3->get('DB')->exec('SELECT LAST_INSERT_ID()');
		$groupingId = $result[0]['LAST_INSERT_ID()'];
		
		// Build meta data entry
		$this->buildDiscussionMeta($f3, Grouping::TYPE_SILO, $groupingId);
	}
	
	// Update for silo grouping
	function updateSiloGrouping($f3, $fid){
		
		$f3->get('DB')->exec('
			UPDATE `grouping_silo`
				JOIN `forum_meta` ON `forum_meta`.`typeid` = `grouping_silo`.`id`
			SET `min`=:min
			WHERE `fid`=:fid',
			array( 
				':min'=>$f3->get('POST.min'),
				':fid'=>$fid,
			)
		);
		$f3->get('DB')->exec('
			UPDATE `grouping_silo`
				JOIN `forum_meta` ON `forum_meta`.`typeid` = `grouping_silo`.`id`
			SET `max`=:max
			WHERE `fid`=:fid',
			array( 
				':max'=>$f3->get('POST.max'),
				':fid'=>$fid,
			)
		);		
		
	}
	
}