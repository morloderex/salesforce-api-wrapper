<?php namespace Crunch\Salesforce;

use Crunch\Salesforce\Exceptions\RequestException;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use Crunch\Salesforce\Exceptions\AuthenticationException;

class Client
{
    /**
     * @var string
     */
    protected $salesforceLoginUrl;

    /**
     * @var string
     */
    protected $clientId;

    /**
     * @var string
     */
    protected $clientSecret;

    /**
     * @var AccessToken
     */
    private $accessToken;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var \GuzzleHttp\Client
     */
    private $guzzleClient;


    /**
     * Create a sf client using a client config object or an array of params
     *
     * @param ClientConfigInterface $clientConfig
     * @param \GuzzleHttp\Client    $guzzleClient
     * @throws \Exception
     */
    public function __construct(ClientConfigInterface $clientConfig, \GuzzleHttp\Client $guzzleClient)
    {
        $this->salesforceLoginUrl = $clientConfig->getLoginUrl();
        $this->clientId           = $clientConfig->getClientId();
        $this->clientSecret       = $clientConfig->getClientSecret();

        $this->guzzleClient = $guzzleClient;
    }

    /**
     * Create an instance of the salesforce client using the passed in config data
     *
     * @param $salesforceLoginUrl
     * @param $clientId
     * @param $clientSecret
     * @return Client
     */
    public static function create($salesforceLoginUrl, $clientId, $clientSecret)
    {
        return new self(new ClientConfig($salesforceLoginUrl, $clientId, $clientSecret), new \GuzzleHttp\Client);
    }

    /**
     * Fetch a specific object
     *
     * @param string $objectType
     * @param string $sfId
     * @param array  $fields
     * @return string
     */
    public function getRecord($objectType, $sfId, array $fields)
    {
        $url      = $this->baseUrl . '/services/data/v24.0/sobjects/' . $objectType . '/' . $sfId . '?fields=' . implode(',', $fields);
        $response = $this->makeRequest('get', $url, ['headers' => ['Authorization' => $this->getAuthHeader()]]);

        return json_decode($response->getBody(), true);
    }

    /**
     * Execute an SOQL query and return the result set
     * This will loop through large result sets collecting all the data so the query should be limited
     *
     * @param string|null $query
     * @param string|null $next_url
     * @return array
     * @throws \Exception
     */
    public function search($query = null, $next_url = null)
    {
        if ( ! empty($next_url)) {
            $url = $this->baseUrl . '/' . $next_url;
        } else {
            $url = $this->baseUrl . '/services/data/v24.0/query/?q=' . urlencode($query);
        }
        $response = $this->makeRequest('get', $url, ['headers' => ['Authorization' => $this->getAuthHeader()]]);
        $data     = json_decode($response->getBody(), true);

        $results = $data['records'];
        if ( ! $data['done']) {
            $more_results = $this->search(null, substr($data['nextRecordsUrl'], 1));
            if ( ! empty($more_results)) {
                $results = array_merge($results, $more_results);
            }
        }

        return $results;
    }

    /**
     * Make an update request
     *
     * @param string $object The object type to update
     * @param string $id The ID of the record to update
     * @param array  $data The data to put into the record
     * @return bool
     * @throws \Exception
     */
    public function updateRecord($object, $id, array $data)
    {
        $url = $this->baseUrl . '/services/data/v24.0/sobjects/' . $object . '/' . $id;

        $this->makeRequest('patch', $url, [
            'headers' => ['Content-Type' => 'application/json', 'Authorization' => $this->getAuthHeader()],
            'body'    => json_encode($data)
        ]);

        return true;
    }

    /**
     * Create a new object in salesforce
     *
     * @param string $object
     * @param string $data
     * @return bool
     * @throws \Exception
     */
    public function createRecord($object, $data)
    {
        $url = $this->baseUrl . '/services/data/v24.0/sobjects/' . $object . '/';

        $response     = $this->makeRequest('post', $url, [
            'headers' => ['Content-Type' => 'application/json', 'Authorization' => $this->getAuthHeader()],
            'body'    => json_encode($data)
        ]);
        $responseBody = json_decode($response->getBody(), true);

        return $responseBody['id'];
    }

    /**
     * Delete an object with th specified id
     *
     * @param $object
     * @param $id
     * @return bool
     * @throws \Exception
     */
    public function deleteRecord($object, $id)
    {
        $url = $this->baseUrl . '/services/data/v24.0/sobjects/' . $object . '/' . $id;

        $this->makeRequest('delete', $url, ['headers' => ['Authorization' => $this->getAuthHeader()]]);

        return true;
    }

    /**
     * Complete the oauth process by confirming the code and returning an access token
     *
     * @param $code
     * @param $redirect_url
     * @return array|mixed
     * @throws \Exception
     */
    public function authorizeConfirm($code, $redirect_url)
    {
        $url = $this->salesforceLoginUrl . 'services/oauth2/token';

        $post_data = [
            'grant_type'    => 'authorization_code',
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code'          => $code,
            'redirect_uri'  => $redirect_url
        ];

        $response = $this->makeRequest('post', $url, ['form_params' => $post_data]);

        return json_decode($response->getBody(), true);
    }

    /**
     * Get the url to redirect users to when setting up a salesforce access token
     *
     * @param $redirectUrl
     * @return string
     */
    public function getLoginUrl($redirectUrl)
    {
        $params = [
            'client_id'     => $this->clientId,
            'redirect_uri'  => $redirectUrl,
            'response_type' => 'code',
            'grant_type'    => 'authorization_code'
        ];

        return $this->salesforceLoginUrl . 'services/oauth2/authorize?' . http_build_query($params);
    }

    /**
     * Refresh an existing access token
     *
     * @return AccessToken
     * @throws \Exception
     */
    public function refreshToken()
    {
        $url = $this->salesforceLoginUrl . 'services/oauth2/token';

        $post_data = [
            'grant_type'    => 'refresh_token',
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $this->accessToken->getRefreshToken()
        ];

        $response = $this->makeRequest('post', $url, ['form_params' => $post_data]);

        $update = json_decode($response->getBody(), true);
        $this->accessToken->updateFromSalesforceRefresh($update);

        return $this->accessToken;
    }

    /**
     * @param AccessToken $accessToken
     */
    public function setAccessToken(AccessToken $accessToken)
    {
        $this->accessToken = $accessToken;
        $this->baseUrl     = $accessToken->getApiUrl();
    }

    /**
     * @param string $method
     * @param string $url
     * @param array  $data
     * @return mixed
     * @throws AuthenticationException
     * @throws RequestException
     */
    private function makeRequest($method, $url, $data)
    {
        try {
            $response = $this->guzzleClient->$method($url, $data);

            return $response;
        } catch (GuzzleRequestException $e) {
            
            if ($e->getResponse() === null) {
        		throw $e;
        	}

            //If its an auth error convert to an auth exception
            if ($e->getResponse()->getStatusCode() == 401) {
                $error = json_decode($e->getResponse()->getBody(), true);
                throw new AuthenticationException($error[0]['errorCode'], $error[0]['message']);
            }
            throw new RequestException($e->getMessage(), (string)$e->getResponse()->getBody());
        }

    }

    /**
     * @return string
     */
    private function getAuthHeader()
    {
        if ($this->accessToken === null) {
    		throw new AuthenticationException(0, "Access token not set");
    	}
    	
        return 'Bearer ' . $this->accessToken->getAccessToken();
    }

}
