<?php

namespace Morloderex\Salesforce;

class ClientConfig implements ClientConfigInterface
{
    /**
     * @var string
     */
    private $redirectUrl;

    /**
     * @var string
     */
    private $loginUrl;

    /**
     * @var string
     */
    private $clientId;
    /**
     * @var string
     */
    private $clientSecret;

    /**
     * @var string
     */
    private $version;

    /**
     * ClientConfig constructor.
     * @param string $loginUrl
     * @param string $clientId
     * @param string $clientSecret
     * @param string $redirectUrl
     * @param string $version
     */
    public function __construct(
        string $loginUrl,
        string $clientId,
        string $clientSecret,
        string $redirectUrl,
        string $version = 'v37.0'
    ) {
        $this->loginUrl     = $loginUrl;
        $this->clientId     = $clientId;
        $this->clientSecret = $clientSecret;
        $this->version      = $version;
        $this->redirectUrl  = $redirectUrl;
    }

    /**
     * @return string
     */
    public function getRedirectUrl(): string
    {
        return $this->redirectUrl;
    }

    /**
     * @return string
     */
    public function getLoginUrl(): string
    {
        return $this->loginUrl;
    }

    /**
     * @return string
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * @return string
     */
    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    /**
     * Getter for version
     *
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }
}
