<?php

namespace T2G\Common\Controllers\Front;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
/**
 * Class BaseFrontController
 *
 * @package \T2G\Common\Controllers\Front
 */
class BaseFrontController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * BaseFrontController constructor.
     */
    public function __construct()
    {
        $this->middleware(['t2g']);
        $this->tracks(['utm_source', 'utm_medium', 'utm_campaign']);
    }

    /**
     * @param $name
     *
     * @return bool
     */
    protected function track($name)
    {
        $value = request($name);
        if (!$value) {
            return false;
        }
        $this->setTrackingCookie($name, $value);

        return true;
    }

    /**
     * @param array $names
     */
    protected function tracks(array $names)
    {
        $tracked = false;
        foreach ($names as $name) {
            $tracked = $this->track($name);
        }
        if (!$tracked && !empty($_SERVER['HTTP_REFERER'])) {
            $referer = parse_url($_SERVER['HTTP_REFERER']);
            if (!empty($referer['host']) && !in_array($referer['host'], config('t2g_common.site.domains', []))) {
                $this->setTrackingCookie('utm_source', $referer['host']);
                $this->setTrackingCookie('utm_medium', 'Referral');
            }
        }
    }

    /**
     * @param $name
     * @param $value
     */
    protected function setTrackingCookie($name, $value)
    {
        /** @var \Illuminate\Cookie\CookieJar $cookie */
        $cookieJar = app(\Illuminate\Cookie\CookieJar::class);
        $expire = 60 * 24 * 7; // 7 days
        $cookie = $cookieJar->make($name, $value, $expire);
        $cookieJar->queue($cookie);
    }

    /**
     * @param $title
     *
     * @return \T2G\Common\Controllers\Front\BaseFrontController
     */
    protected function setMetaTitle($title)
    {
        view()->share('title', $title . " - " . config('t2g_common.site.seo.title'));
        return $this;
    }

    /**
     * @param $description
     *
     * @return \T2G\Common\Controllers\Front\BaseFrontController
     */
    protected function setMetaDescription($description)
    {
        if (strlen($description) < 50) {
            return $this;
        }
        view()->share('meta_description', str_limit($description, 255) ?? config('t2g_common.site.seo.meta_description'));

        return $this;
    }

    /**
     * @param $image
     *
     * @return $this
     */
    protected function setMetaImage($image)
    {
        if ($image && strpos(trim($image), 'http') !== 0) {
            $image = url($image);
        }
        view()->share('meta_image', $image ?? asset(config('t2g_common.site.seo.meta_image')));

        return $this;
    }
}
