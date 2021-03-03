<?php

namespace App\Services;

class Google
{
	protected $client;

	public function __construct()
	{
		$client = new \Google_Client();
		$client->setClientId(config('services.google.client_id'));
		$client->setClientSecret(config('services.google.client_secret'));
		$client->setRedirectUri(config('services.google.redirect'));
		$client->setScopes(config('services.google.scopes'));
		$client->setApprovalPrompt(config('services.google.approval_prompt'));
		$client->setAccessType(config('services.google.access_type'));
		$client->setIncludeGrantedScopes(config('services.google.include_granted_scopes'));
		$this->client = $client;
	}

	public function revokeToken($token = null)
	{
		if (isset($token)) {
		    $token = $token;
		} else {
		    $token = $this->client->getAccessToken();
		}
		return $this->client->revokeToken($token);
	}

	public function connectUsing($token)
	{
		$this->client->setAccessToken($token);
		return $this;
	}

	public function service($service)
	{
		$className = "Google_Service_$service";
		return new $className($this->client);
	}

	public function __call($method, $args)
	{
		if (!method_exists($this->client, $method)) {
			throw new Exception("Call to undefined method '{$method}'", 1);
		}
		return call_user_func_array([$this->client, $method], $args);
	}
}