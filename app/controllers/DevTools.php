<?php

// Tools to help development
// TODO: remove before shipping
class DevTools{
	
	// Clear database of non-essential data
	function purge($f3){
	
		echo "<p><strong>Purge start</strong></p>";
	
		$f3->get('DB')->exec('
			DELETE FROM `forum_meta` WHERE 1;
		');
		echo "<p>forum_meta purged</p>";
		
		
		echo "<p>** Grouping tables **</p>";
		
		$f3->get('DB')->exec('
			DELETE FROM `grouping_silo` WHERE 1;
		');
		echo "<p>grouping_silo purged</p>";
		
		echo "<p>** Is that all the grouping tables? **</p>";
		
		
		$f3->get('DB')->exec('
			DELETE FROM `membership` WHERE 1;
		');
		echo "<p>membership purged</p>";
		
		$f3->get('DB')->exec('
			DELETE FROM `posts` WHERE 1;
		');
		echo "<p>posts purged</p>";
		
		$f3->get('DB')->exec('
			DELETE FROM `sub_forum` WHERE 1;
		');
		echo "<p>sub_forum purged</p>";
		
		
		echo "<p><strong>Purge complete</strong></p>";
	}
	
}

?>