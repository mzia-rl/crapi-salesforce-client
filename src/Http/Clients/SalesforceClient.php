<?php

namespace Canzell\Http\Clients;

use GuzzleHttp\Client as Guzzle;

class SalesforceClient extends HttpClient
{

    private $config;
    private $issuedAt;
    private $instanceUrl;
    private $token;

    public function __construct()
    {

        $this->config = config('salesforce-client');
        $this->authenticate();

        // Configure Client
        $client = new Guzzle([
            'base_uri' => "{$this->instanceUrl}/services/data/{$this->config['api_version']}/",
            'headers' => [
                'Authorization' => "Bearer {$this->token}",
            ]
        ]);

        parent::__construct($client);
    }

    private function authenticate()
    {
        // Fetch access token and instance URL from Salesforce
        $res = (new Guzzle)->post(
            'https://login.salesforce.com/services/oauth2/token',
            [
                'form_params' => [
                    'grant_type' => 'password',
                    'client_id' => $this->config['client_id'],
                    'client_secret' => $this->config['client_secret'],
                    'username' => $this->config['user']['username'],
                    'password' => $this->config['user']['password'] . $this->config['security_token'],
                ]
            ]
        );
        $body = json_decode($res->getBody());

        // Verify access token
        $hash = hash_hmac(
            'sha256', 
            $body->id . $body->issued_at, 
            $this->config['client_secret'], 
            true
        );
        if (base64_encode($hash) !== $body->signature) throw new \Exception('Salesforce access token is invalid');

        $this->instanceUrl = $body->instance_url;
        $this->issuedAt = $body->issued_at;
        $this->token = $body->access_token;
    }


    //
    //  Salesforce Client Specific Methods
    //

    // Run a SOQL Query
    public function query($query)
    {
        return $this->get("query?q=$query");
    }

    // Update an sObject based on included attributes and id
    public function update($object, $values = null)
    {
        $type = $object->attributes->type;
        $id = $object->Id;
        $object = array_diff_key((array) $object, array_flip(['Id', 'attributes']));
        $this->patch("sobjects/$type/$id", ['json' => $values ? $values : $object]);
    }

    // Fetch a single user and all its fields by id
    public function User($id)
    {
        return $this->get("sobjects/User/$id");
    }

    // Fetch all agents and a set of default fields (specifiy additional fields with an array of field names)
    public function agents($additional = [])
    {
        // Remove random users from request.
        $blacklist = 'id != \'0051a000000XnJoAAK\' and id != \'0051a000001tLhwAAE\'';
        
        // Include these fields on the user.
        $fields = [
            'id',
            'username',
            'name',
            'email',
            'profile.name',
            'Salesforce_Type__c',
            'Agent_Override__c',
            'Agent_production__c',
            'Do_not_send_leads__c',
            'Free_pass__c',
            'Consider_for_leads__c',
            'Eligible_for_preferred_leads__c',
            'Ineligible_Reason__c',
            'New_Conversion_Rate_L5M__c',
            'New_Appt_Conversion_Rate__c',
            'New_L5M_Given__c',
            'New_L5M_Shown__c',
            'New_L5M_Not_Approved__c'
        ];
        $fields = array_merge($fields, $additional);

        // Build query
        $query = 'SELECT '.implode(', ', $fields).' from user WHERE (IsActive = true OR Current_Agent__c = true) and '.$blacklist;

        // Make request
        return $this->query($query)->records;
    }



}