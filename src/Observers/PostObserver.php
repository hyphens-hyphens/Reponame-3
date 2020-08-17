<?php

namespace T2G\Common\Observers;


use Illuminate\Support\Str;
use T2G\Common\Models\Post;
use T2G\Common\Util\CommonHelper;

/**
 * Class PostObserver
 *
 * @package \T2G\Common\Observers
 */
class PostObserver
{
    public function saving(Post $post)
    {
        $post->title_keyword = CommonHelper::makeKeyword($post->title);
        $post->excerpt_keyword = CommonHelper::makeKeyword($post->excerpt);
    }
}
