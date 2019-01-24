<?php namespace Crunch\Salesforce;

interface ClientConfigInterface {

    /**
     * @return string
     */
    public function getLoginUrl();

    /**
     * @return string
     */
    public function getClientId();

    /**
     * @return string
     */
    public function getClientSecret();

    /**
     * Version of the API
     * @return string
     */
    public function getVersion();
}