<?php

namespace T2G\Common\Console\Commands;

use Illuminate\Console\Command;
use T2G\Common\Models\Post;
use T2G\Common\Util\CommonHelper;

class UpdatePostKeywordsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:post:keywords';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update posts keyword';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $posts     = Post::all();
        $processed = 0;
        foreach ($posts as $post) {
            $post->title_keyword   = CommonHelper::makeKeyword($post->title);
            $post->excerpt_keyword = CommonHelper::makeKeyword($post->excerpt);
            $post->timestamps = false;
            $post->save();
            $processed++;
        }
        echo "Processed {$processed} posts";

        return 0;
    }
}
