<?php
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
