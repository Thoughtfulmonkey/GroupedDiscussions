<?php

namespace grouptypes;

class Activity {
	
	const TYPE_ID = 2;
	
	// Returns address of view to handle grouping type configuration
	public static function getConfigView($f3){
		
		if ( null === $f3->get('POST.sourceForum') ){
			
			// First step, pick a forum to evaluate activity from
			$f3->set('sequence', 'select');
			
			// Find all of the forums
			$f3->set('forumlist', $f3->get('DB')->exec('SELECT title, publicId FROM forum_meta'));
			
		} else {
			
			// Second step, configure the settings
			$f3->set('sequence', 'config');
			
			// Make some calculations to help the decision
			
			// Find all posts from that forum
			$posts = $f3->get('DB')->exec('
				SELECT COUNT(`pid`) AS "posts", SUM( CHAR_LENGTH(`content`) ) AS "chars" 
				FROM `posts` 
					JOIN `sub_forum` ON `posts`.`sfid` = `sub_forum`.`sfid`
					JOIN `forum_meta` ON `sub_forum`.`fid` = `forum_meta`.`fid`
				WHERE 
					`forum_meta`.`publicId` = :publicId
				GROUP BY (`author`)',
				array( 
					':publicId'=>$f3->get('POST.sourceForum') 
				)
			);

			$f3->set('posters_counted', count($posts));
			if ( count($posts) > 0 ){
				
				// Loop to find min, max, and averages
				$minf = -1; $maxf = -1; $totalf = 0;
				$minl = -1; $maxl = -1; $totall = 0;
				for ($i=0; $i<count($posts); $i++){
					
					$totalf += $posts[$i]['posts'];
					$avgl = round( $posts[$i]['chars'] / $posts[$i]['posts'], 2 );
					$totall += $avgl;
					
					if ( ($posts[$i]['posts']<$minf) || ($minf==-1) ) $minf = $posts[$i]['posts'];
					if ( ($posts[$i]['posts']>$maxf) || ($maxf==-1) ) $maxf = $posts[$i]['posts'];
					
					if ( ($avgl<$minl) || ($minl==-1) ) $minl = $avgl;
					if ( ($avgl>$maxl) || ($maxl==-1) ) $maxl = $avgl;
				}
				
				// TODO: rounding of averages
				
				// Store values for display
				$f3->set('MinL', $minl);
				$f3->set('MaxL', $maxl);
				$f3->set('AvgL', round($totall/count($posts),2) );
				
				$f3->set('MinF', $minf);
				$f3->set('MaxF', $maxf);
				$f3->set('AvgF', round($totalf/count($posts),2) );
				
			} else {
				
			}
		}
		
		return('app/views/configactivity.php');
	}
	
	// Handles submission of the configuration form
	public static function buildGrouping($f3){
		
		// Retrieve the forum id
		$forum = $f3->get('DB')->exec('
			SELECT `fid`
			FROM `forum_meta`  
			WHERE 
				`publicId` = :publicfid ',
			array( ':publicfid'=>$f3->get('POST.sourceForum') )
		);
		
		// Store grouping config settings
		$f3->get('DB')->exec('
			INSERT INTO `grouping_activity`
				(`max`, `min`, `lengthCut`, `postCut`)
			VALUES
				(:max, :min, :lengthCut, :postCut)',
			array( 
				':max'=>$f3->get('POST.max'),
				':min'=>$f3->get('POST.min'),
				':lengthCut'=>$f3->get('POST.lengthCut'),
				':postCut'=>$f3->get('POST.postCut')
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
		
		// Second step, configure the settings
		$f3->set('sequence', 'config');
		
		// Load the grouping config
		$f3->set('groupingData', $f3->get('DB')->exec('
			SELECT *
			FROM `grouping_activity`
			WHERE `id`=:typeid',
			array( 
				':typeid'=>$f3->get('forumData')[0]['typeid']
			)
		));
		
		// Make some calculations to help the decision
		
		// Find all posts from that forum
		$posts = $f3->get('DB')->exec('
			SELECT COUNT(`pid`) AS "posts", SUM( CHAR_LENGTH(`content`) ) AS "chars" 
			FROM `posts` 
				JOIN `sub_forum` ON `posts`.`sfid` = `sub_forum`.`sfid`
			WHERE 
				`sub_forum`.`fid` = :fid
			GROUP BY (`author`)',
			array( 
				':fid'=>$f3->get('forumData')[0]['fid']
			)
		);

		$f3->set('posters_counted', count($posts));
		if ( count($posts) > 0 ){
			
			// Loop to find min, max, and averages
			$minf = -1; $maxf = -1; $totalf = 0;
			$minl = -1; $maxl = -1; $totall = 0;
			for ($i=0; $i<count($posts); $i++){
				
				$totalf += $posts[$i]['posts'];
				$totall += $posts[$i]['chars'];
				
				if ( ($posts[$i]['posts']<$minf) || ($minf==-1) ) $minf = $posts[$i]['posts'];
				if ( ($posts[$i]['posts']>$maxf) || ($maxf==-1) ) $maxf = $posts[$i]['posts'];
				
				if ( ($posts[$i]['chars']<$minl) || ($minl==-1) ) $minl = $posts[$i]['chars'];
				if ( ($posts[$i]['chars']>$maxl) || ($maxl==-1) ) $maxl = $posts[$i]['chars'];
			}
			
			// TODO: rounding of averages
			
			// Store values for display
			$f3->set('MinL', $minl);
			$f3->set('MaxL', $maxl);
			$f3->set('AvgL', ($totall/count($posts)) );
			
			$f3->set('MinF', $minf);
			$f3->set('MaxF', $maxf);
			$f3->set('AvgF', ($totalf/count($posts)) );
			
		}
		
		return('app/views/configactivity.php');
	}
	
	// Handles submission of edit form
	public static function updateGroupingData($f3, $publicfid){
		
		$f3->get('DB')->exec('
			UPDATE `grouping_activity`
				JOIN `forum_meta` ON `forum_meta`.`typeid` = `grouping_activity`.`id`
			SET `min`=:min
			WHERE `publicId`=:publicfid',
			array( 
				':min'=>$f3->get('POST.min'),
				':publicfid'=>$publicfid,
			)
		);
		$f3->get('DB')->exec('
			UPDATE `grouping_activity`
				JOIN `forum_meta` ON `forum_meta`.`typeid` = `grouping_activity`.`id`
			SET `max`=:max
			WHERE `publicId`=:publicfid',
			array( 
				':max'=>$f3->get('POST.max'),
				':publicfid'=>$publicfid,
			)
		);
		$f3->get('DB')->exec('
			UPDATE `grouping_activity`
				JOIN `forum_meta` ON `forum_meta`.`typeid` = `grouping_activity`.`id`
			SET `lengthCut`=:lengthCut
			WHERE `publicId`=:publicfid',
			array( 
				':lengthCut'=>$f3->get('POST.lengthCut'),
				':publicfid'=>$publicfid,
			)
		);
		$f3->get('DB')->exec('
			UPDATE `grouping_activity`
				JOIN `forum_meta` ON `forum_meta`.`typeid` = `grouping_activity`.`id`
			SET `postCut`=:postCut
			WHERE `publicId`=:publicfid',
			array( 
				':postCut'=>$f3->get('POST.postCut'),
				':publicfid'=>$publicfid,
			)
		);		
		
	}
	
	// Handles a new user joining a forum of this grouping type
	//  decides which sub-forum to assign the user to
	// TODO: 
	public static function registerUser($f3, $forum){
		
		// Get the grouping details
		$details = $f3->get('DB')->exec('
			SELECT * 
			FROM `grouping_activity` 
			WHERE 
				`id` = :gid ',
			array( ':gid'=>$forum[0]['typeid'] )
		);
		$cutp = $details[0]['postCut'];
		$cutl = $details[0]['lengthCut'];
		
		// Find this user's post count and length total
		$posts = $f3->get('DB')->exec('
			SELECT COUNT(`pid`) AS "posts", SUM( CHAR_LENGTH(`content`) ) AS "chars" 
			FROM `posts` 
				JOIN `sub_forum` ON `posts`.`sfid` = `sub_forum`.`sfid`
			WHERE 
				`sub_forum`.`fid` = :fid AND `posts`.`author` = :author
			GROUP BY (`author`)',
			array( 
				':fid'=>$details[0]['fid'],
				':author'=>$f3->get('SESSION.uid')
			)
		);
		
		// Any posts from this user?
		$up = 0;  $ul = 0;
		if ( count($posts)>0 ){
			$up = $posts[0]['posts'];
			$ul = $posts[0]['chars'] / $posts[0]['posts'];
		}

		// Determine quad
		// 3: low posts, high length     4: high posts, high length
		// 1: low posts, low length      2: high posts, low length
		if ( $ul < $cutl ){	
			if ( $up < $cutp ){
				$quad = 1;
			} else {
				$quad = 2;
			}
		} else {
			if ( $up < $cutp ){
				$quad = 3;
			} else {
				$quad = 4;
			}
		}
		
		// Search for groups with less than maximum in matching quad
		$suitableGroups = $f3->get('DB')->exec('
			SELECT `sub_forum`.`sfid`, COUNT(`membership`.`sfid`) AS members
			FROM `sub_forum` 
				INNER JOIN `membership` ON `sub_forum`.`sfid` = `membership`.`sfid`
				INNER JOIN `sub_addon_activity` ON `sub_forum`.`sfid` = `sub_addon_activity`.`sfid`
			WHERE
				`fid`=:fid AND `sub_addon_activity`.`quad`=:quad
			GROUP BY 
				`membership`.`sfid`
			HAVING
				COUNT(`membership`.`sfid`)<:max',
			array( 
				':fid'=>$forum[0]['fid'], 
				':quad'=>$quad,
				':max'=>$details[0]['max']
			)
		);
		
		// If no suitable groups exist, then create one
		// TODO: Use transaction or rollback in F3 to prevent lots of sub-forums if errors
		$lid = -1;
		if ( count($suitableGroups)==0 ){

			// Create a sub-forum
			$f3->get('DB')->exec('
				INSERT INTO `sub_forum`
					(`fid`)
				VALUES
					(:fid)',
				array( ':fid'=>$forum[0]['fid'] )
			);
		
			// Get the last inserted ID
			$result = $f3->get('DB')->exec('SELECT LAST_INSERT_ID()');
			$lid = $result[0]['LAST_INSERT_ID()'];
		
			// Tag it as being of the required quad type
			$f3->get('DB')->exec('
				INSERT INTO `sub_addon_activity`
					(`sfid`, `quad`)
				VALUES
					(:sfid, :quad)',
				array( 
					':sfid'=>$lid,
					':quad'=>$quad 
				)
			);
		
			// Generate public ID
			// Slash is to return to root namespace
			\IdGeneration::generateLabel($f3, $lid, "sub_forum", 10); 
		}
		else {
			$lid = $suitableGroups[0]['sfid'];
		}

		// Add to a suitable group
		// Either found one with less than max users, or created new
		$f3->get('DB')->exec('
			INSERT INTO `membership`
				(`uid`, `sfid`)
			VALUES
				(:uid, :sfid)',
			array( 
				':uid'=>$f3->get('SESSION.uid'), 
				':sfid'=>$lid
			)
		);
	
		return $lid;
	}
	
}


?>