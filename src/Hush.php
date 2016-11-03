<?php
namespace Hush;

use Fabiang\Xmpp\Protocol\Roster;
use Fabiang\Xmpp\Protocol\Presence;
use Fabiang\Xmpp\Protocol\Message;
use Fabiang\Xmpp\Options;
use \Fabiang\Xmpp\Client;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;
use Hackzilla\PasswordGenerator\Generator\ComputerPasswordGenerator;

class Hush
{
    protected $username;
    protected $password;
    protected $usernameTarget;
    protected $address = "tls://im.lovense.com:9529";
    protected $logger;
    protected $options;

    /** @var Client */
    protected $client;

    protected $isConnected = false;

    public function __construct()
    {
        $this->options = new Options();

        $this->options->setTimeout(5);

        $this->options->setAuthenticationClasses(['plain' => "\\" . BullshitAuth::class]);
    }

    public function generateWierdAssKey(){

        $generator = new ComputerPasswordGenerator();

        $generator
            ->setOptionValue(ComputerPasswordGenerator::OPTION_UPPER_CASE, true)
            ->setOptionValue(ComputerPasswordGenerator::OPTION_LOWER_CASE, true)
            ->setOptionValue(ComputerPasswordGenerator::OPTION_NUMBERS, true)
            ->setOptionValue(ComputerPasswordGenerator::OPTION_SYMBOLS, false)
            ->setOptionValue(ComputerPasswordGenerator::OPTION_LENGTH, 32)
        ;

        $wierdAssKey = $generator->generatePassword();

        return $wierdAssKey;
    }

    public function setConnected($isConnected)
    {
        $this->isConnected = $isConnected;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $address
     * @return Hush
     */
    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    public function getUsernameMangled()
    {
        return str_replace("@", "!!!", $this->getUsername());
    }

    /**
     * @param mixed $username
     * @return Hush
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     * @return Hush
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param mixed $logger
     * @return Hush
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    public static function Factory($username = null, $password = null, $targetUsername = null)
    {
        $hush = new Hush();
        if ($username) {
            $hush->setUsername($username);
        }
        if ($password) {
            $hush->setPassword($password);
        }
        if ($targetUsername){
            $hush->setUsernameTarget($targetUsername);
        }
        return $hush;
    }

    /**
     * @return Options
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param Options $options
     * @return Hush
     */
    public function setOptions(Options $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param Client $client
     * @return Hush
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
        return $this;
    }

    public function connect()
    {
        $this->setConnected(true);

        $this->options
            ->setAddress($this->getAddress())
            ->setUsername($this->getUsername())
            ->setPassword($this->getPassword());

        if($this->getLogger()) {
            $this->options->setLogger($this->getLogger());
        }

        $this->client = new Client($this->options);

        $this->client->connect();
        // fetch roster list; users and their groups
        $this->client->send(new Roster);
        // set status to online
        $this->client->send(new Presence);

        return $this;
    }

    public function setBuzzSpeed($speed)
    {
        if(!$this->isConnected){
            $this->connect();
        }
        $buzzCommand = [
            "id" => $this->generateWierdAssKey(),
            "fromJid" => "{$this->getUsernameMangled()}_w@im.lovense.com",
            "isAutoPlay" => false,
            "text" => (string)$speed,
            "toJid" => "{$this->getUsernameTargetMangled()}_w@im.lovense.com",
            "type" => "toy",
        ];

        $message = new Message;
        $message->setMessage(base64_encode(json_encode($buzzCommand)))
            ->setTo("{$this->getUsernameTargetMangled()}_w@im.lovense.com");
        $this->client->send($message);
    }

    /**
     * @return mixed
     */
    public function getUsernameTarget()
    {
        return $this->usernameTarget;
    }

    public function getUsernameTargetMangled()
    {
        return str_replace("@", "!!!", $this->getUsernameTarget());
    }

    /**
     * @param mixed $usernameTarget
     * @return Hush
     */
    public function setUsernameTarget($usernameTarget)
    {
        $this->usernameTarget = $usernameTarget;
        return $this;
    }

    public function disconnect()
    {
        $this->client->disconnect();
        $this->setConnected(false);
    }

    public function __destruct()
    {
        if ($this->isConnected) {
            $this->disconnect();
        }
    }
}