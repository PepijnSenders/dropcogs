<?php namespace Pep\Dropcogs\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Pep\Dropcogs\DropboxSession;

class DropboxAuthenticate
{

    public function handle($request, Closure $next)
    {
        if (DropboxSession::getUser()) {
            return $next($request);
        } else {
            return Redirect::route('pages.users.login');
        }
    }
}
