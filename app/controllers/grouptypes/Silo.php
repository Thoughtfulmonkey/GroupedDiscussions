<?php

namespace grouptypes;

class Silo {
	
	const TYPE_ID = 1;
	
	// Returns address of view to handle grouping type configuration
	public static function getConfigView($f3){
		return('app/views/configsilo.php');
	}
	
	// Handles submission of the configuration form
	public static function buildGrouping($f3){
		
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
		
		return $groupingId;
	}
	
	// Extracts grouping type settings, and stores them in f3
	//  returns the view to handle editing the grouping type configuration
	public static function storeGroupingData($f3){
		
		// Load the grouping config
		$f3->set('groupingData', $f3->get('DB')->exec('
			SELECT *
			FROM `grouping_'.$f3->get('forumData')[0]['name'].'`
			WHERE `id`=:typeid',
			array( 
				':typeid'=>$f3->get('forumData')[0]['typeid']
			)
		));
		
		return('app/views/configsilo.php');
	}
	
	// Handles submission of edit form
	public static function updateGroupingData($f3, $publicfid){
		
		$f3->get('DB')->exec('
			UPDATE `grouping_silo`
				JOIN `forum_meta` ON `forum_meta`.`typeid` = `grouping_silo`.`id`
			SET `min`=:min
			WHERE `publicId`=:publicfid',
			array( 
				':min'=>$f3->get('POST.min'),
				':publicfid'=>$publicfid,
			)
		);
		$f3->get('DB')->exec('
			UPDATE `grouping_silo`
				JOIN `forum_meta` ON `forum_meta`.`typeid` = `grouping_silo`.`id`
			SET `max`=:max
			WHERE `publicId`=:publicfid',
			array( 
				':max'=>$f3->get('POST.max'),
				':publicfid'=>$publicfid,
			)
		);		
	}
	
	// Handles a new user joining a forum of this grouping type
	//  decides which sub-forum to assign the user to
	// TODO: use multiple active silos to reduce chance of loners
	public static function registerUser($f3, $forum){
		
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
			
			// Generate public ID
			// Slash is to return to root namespace
			\IdGeneration::generateLabel($f3, $suitableGroups[0]['sfid'], "sub_forum", 10);
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
	
}


?>