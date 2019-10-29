<?php

namespace T2G\Common\Repository;

use T2G\Common\Models\Post;

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
            ->orderByPublishDate()
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
            ->orderByPublishDate()
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
     * @param     $keyword
     * @param int $limit
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function searchPost($keyword, $limit = 10)
    {
        /** @var \Illuminate\Database\Query\Builder|Post $query */
        $query = $this->query();
        $query->published()
            ->whereRaw("title LIKE '%{$keyword}%'")
            ->orderBy('updated_at', 'desc')
        ;

        return $query->paginate($limit);
    }

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
}
