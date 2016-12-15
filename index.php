<?php

session_start();

// Initialise framework
$f3=require('lib/base.php');
$f3->set('DEBUG',1);
$f3->set('AUTOLOAD','app/controllers/');
$f3->set('APPROOT','/disc');

// Set dev mode
$f3->set('DEV',1);

// Connection to database
$f3->set('DB', new DB\SQL(
    'mysql:host=localhost;port=3306;dbname=discdb',
    'root',
    ''
));

// Using SQL sessions
new \DB\SQL\Session( $f3->get('DB') );

// Routes
$f3->route('GET /',
    function($f3) {
		
		// If not logged in, then re-route to login page
		if ( is_null($f3->get('SESSION.uid')) ) $f3->reroute('/login');
		else{
			// Redirect to discussion list
			$f3->reroute('/discussion/');
		}
    }
);

// Auth links
$f3->route('GET /login', 'MyAuth->showlogin');		// Show login page
$f3->route('POST /login', 'MyAuth->attemptlogin');	// Attempt to login
$f3->route('GET /login/@uid', 'MyAuth->devlogin');	// Attempt to login as a user (dev mode)
$f3->route('GET /logout', 'MyAuth->logout');		// Log the user out

// Discussion
$f3->route('GET /discussion', 'Discussions->listall');				// Discussion root 
$f3->route('GET /discussion/@fid', 'Discussions->land');			// Display a discussion
$f3->route('POST /discussion/@fid', 'Discussions->addreply');		// add a reply to a post

$f3->route('GET /discussion/new', 'Discussions->definenew');		// Define a new discussion
$f3->route('POST /discussion/new', 'Discussions->submitnew');		// Build a new discussion in the DB

// Internal API calls
$f3->route('GET /grouping/params/@option', 'Grouping->getparams');	// Retrieve parameter list for grouping option

// Dev calls
// TODO: remove before shipping
$f3->route('GET /purge', 'DevTools->purge');						// Wipe non-essential data for fresh restart


$f3->run();



?>