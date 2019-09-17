<?php

namespace T2G\Common\Listeners;

use T2G\Common\Event\PostCreatingEvent;
use Illuminate\Support\Carbon;

class PostCreatingListener
{

    /**
     * Handle the event.
     *
     * @param  PostCreatingEvent $event
     *
     * @return void
     */
    public function handle(PostCreatingEvent $event)
    {
        if (empty($event->post->publish_date)) {
            $event->post->publish_date = Carbon::now();
        }
    }
}
