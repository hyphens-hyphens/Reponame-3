<?php

namespace T2G\Common\Models;

use T2G\Common\Event\PostModelEvent;
use T2G\Common\Util\CommonHelper;
use Illuminate\Support\Carbon;

/**
 * T2G\Common\Models\Post
 *
 * @property int $id
 * @property int $author_id
 * @property int|null $category_id
 * @property string $title
 * @property string|null $seo_title
 * @property string|null $excerpt
 * @property string $body
 * @property string|null $image
 * @property string $slug
 * @property string|null $meta_description
 * @property string|null $meta_keywords
 * @property int $status
 * @property int $featured
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $publish_date
 * @property-read \TCG\Voyager\Models\User $authorId
 * @property-read \T2G\Common\Models\Category|null $category
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @method static \Illuminate\Database\Eloquent\Builder|Post active()
 * @method static \Illuminate\Database\Eloquent\Builder|Post categorySlug($categorySlug)
 * @method static \Illuminate\Database\Eloquent\Builder|Post orderByPublishDate()
 * @method static \Illuminate\Database\Eloquent\Builder|Post published()
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereAuthorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereExcerpt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereMetaDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereMetaKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post wherePublishDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereSeoTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post withTranslation($locale = null, $fallback = true)
 * @method static \Illuminate\Database\Eloquent\Builder|Post withTranslations($locales = null, $fallback = true)
 * @mixin \Eloquent
 * @property string|null $group_name
 * @property string|null $group_slug
 * @property string|null $group_title
 * @property int|null $group_order
 * @property string|null $group_sub
 * @property string|null $title_keyword
 * @property string|null $excerpt_keyword
 * @property-read int|null $translations_count
 * @method static \Illuminate\Database\Eloquent\Builder|\T2G\Common\Models\Post newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\T2G\Common\Models\Post newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\T2G\Common\Models\Post query()
 * @method static \Illuminate\Database\Eloquent\Builder|\T2G\Common\Models\Post whereExcerptKeyword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\T2G\Common\Models\Post whereGroupName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\T2G\Common\Models\Post whereGroupOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\T2G\Common\Models\Post whereGroupSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\T2G\Common\Models\Post whereGroupSub($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\T2G\Common\Models\Post whereGroupTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\T2G\Common\Models\Post whereTitleKeyword($value)
 */
class Post extends \TCG\Voyager\Models\Post
{
    use BaseEloquentModelTrait;

    const PUBLISHED = 1;

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'creating' => PostModelEvent::class,
        'saving'   => PostModelEvent::class,
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return string|null
     */
    public function getImage()
    {
        $image = '';
        if (!empty($this->image)) {
            $image = \Voyager::image(trim($this->image, '/'));
        } elseif ($this->body) {
            $image = $this->getFirstImageFromBody();
        }

        return $image;
    }

    /**
     * @param int $limit
     *
     * @return string
     */
    public function getDescription($limit = 150)
    {
        return str_limit($this->excerpt, $limit) ?: str_limit(strip_tags($this->body), $limit);
    }

    /**
     * @return string|null
     */
    private function getFirstImageFromBody() {
        preg_match('/<img.*src="([^"]*)"/i', $this->body, $matches);

        return $matches[1] ?? null;
    }

    /**
     * @return bool
     */
    public function hasImage()
    {
        return !!$this->getImage();
    }

    /**
     * Scope a query to only published scopes.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublished(\Illuminate\Database\Eloquent\Builder $query)
    {
        $now = time();

        return $query->where('status', self::PUBLISHED)
            ->where(
                function (\Illuminate\Database\Eloquent\Builder $query) use ($now) {
                    $query->where('publish_date', '<=', Carbon::now())
                        ->orWhere('publish_date', '=', null);
                }
            )->where("category_id", ">", 0 )
        ;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param                                       $categorySlug
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCategorySlug(\Illuminate\Database\Eloquent\Builder $query, $categorySlug)
    {
        return $query->whereHas('category', function (\Illuminate\Database\Eloquent\Builder $query) use ($categorySlug) {
            $query->where('slug', $categorySlug);
        });
    }

    /**
     * @param string $format
     *
     * @return string
     * @throws \Exception
     */
    public function displayPublishedDate($format = 'd.m.Y')
    {
        $date = $this->publish_date ?? $this->created_at;

        return CommonHelper::formatDate($date, $format);
    }

    /**
     *
     * @return string
     */
    public function getCategorySlug()
    {
        return $this->category ? $this->category->slug : "";
    }

    /**
     * @return string
     */
    public function getCategoryName()
    {
        return $this->category ? $this->category->name : "";
    }

    /**
     * @return bool
     */
    public function hasGroup()
    {
        return !empty($this->group_slug);
    }
}

