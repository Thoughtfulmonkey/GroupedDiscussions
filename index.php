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
$f3->route('POST /lti', 'MyAuth->lti');				// Accept an LTI request

// Discussion
$f3->route('GET /discussion', 'Discussions->listall');						// Discussion root 
$f3->route('GET /discussion/@fid', 'Discussions->land');					// Display a discussion
$f3->route('POST /discussion/@fid', 'Discussions->addreply');				// add a reply to a post
$f3->route('GET /discussion/@fid/@sfid', 'Discussions->subForumDirect'); 		// Directly access a sub-forum (admin only at this point)
$f3->route('POST /discussion/@fid/@sfid', 'Discussions->subForumPostDirect'); 	// Directly post to a sub-forum (admin only at this point)

// Internal API calls
//$f3->route('GET /grouping/params/@option', 'Grouping->getparams');		// Retrieve parameter list for grouping option
$f3->route('GET /wordcloud/@fid', 'Words->buildCloud');						// Build a word cloud for the chosen discussion forum

// Grouping
$f3->route('GET /discussion/new', 'Grouping->chooseGrouping');				// Define a new discussion (built around grouping choices)
$f3->route('GET /discussion/edit/@fid', 'Grouping->edit');					// Edit discussion details
$f3->route('POST /discussion/update/@fid', 'Grouping->update');				// Updating discussion details
$f3->route('POST /config_grouping/@method', 'Grouping->configGrouping');	// Unique pages to configure each grouping type
$f3->route('POST /build_grouping/@method', 'Grouping->buildGrouping');		// Unique pages to build each grouping type

// Peeking
$f3->route('GET /peek/@fid/@direction', 'Discussions->peek');				// Peek at an adjacent discussion

// Promoting (polinating)
$f3->route('POST /promote', 'Discussions->promote');						// Share a post amongst sibling sub-forums

// Dev calls
// TODO: remove before shipping
$f3->route('GET /purge', 'DevTools->purge');								// Wipe non-essential data for fresh restart


$f3->run();



?>