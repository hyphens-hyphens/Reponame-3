<?php

use T2G\Common\Services\JXApiClient;

if (!function_exists('t2g_model')) {
    /**
     * @param string $name model name
     * @param string $default
     *
     * @return \Illuminate\Database\Eloquent\Model|mixed
     */
    function t2g_model(string $name, $default = '') {
        $modelClassName = config("t2g_common.models.{$name}_model_class");
        if (!$modelClassName && $default) {
            $modelClassName = $default;
        }

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

if (!function_exists( 'staticUrl')) {
    /**
     * @param      $path
     * @param bool $useMix
     *
     * @return string
     * @throws \Exception
     */
    function staticUrl($path, $useMix = false) {
        $root = config('t2g_common.asset.base_url');
        if ($useMix) {
            try {
                $path = mix($path);
            } catch (\Exception $e) {}
        }
        $generator = app(\Illuminate\Routing\UrlGenerator::class);

        $url = $root ? $generator->assetFrom($root, $path) : $generator->asset($path);
        if (strpos($url, '?') === false) {
            $version = config('t2g_common.asset.version');
            $url .= "?v={$version}";
        }

        return $url;
    }
}

if (!function_exists('voyagerStaticUrl')) {
    /**
     * @param      $path
     * @param null $secure
     *
     * @return string
     * @throws \Exception
     */
    function voyagerStaticUrl($path, $secure = null)
    {
        return staticUrl(config('voyager.assets_path').'/'.$path, $secure);
    }
}
