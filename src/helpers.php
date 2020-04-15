<?php

use T2G\Common\Services\JXApiClient;

if (!function_exists('t2g_model')) {
    /**
     * @param string $name model name
     *
     * @return \Illuminate\Database\Eloquent\Model|mixed
     */
    function t2g_model(string $name) {
        $modelClassName = config("t2g_common.models.{$name}_model_class");

        return app($modelClassName);
    }
}

if (!function_exists('voyager')) {
    /**
     *
     * @return \TCG\Voyager\Voyager
     */
    function voyager() {
        return app('voyager');
    }
}

if (!function_exists('getGameApiClient')) {
    /**
     *
     * @return \T2G\Common\Services\GameApiClientInterface
     */
    function getGameApiClient() {
        return app(config('t2g_common.game_api.api_client_classname', JXApiClient::class));
    }
}

