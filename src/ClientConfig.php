<?php namespace Crunch\Salesforce;

class ClientConfig implements ClientConfigInterface
{

    /**
     * @var
     */
    private $loginUrl;
    /**
     * @var
     */
    private $clientId;
    /**
     * @var
     */
    private $clientSecret;
    /**
     * @var string
     */
    private $version;

    public function __construct($loginUrl, $clientId, $clientSecret, $version = "v37.0")
    {
        $this->loginUrl     = $loginUrl;
        $this->clientId     = $clientId;
        $this->clientSecret = $clientSecret;
        $this->version      = $version;
    }

    /**
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->loginUrl;
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @return string
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * Getter for version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }


}