<?php

namespace OpenproviderPremiumDns\lib\xmlapihelper;

/**
 * Class Request
 * OpenProvider PremiumDNS module
 *
 * @copyright Copyright (c) Openprovider 2025
 */

class Request
{
    protected $cmd;
    protected $args;
    protected $username;
    protected $password;
    protected $client;

    public function __construct()
    {
        $this->client = \OpenproviderPremiumDns\lib\xmlapihelper\APIConfig::$moduleVersion;
    }

    public function getRaw()
    {
        $dom = new \DOMDocument('1.0', \OpenproviderPremiumDns\lib\xmlapihelper\APIConfig::$encoding);

        $credentialsElement = $dom->createElement('credentials');
        $usernameElement = $dom->createElement('username');
        $usernameElement->appendChild(
                $dom->createTextNode(mb_convert_encoding($this->username, \OpenproviderPremiumDns\lib\xmlapihelper\APIConfig::$encoding))
        );
        $credentialsElement->appendChild($usernameElement);

        $passwordElement = $dom->createElement('password');
        $passwordElement->appendChild(
                $dom->createTextNode(mb_convert_encoding($this->password, \OpenproviderPremiumDns\lib\xmlapihelper\APIConfig::$encoding))
        );
        $credentialsElement->appendChild($passwordElement);

        $clientElement = $dom->createElement('client');
        $clientElement->appendChild(
                $dom->createTextNode(mb_convert_encoding($this->client, \OpenproviderPremiumDns\lib\xmlapihelper\APIConfig::$encoding))
        );
        $credentialsElement->appendChild($clientElement);

        $initiator = \OpenproviderPremiumDns\lib\xmlapihelper\APIConfig::getInitiator();
        $clientElement = $dom->createElement('initiator');
        $clientElement->appendChild(
            $dom->createTextNode(mb_convert_encoding($initiator, \OpenproviderPremiumDns\lib\xmlapihelper\APIConfig::$encoding))
        );
        $credentialsElement->appendChild($clientElement);

        $rootElement = $dom->createElement('openXML');
        $rootElement->appendChild($credentialsElement);

        $rootNode = $dom->appendChild($rootElement);
        $cmdNode = $rootNode->appendChild(
                $dom->createElement($this->getCommand())
        );

        \OpenproviderPremiumDns\lib\xmlapihelper\APITools::convertPhpObjToDom($this->args, $cmdNode, $dom);

        return $dom->saveXML();
    }
    
    public function setArgs($args)
    {
        $this->args = $args;
        return $this;
    }

    public function setCommand($cmd)
    {
        $this->cmd = $cmd;
        return $this;
    }

    public function getCommand()
    {
        return $this->cmd;
    }

    public function setAuth($args)
    {
        $this->username = isset($args["username"]) ? $args["username"] : null;
        $this->password = isset($args["password"]) ? $args["password"] : null;

        return $this;
    }
}
