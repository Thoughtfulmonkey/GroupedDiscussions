<?php

	Class Crypto{
		
		public static function hashpassword ( $rawpass ){
			
			$hashed = crypt( $rawpass, "somesecretsalttobesetinanotherway" );
			
			return $hashed;
		}
		
	}

?>