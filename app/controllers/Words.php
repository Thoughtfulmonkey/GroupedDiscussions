<?php

class Words{
	
	const MIN_SIZE = 4;
	const MIN_COUNT = 3;
	
	//https://en.wikipedia.org/wiki/Most_common_words_in_English (top 50 included)
	private $common = ["the", "be", "to", "of", "and", "a", "in", "that", "have", "i", "it", "for", "not", "on", "with", "he", "as", "you", "do", "at", "this", "but", "his", "by", "from", "they", "we", "say", "her", "she", "or", "an", "will", "my", "one", "all", "would", "there", "their", "what"];
	
	//
	function buildCloud($f3){
		
		// Sanitise forum id on address bar
		$fid = $f3->get('PARAMS.fid');
		$fid = $f3->scrub($fid);
		
		// Pull all posts (across all groups) for this forum id
		$posts = $f3->get('DB')->exec('
			SELECT `posts`.`content` 
			FROM `posts` 
				JOIN `sub_forum` ON `posts`.`sfid` = `sub_forum`.`sfid`
			WHERE 
				`sub_forum`.`fid`=:fid',
			array( ':fid'=>$fid )
		);
		
		// To store an associated array of words
		$cloud = [];
		$biggest = -1;
		
		// Loop through all posts and build word list
		for ($i=0; $i<count($posts); $i++){
			
			$words = strip_tags( $posts[$i]["content"] );		// Strip HTML tags
			$words = preg_replace('/[^a-z]+/i', ' ', $words); 	// Keep only letters
			$words = strtolower($words);						// Make all lower case
			
			// Loop through each word in the posts
			$wlist = explode(" ", $words);
			for ($w=0; $w<count($wlist); $w++){
			
				$include = true;
			
				if ( strlen($wlist[$w]) < Words::MIN_SIZE ) $include = false;	// Basic length check
				if ( in_array($wlist[$w], $this->common) ) $include = false;	// Common word check
				// Other checks
				
				if ($include){
					
					// If stored before, then increment, else store as 1 count
					if ( isset($cloud[$wlist[$w]]) ) {
						$cloud[$wlist[$w]]++;
						if ( $cloud[$wlist[$w]] > $biggest) $biggest = $cloud[$wlist[$w]];
					}
					else $cloud[$wlist[$w]] = 1;
				}
			}
			
		}
		
		// Output
		while ($wordCount = current($cloud)) {
			if ($wordCount > Words::MIN_COUNT) {
				
				echo "<span style='font-size:";
				
				echo floor( 10 + ( (60/$biggest)*$wordCount ) );
				
				
				echo "px'>";
				echo key($cloud);
				
				echo "</span> ";
			}
			next($cloud);
		}
		
	}
	
}

?>