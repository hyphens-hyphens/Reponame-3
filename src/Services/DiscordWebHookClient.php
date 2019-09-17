<?php

namespace T2G\Common\Services;

use GuzzleHttp\Client;
use Illuminate\Log\LogManager;

/**
 * Class DiscordWebhook
 */
class DiscordWebHookClient
{
    const EMBED_COLOR_DEBUG = 10395294;
    const EMBED_COLOR_INFO = 5025616;
    const EMBED_COLOR_NOTICE = 6323595;
    const EMBED_COLOR_WARNING = 16771899;
    const EMBED_COLOR_ERROR = 16007990;
    const EMBED_COLOR_CRITICAL = 16007990;
    const EMBED_COLOR_ALERT = 16007990;
    const EMBED_COLOR_EMERGENCY = 16007990;

    protected $webHookUrl;

    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * @var LogManager
     */
    protected $logger;

    /**
     * DiscordHandler constructor.
     *
     * @param $webHookUrl
     */
    public function __construct($webHookUrl)
    {
        $this->client = new Client();
        $this->webHookUrl = $webHookUrl;
        $this->logger = app(LogManager::class);
    }

    /**
     * @param $message
     *
     * @return \Psr\Http\Message\ResponseInterface|null
     */
    public function send($message)
    {
        $content = [
            'content' => $message,
        ];
        if(env('APP_ENV') != 'prod') {
            $this->logger->debug("DiscordWebhook message sent.", $content);
            return null;
        }

        return $this->execute($content);
    }

    /**
     * @param     $title
     * @param     $message
     * @param int $color
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    public function sendWithEmbed($title, $message, $color = self::EMBED_COLOR_INFO)
    {
        $content = [
            "embeds" => [
                [
                    "title"       => $title,
                    "description" => $message,
                    "timestamp"   => (new \DateTime())->setTimezone(new \DateTimeZone('UTC'))->format('c'),
                    "color"       => $color,
                ],
            ],
        ];

        return $this->execute($content);
    }

    /**
     * @param $content
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function execute($content)
    {
        return $this->client->post($this->webHookUrl, [
            'json' => $content,
        ]);
    }
}
