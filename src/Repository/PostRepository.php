<?php

namespace T2G\Common\Repository;

use T2G\Common\Models\Post;
use T2G\Common\Util\CommonHelper;

/**
 * Class PostRepository
 *
 * @package \T2G\Common\Repository
 */
class PostRepository extends AbstractEloquentRepository
{
    /**
     * @var Post
     */
    protected $model;

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model(): string
    {
        return Post::class;
    }

    /**
     * @param string $categorySlug
     * @param int    $limit
     *
     * @return \Illuminate\Support\Collection
     */
    public function getHomePostsByCategory($categorySlug = '', $limit = self::DEFAULT_PER_PAGE)
    {
        /** @var \Illuminate\Database\Query\Builder|Post $query */
        $query = $this->query();
        $query->published()
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->with('category')
        ;
        if ($categorySlug) {
            $query->categorySlug($categorySlug);
        }

        return $query->get();
    }

    public function getHomeEvents($eventSlug = 'su-kien', $limit = self::DEFAULT_PER_PAGE)
    {
        /** @var \Illuminate\Database\Query\Builder|Post $query */
        $query = $this->query();
        $query->published()
            ->categorySlug($eventSlug)
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
        ;

        return $query->get();
    }

    /**
     * @param string $categorySlug
     * @param int $limit
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function listPostByCategory($categorySlug, $limit = self::DEFAULT_PER_PAGE)
    {
        /** @var \Illuminate\Database\Query\Builder|Post $query */
        $query = $this->query();
        $query->published()
            ->orderBy('updated_at', 'desc')
        ;
        if ($categorySlug) {
            $query->categorySlug($categorySlug);
        }

        return $query->paginate($limit);
    }

    /**
     * @param Post $post
     * @param int  $limit
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model[]
     */
    public function getOtherPosts(Post $post, $limit = 6)
    {
        /** @var \Illuminate\Database\Query\Builder|Post $query */
        $query = $this->query();
        $query->published()
            ->where("id", '!=', $post->id)
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->with('category')
        ;

        return $query->get();
    }

    /**
     * @param $postSlug
     *
     * @return Post|null
     */
    public function getPostBySlug($postSlug)
    {
        $query = $this->query();
        $query->where('slug', $postSlug);

        return $query->first();
    }

    /**
     * @param      $text
     * @param int  $limit
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function searchPost($text, $limit = 10)
    {
        $keyword = CommonHelper::makeKeyword($text);
        /** @var \Illuminate\Database\Query\Builder|Post $query */
        $query = $this->query();
        $query->published()
            ->orderBy('updated_at', 'desc')
            ->whereRaw("(`title_keyword` LIKE ? OR `excerpt_keyword` LIKE ?)", ["%{$keyword}%", "%{$keyword}%"])
            ->with('category')
        ;

        return $query->paginate($limit);
    }

    /**
     * @param array $slugs
     * @param int   $limit
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|\T2G\Common\Models\Post[]
     */
    public function getPostsBySlugs(array $slugs, $limit = 10)
    {
        /** @var \Illuminate\Database\Query\Builder|Post $query */
        $query = $this->query();
        $query->published()
            ->whereIn("slug", $slugs)
            ->limit($limit)
            ->with('category')
            ->orderByRaw("FIELD(slug, '". implode("','", $slugs) ."')")
        ;

        return $query->get();
    }


    /**
     * @param      $groupSlug
     * @param null $currentPostId
     * @param int  $limit
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|\T2G\Common\Models\Post[]
     */
    public function getGroupPosts($groupSlug, $currentPostId = null, $limit = 20)
    {
        $posts = $this->query()
            ->selectRaw('*, (`group_sub` IS NULL OR `group_sub` = "") as `group_sub_order`')
            ->with('category')
            ->where('group_slug', $groupSlug)
            ->orderByRaw('group_sub_order desc, group_order asc')
            ->limit($limit)
            ->get()
        ;
        if ($currentPostId && $posts->count() == 1 && $posts[0]->id == $currentPostId) {
            // do not have other posts published
            return new \Illuminate\Database\Eloquent\Collection();
        }
        $results = [];
        $index = [];
        foreach ($posts as $post) {
            if (!empty($post->group_sub)) {
                if (!isset($index[$post->group_sub])) {
                    $index[$post->group_sub] = count($results) - 1;
                }
                $results[$index[$post->group_sub]]['sub_title'] = $post->group_sub;
                $results[$index[$post->group_sub]]['subs'][] = $post;
            } else {
                $results[] = ['post' => $post];
            }
        }

        return $results;
    }

    /**
     * @param      $groupSlug
     * @param int  $limit
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|\T2G\Common\Models\Post[]
     */
    public function getGroupPostsWithoutSubs($groupSlug, $limit = 20)
    {
        $posts = $this->query()
            ->published()
            ->with('category')
            ->where('group_slug', 'LIKE', $groupSlug)
            ->whereRaw('`group_sub` IS NULL OR `group_sub` = ""')
            ->orderBy('group_order', 'asc')
            ->limit($limit)
            ->get()
        ;

        return $posts;
    }
}
