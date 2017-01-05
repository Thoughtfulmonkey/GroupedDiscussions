<?php

namespace grouptypes;

class None {
	
	const TYPE_ID = 0;
	
	// Returns address of view to handle grouping type configuration
	public static function getConfigView(){
		return('app/views/confignone.php');
	}
	
	// Handles submission of the configuration form
	public static function buildGrouping($f3){
		return null;
	}
	
	// Extracts grouping type settings, and stores them in f3
	//  returns the view to handle editing the grouping type configuration
	public static function storeGroupingData($f3){
		return('app/views/confignone.php');
	}
	
	// Handles submission of edit form
	public static function updateGroupingData($f3, $fid){
	}
	
	// Handles a new user joining a forum of this grouping type
	//  decides which sub-forum to assign the user to
	public static function registerUser($f3, $forum){
		
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
			
			// Generate public ID
			// Slash is to return to root namespace
			\IdGeneration::generateLabel($f3, $subForum[0]['sfid'], "sub_forum", 10);
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
	
	
}


?>