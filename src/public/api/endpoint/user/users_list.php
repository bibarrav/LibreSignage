<?php
/*
*  ====>
*
*  Get a list of the existing usernames.
*
*  **Request:** GET
*
*  Return value
*    * users = An array of usernames.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/config.php');
require_once(LIBRESIGNAGE_ROOT.'/api/APIInterface.php');

APIEndpoint::GET(
	[
		'APIAuthModule' => [
			'cookie_auth' => FALSE
		],
		'APIRateLimitModule' => []
	],
	function($req, $resp, $module_data) {
		$ret = ['users' => []];
		foreach (user_array() as $u) { $ret['users'][] = $u->get_name(); }
		return $ret;
	}
);
