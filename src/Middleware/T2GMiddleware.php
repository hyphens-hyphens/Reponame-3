<?php

namespace T2G\Common\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

/**
 * Class T2GMiddleware
 *
 * @package \T2G\Common\Middleware
 */
class T2GMiddleware
{
    protected $except;

    public function __construct()
    {
        $this->except = config('t2g_common.site.front_page_forbidden_except_uris', ['/']);
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        /** @var Guard $auth */
        $auth = app('auth.driver');
        /** @var \T2G\Common\Models\AbstractUser $user */
        $user = $auth->user();
        if (
            $request->getMethod() == Request::METHOD_GET
            && config('t2g_common.site.front_page_forbidden')
            && !in_array($request->getPathInfo(), $this->except)
        ) {

            if (!$user || !$user->hasRole(['admin', 'operator', 'dev'])) {
                $redirectRouteParams = config('t2g_common.site.front_page_forbidden_redirect_route');
                return redirect(route(...$redirectRouteParams))->with('showWarning', true);
            }
        }
        view()->share('user', $user);

        return $next($request);
    }
}
