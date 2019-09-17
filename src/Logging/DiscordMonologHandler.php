<?php

namespace T2G\Common\Logging;

use GuzzleHttp\Client;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
/**
 * Class DiscordMonologHandler
 *
 * @package \T2G\Common\Logging
 */
class DiscordMonologHandler extends AbstractProcessingHandler
{
    /**
     * @var \GuzzleHttp\Client;
     */
    private $client;

    /**
     * @var array
     */
    private $webhooks;

    /**
     * Colors for a given log level.
     *
     * @var array
     */
    protected $levelColors = [
        Logger::DEBUG => 10395294,
        Logger::INFO => 5025616,
        Logger::NOTICE => 6323595,
        Logger::WARNING => 16771899,
        Logger::ERROR => 16007990,
        Logger::CRITICAL => 16007990,
        Logger::ALERT => 16007990,
        Logger::EMERGENCY => 16007990,
    ];

    /**
     * DiscordHandler constructor.
     * @param $webhooks
     * @param int $level
     * @param bool $bubble
     */
    public function __construct($webhooks, $level, $bubble = true)
    {
        $this->client = new Client();
        $this->webhooks = $webhooks;
        parent::__construct($level, $bubble);
    }

    /**
     * @param array $record
     *
     * @throws \Exception
     */
    protected function write(array $record)
    {
        if (env('APP_ENV') == 'local') {
            return;
        }
        $content = [
            "embeds" => [
                [
                    "title"       => $record['level_name'],
                    "description" => $record['message'],
                    "timestamp"   => (new \DateTime())->setTimezone(new \DateTimeZone('UTC'))->format('c'),
                    "color"       => $this->levelColors[$record['level']],
                ],
            ],
        ];
        foreach ($this->webhooks as $webhook) {
            $this->client->post($webhook, [
                'json' => $content,
            ]);
        }
    }
}
