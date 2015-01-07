<?php
/**
 *
 * CredentialsParser registry
 *
 * @package    Braintree
 * @subpackage Utility
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */

class Braintree_CredentialsParser
{
    private $_clientId;
    private $_clientSecret;
    private $_accessToken;
    private $_environment;
    private $_merchantId;

    public function __construct($attribs)
    {
        foreach ($attribs as $kind => $value) {
            if ($kind == 'clientId') {
                $this->_clientId = $value;
            }
            if ($kind == 'clientSecret') {
                $this->_clientSecret = $value;
            }
            if ($kind == 'accessToken') {
                $this->_accessToken = $value;
            }
        }
        $this->parse();
    }

    public function parse()
    {
        $environments = array();
        if (!empty($this->_clientId)) {
            $environments[] = array('clientId', $this->_parseClientCredential('clientId', $this->_clientId, 'client_id'));
            $environments[] = array('clientSecret', $this->_parseClientCredential('clientSecret', $this->_clientSecret, 'client_secret'));
        }
        if (!empty($this->_accessToken)) {
            $environments[] = array('accessToken', $this->_parseAccessToken());
        }

        $checkEnv = $environments[0];
        foreach ($environments as $env) {
            if ($env[1] !== $checkEnv[1]) {
                throw new Braintree_Exception_Configuration(
                    'Mismatched credential environments: ' . $checkEnv[0] . ' environment is ' . $checkEnv[1] .
                    ' and ' . $env[0] . ' environment is ' . $env[1]);
            }
        }

        $this->_environment = $checkEnv[1];
    }

    private function _parseClientCredential($credentialType, $value, $expectedValuePrefix)
    {
        if (empty($value)) {
            throw new Braintree_Exception_Configuration($credentialType . ' needs to be set.');
        }
        $explodedCredential = explode('$', $value);
        if (sizeof($explodedCredential) != 3) {
            throw new Braintree_Exception_Configuration('Incorrect ' . $credentialType . ' format. Expected: type$environment$token');
        }

        $gotValuePrefix = $explodedCredential[0];
        $environment = $explodedCredential[1];
        $token = $explodedCredential[2];

        if ($gotValuePrefix != $expectedValuePrefix) {
            throw new Braintree_Exception_Configuration('Value passed for ' . $credentialType . ' is not a ' . $credentialType);
        }

        return $environment;
    }

    private function _parseAccessToken()
    {
        $accessTokenExploded = explode('$', $this->_accessToken);
        if (sizeof($accessTokenExploded) != 4) {
            throw new Braintree_Exception_Configuration('Incorrect accessToken syntax. Expected: type$environment$merchant_id$token');
        }

        $gotValuePrefix = $accessTokenExploded[0];
        $environment = $accessTokenExploded[1];
        $merchantId = $accessTokenExploded[2];
        $token = $accessTokenExploded[3];

        if ($gotValuePrefix != 'access_token') {
            throw new Braintree_Exception_Configuration('Value passed for accessToken is not an accessToken');
        }

        $this->_merchantId = $merchantId;
        return $environment;
    }

    public function getClientId()
    {
        return $this->_clientId;
    }

    public function getClientSecret()
    {
        return $this->_clientSecret;
    }

    public function getAccessToken()
    {
        return $this->_accessToken;
    }

    public function getEnvironment()
    {
        return $this->_environment;
    }

    public function getMerchantId()
    {
        return $this->_merchantId;
    }
}