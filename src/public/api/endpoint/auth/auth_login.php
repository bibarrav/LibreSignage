<?php
/*
*  ====>
*
*  Login using the authentication system.
*
*  **Request:** POST, application/json
*
*  POST parameters
*    * username    = Username
*    * password    = Password
*    * who         = A string that identifies the caller.
*    * permanent   = Create permanent session. Optional, FALSE by default.
*
*  Return value
*    * user    = Current user data.
*    * session = Current session data.
*
*  <====
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/../common/php/config.php');
require_once(LIBRESIGNAGE_ROOT.'/api/APIInterface.php');

APIEndpoint::POST(
	[
		'APIJsonValidatorModule' => [
			'schema' => [
				'type' => 'object',
				'properties' => [
					'username' => ['type' => 'string'],
					'password' => ['type' => 'string'],
					'who' => ['type' => 'string'],
					'permanent' => ['type' => 'boolean', 'default' => FALSE]
				],
				'required' => ['username', 'password', 'who']
			]
		]
	],
	function($req, $resp, $module_data) {
		$params = $module_data['APIJsonValidatorModule'];

		$user = auth_creds_verify($params->username, $params->password);
		if ($user === NULL) {
			throw new APIException(
				'Invalid credentials.',
				HTTPStatus::UNAUTHORIZED
			);
		}

		// Try to create a new session.
		$tmp = preg_match('/[^a-zA-Z0-9_-]/', $params->who);
		if ($tmp === 1) {
			throw new APIException(
				"Invalid characters in the 'who' parameter.",
				HTTPStatus::BAD_REQUEST
			);
		} else if ($tmp === FALSE) {
			throw new APIException(
				"preg_match() failed.",
				HTTPStatus::INTERNAL_SERVER_ERROR
			);
		}
		$data = $user->session_new(
			$params->who,
			$req->getClientIp(),
			$params->permanent
		);
		$user->write();

		// Set the session token cookie.
		setcookie(
			$name = 'session_token',
			$value = $data['token'],
			$expire = PERMACOOKIE_EXPIRE,
			$path = '/'
		);

		return [
			'user' => $user->export(FALSE, FALSE),
			'session' => array_merge(
				$data['session']->export(FALSE, FALSE),
				[ 'token' => $data['token'] ]
			)
		];
	}
);
