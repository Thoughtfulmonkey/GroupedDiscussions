<?php

namespace grouptypes;

class Prompt {
	
	const TYPE_ID = 3;
	
	// Returns address of view to handle grouping type configuration
	public static function getConfigView($f3){
			
		return('app/views/configprompt.php');
	}
	
	// Handles submission of the configuration form
	public static function buildGrouping($f3){
		
		// Extract post data (with array keys)
		$post = $f3->get('POST');
		$keys = array_keys($post);

		// Copy option entries to new array
		$options = [];
		for ($i=0; $i<count( $keys ); $i++){
			
			if ( strpos($keys[$i],"optiontxt") !== false){
				
				$name = $post[ $keys[$i] ];
				array_push($options, $name);
			}
		}
		
		// Encode option array as json
		$json = json_encode($options);

		// Store json encoded options in array
		$f3->get('DB')->exec('
			INSERT INTO `grouping_prompt`
				(`max`, `option_prompt`, `options`)
			VALUES
				(:max, :option_prompt, :options)',
			array( 
				$f3->get('POST.max'),
				':option_prompt'=>$post['optPrompt'],
				':options'=>$json
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
		$config =  $f3->get('DB')->exec('
			SELECT *
			FROM `grouping_prompt`
			WHERE `id`=:typeid',
			array( 
				':typeid'=>$f3->get('forumData')[0]['typeid']
			)
		);

		// Extract and decode json, then add to data array
		$groupingData = [];
		$groupingData['options'] = json_decode($config[0]['options']);
		$groupingData['numoptions'] = count( $groupingData['options'] );
		$groupingData['option_prompt'] = $config[0]['option_prompt'];
		$groupingData['max'] = $config[0]['max'];
		
		$f3->set('groupingData', $groupingData);

		return('app/views/configprompt.php');
	}
	
	// Handles submission of edit form
	public static function updateGroupingData($f3, $publicfid){
		
		$f3->get('DB')->exec('
			UPDATE `grouping_prompt`
				JOIN `forum_meta` ON `forum_meta`.`typeid` = `grouping_prompt`.`id`
			SET `max`=:max
			WHERE `publicId`=:publicfid',
			array( 
				':max'=>$f3->get('POST.max'),
				':publicfid'=>$publicfid,
			)
		);
		$f3->get('DB')->exec('
			UPDATE `grouping_prompt`
				JOIN `forum_meta` ON `forum_meta`.`typeid` = `grouping_prompt`.`id`
			SET `option_prompt`=:option_prompt
			WHERE `publicId`=:publicfid',
			array( 
				':option_prompt'=>$f3->get('POST.optPrompt'),
				':publicfid'=>$publicfid,
			)
		);
		
		// Extract post data (with array keys)
		$post = $f3->get('POST');
		$keys = array_keys($post);

		// Copy option entries to new array
		$options = [];
		for ($i=0; $i<count( $keys ); $i++){
			
			if ( strpos($keys[$i],"optiontxt") !== false){
				
				$name = $post[ $keys[$i] ];
				array_push($options, $name);
			}
		}
		
		// Encode option array as json
		$json = json_encode($options);
		
		$f3->get('DB')->exec('
			UPDATE `grouping_prompt`
				JOIN `forum_meta` ON `forum_meta`.`typeid` = `grouping_prompt`.`id`
			SET `options`=:options
			WHERE `publicId`=:publicfid',
			array( 
				':options'=>$json,
				':publicfid'=>$publicfid,
			)
		);
		
		
	}
	
	// Handles a new user joining a forum of this grouping type
	//  decides which sub-forum to assign the user to
	// TODO: 
	public static function registerUser($f3, $forum){
		
		$lid = null;
		
		// Load the grouping config
		$config =  $f3->get('DB')->exec('
			SELECT *
			FROM `grouping_prompt`
			WHERE `id`=:typeid',
			array( 
				':typeid'=>$forum[0]['typeid']
			)
		);
		
		// Stage 1, posing the question
		if ( null === $f3->get('POST.promptChoice') ){
			
			// Extract and decode options		
			$f3->set('options', json_decode($config[0]['options']) );
			$f3->set('prompt', $config[0]['option_prompt']);

			echo \Template::instance()->render('app/views/promptchoose.php');
		
		} else {
			
			$suitableGroups = $f3->get('DB')->exec('
				SELECT `sub_forum`.`sfid`, COUNT(`membership`.`sfid`) AS members
				FROM `sub_forum` 
					INNER JOIN `membership` ON `sub_forum`.`sfid` = `membership`.`sfid`
					INNER JOIN `sub_addon_prompt` ON `sub_forum`.`sfid` = `sub_addon_prompt`.`sfid`
				WHERE
					`fid`=:fid AND `sub_addon_prompt`.`optiontxt`=:option
				GROUP BY 
					`membership`.`sfid`
				HAVING
					COUNT(`membership`.`sfid`)<:max',
				array( 
					':fid'=>$forum[0]['fid'], 
					':option'=>$f3->get('POST.promptChoice'),
					':max'=>$config[0]['max']
				)
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
			
				// Get the last inserted ID
				$result = $f3->get('DB')->exec('SELECT LAST_INSERT_ID()');
				$lid = $result[0]['LAST_INSERT_ID()'];
			
				// Tag it as being of the required quad type
				$f3->get('DB')->exec('
					INSERT INTO `sub_addon_prompt`
						(`sfid`, `optiontxt`)
					VALUES
						(:sfid, :option)',
					array( 
						':sfid'=>$lid,
						':option'=>$f3->get('POST.promptChoice') 
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
		}
		
		return $lid;
		
	}
	
}


?>