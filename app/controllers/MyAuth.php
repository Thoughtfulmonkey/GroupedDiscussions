<?php

// Authorisations
class MyAuth {

	// Helper function to load user list during development
	private function loaduserlist($f3){
		
		// Show login options if in dev mode
		if ( $f3->get('DEV') ){
			
			// Find all non-admin users
			$f3->set('userlist', $f3->get('DB')->exec('SELECT username, uid FROM user WHERE type=1'));
			
		}
	}

	// Display the login screen
	function showlogin($f3) {
		
		$this->loaduserlist($f3);
		
		// Show login page
		echo Template::instance()->render('app/views/login.php');
	}

	// Process attempt to login
	function attemptlogin($f3) {
		
		// Get the hashed version of the user's password
		$hashedpass = Crypto::hashpassword( $f3->get('POST.password') );
		
		// Create mapper object from users table in DB
		$user=new DB\SQL\Mapper( $f3->get('DB') , 'user' );
		
		// Attempt to load the user from login details provided
		$user->load(array('username=? AND password=?', $f3->get('POST.username'), $hashedpass));
		
		// Was a user found?
		if ( !is_null($user->uid) ){
			
			// Store user session details
			$f3->set('SESSION.uid', $user->uid);
			$f3->set('SESSION.name', $user->username);
			$f3->set('SESSION.type', $user->type);
			
			// Redirect to root
			$f3->reroute('/');
		}
		else {
			// Reload login page, but with error
			$f3->set('loginmessage', 'Incorrect login details');
			echo Template::instance()->render('app/views/login.php');
		}
		
	}
	
	// Process attempt to switch to a user
	function devlogin($f3) {
		
		// Attempt user switch if in dev mode
		if ( $f3->get('DEV') ){
			
			// Create mapper object from users table in DB
			$user=new DB\SQL\Mapper( $f3->get('DB') , 'user' );
			
			// Attempt to load the user from uid provided
			$user->load( array('uid=? AND type=1', $f3->get('PARAMS.uid')) );
			
			// Was a user found?
			if ( !is_null($user->uid) ){
				
				// Store user session details
				$f3->set('SESSION.uid', $user->uid);
				$f3->set('SESSION.name', $user->username);
				$f3->set('SESSION.type', $user->type);
				
				// Redirect to root
				$f3->reroute('/');
			}
			else {
				// User doesn't exist or is not plain user type
				// Don't provide any error message that would be useful to a hacker
				echo Template::instance()->render('app/views/login.php');
			}
		} else {
			// Not in dev mode
			// Don't provide any error message that would be useful to a hacker
			echo Template::instance()->render('app/views/login.php');
		}
	}
	
	// Logging out
	function logout($f3){
		
		$this->loaduserlist($f3);
		
		// Store user session details
		$f3->set('SESSION.uid', NULL);
		
		// Reload login page, with logged out message
		$f3->set('loginmessage', 'You have successfully logged out');
		echo Template::instance()->render('app/views/login.php');
	}
}
