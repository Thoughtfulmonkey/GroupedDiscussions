<?php

// All the discussion stuff
class Grouping {
	
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
	
}