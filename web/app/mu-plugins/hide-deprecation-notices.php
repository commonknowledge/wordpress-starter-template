<?php

/*
Plugin Name:  Hide Deprecation Notices
Description:  Stop PHP deprecation notices from plugins and themes being reported
Version:      1.0.0
Author:       Common Knowledge
Author URI:   https://commonknowledge.coop/
Text Domain:  commonknowledge
License:      MIT License
*/

// WordPress sets error_reporting(E_ALL) when WP_DEBUG is on. Must-use plugins
// load after that and before plugins and themes, so this is the earliest point
// to drop deprecations. Displayed in development, they print before REST API
// JSON and break the block editor.
error_reporting(error_reporting() & ~E_DEPRECATED);
