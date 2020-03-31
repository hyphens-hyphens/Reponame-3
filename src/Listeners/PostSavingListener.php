<?php

namespace T2G\Common\Listeners;

use T2G\Common\Event\PostModelEvent;

class PostSavingListener
{

    /**
     * Handle the event.
     *
     * @param  PostModelEvent $event
     *
     * @return void
     */
    public function handle(PostModelEvent $event)
    {
        if (config('t2g_common.features.post_grouping_enabled')) {
            $event->post->group_slug = \Illuminate\Support\Str::slug($event->post->group_name);
        }
    }
}
