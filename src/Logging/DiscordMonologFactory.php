<?php

namespace T2G\Common\Logging;

use Monolog\Logger;

/**
 * Class CreateDiscordLogger
 *
 * @package \T2G\Common\Logging
 */
class DiscordMonologFactory
{
    /**
     * Create a custom Discord Monolog instance.
     *
     * @param  array  $config
     * @return \Monolog\Logger
     */
    public function __invoke(array $config)
    {
        $log = new Logger('discord');
        $log->pushHandler(new DiscordMonologHandler([$config['url']], $config['level']));

        return $log;
    }
}
