<?php namespace Pep\Dropcogs\Http\Controllers;

use Pep\Dropcogs\DropboxSession;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class PagesController extends Controller
{

    public function home()
    {
        return view('pages.home')
            ->with('user', DropboxSession::getUser());
    }
}
