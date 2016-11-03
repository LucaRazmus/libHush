<?php
namespace Hush;

use Fabiang\Xmpp\EventListener\AbstractEventListener;
use Fabiang\Xmpp\EventListener\Stream\Authentication\AuthenticationInterface;
use Fabiang\Xmpp\Util\XML;

/**
 * Handler for "plain" authentication mechanism.
 *
 * @package Xmpp\EventListener\Authentication
 */
class BullshitAuth extends AbstractEventListener implements AuthenticationInterface
{

    /**
     * {@inheritDoc}
     */
    public function attachEvents()
    {

    }

    /**
     * {@inheritDoc}
     */
    public function authenticate($username, $password)
    {
        $username = str_replace("@", "!!!", $username);
        $preEncode = "\x00{$username}_w\x00{$password}";
        $authString = XML::quote(base64_encode($preEncode));

        $this->getConnection()->send(
            '<auth xmlns="urn:ietf:params:xml:ns:xmpp-sasl" mechanism="PLAIN">' . $authString . '</auth>'
        );
    }
}
