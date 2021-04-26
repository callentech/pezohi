<?php

namespace App\Services;

class Google
{
	protected $client;

    function __construct()
    {
        $client = new \Google_Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect_uri'));
        $client->setScopes(config('services.google.scopes'));
        $client->setIncludeGrantedScopes(config('services.google.include_granted_scopes'));

        $client->setAccessType('offline');
        $client->setApprovalPrompt('force'); # this line is important when you revoke permission from your app, it will prompt google approval dialogue box forcefully to user to grant offline access
        $this->client = $client;
    }

    public function connectUsing($token): Google
    {
        $this->client->setAccessToken($token);

        return $this;
    }

    public function revokeToken($token = null): bool
    {
        $token = $token ?? $this->client->getAccessToken();
        return $this->client->revokeToken($token);
    }


    public function service($service)
    {
        $classname = "Google_Service_$service";
        return new $classname($this->client);
    }
}
