<?php
class PushMessage
{

    private $application = "API";

    private $title;

    private $content;

    private $url;

    public function setApplication($application)
    {
        $this->application = $application;
    }

    public function getApplication()
    {
        return $this->application;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getUrl()
    {
        return $this->url;
    }
}

class PushMessenger
{
    private $server = "http://ipush.me";

    private $userId;

    private $secretToken;

    private $lastError;

    public function __construct($userId, $secretToken)
    {
        $this->userId = $userId;
        $this->secretToken = $secretToken;
    }

    public function verify()
    {
        $url = sprintf($this->server . "/api-verify?uid=%d&secret=%s",
                       $this->userId,
                       $this->secretToken);

        $response = self::get($url);

        $o = json_decode($response);

        if (intval($o->code) == 200) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $message PushMessage
     * @return string
     */
    public function push($message)
    {
        $url = sprintf($this->server . "/api-push?uid=%d&secret=%s&app=%s&title=%s&content=%s&url=%s", $this->userId, $this->secretToken,
                       urlencode($message->getApplication()),
                       urlencode($message->getTitle()),
                       urlencode($message->getContent()),
                       urlencode($message->getUrl()));


        return self::get($url, 1);
    }

    private function get($url, $timeout = 30)
    {
        $content = null;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        $content = curl_exec($ch);
        if (curl_errno($ch)) {
            $content = null;
            $this->lastError = curl_error($ch);
        }
        curl_close($ch);

        return $content;
    }

    public function getLastError()
    {
        return $this->lastError;
    }
}

?>
