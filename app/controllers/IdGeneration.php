<?php

class IdGeneration{

	// Unique label generation
	static function generateLabel($f3, $id, $table, $length){
		
		$unique = false;
		$label = null;
		do{
			
			$publicId = IdGeneration::alphaLabel($length);
			
			// See if this publicId has already been used
			$search = $f3->get('DB')->exec("
				SELECT `publicId`
				FROM `$table`
				WHERE `publicId`=:publicId",
				array(
					':publicId'=>$publicId
				)
			);
			
			if ( count($search)==0 ) $unique = true;
			
		} while (!unique);
		
		// Add the public ID to the record
		if ($table == "forum_meta"){
			
			$f3->get('DB')->exec('
				UPDATE `forum_meta`
				SET `publicId`=:publicId
				WHERE `fid`=:id',
				array(
					':publicId'=>$publicId,
					':id'=>$id
				)
			);
		} else if ($table == "sub_forum"){
			
			$f3->get('DB')->exec('
				UPDATE `sub_forum`
				SET `publicId`=:publicId
				WHERE `sfid`=:sfid',
				array(
					':publicId'=>$publicId,
					':sfid'=>$id
				)
			);
		} else if ($table == "posts"){
			$f3->get('DB')->exec('
				UPDATE `posts`
				SET `publicId`=:publicId
				WHERE `pid`=:pid',
				array(
					':publicId'=>$publicId,
					':pid'=>$id
				)
			);
		}
	}
	
	//
	static function alphaLabel($l){
		
		$set = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
		$label = "";
		
		for ($i=0; $i<$l; $i++){
			
			$p = rand(0, strlen($set));
			$label = $label.substr($set, $p, 1);
		}
		return $label;
	}
	
}

?>