<?php

namespace T2G\Common\Listeners;

use T2G\Common\Event\PostModelEvent;
use Illuminate\Support\Carbon;

class PostCreatingListener
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
        if (empty($event->post->publish_date)) {
            $event->post->publish_date = Carbon::now();
        }
    }
}
