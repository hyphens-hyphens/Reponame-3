<?php

namespace T2G\Common\Event;

use T2G\Common\Models\Post;
use Illuminate\Queue\SerializesModels;

/**
 * Class PostSavingEvent
 *
 * @package \App\Event
 */
class PostModelEvent
{
    use SerializesModels;

    /**
     * @var \T2G\Common\Models\Post
     */
    public $post;

    /**
     * Create a new event instance.
     *
     * @param \T2G\Common\Models\Post $post
     */
    public function __construct(Post $post)
    {
        $this->post = $post;
    }
}
