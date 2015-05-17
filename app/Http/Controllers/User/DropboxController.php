<?php namespace Pep\Dropcogs\Http\Controllers\User;

use Pep\Dropcogs\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Pep\Dropcogs\Dropbox\Auth as DropboxAuth;
use Pep\Dropcogs\Dropbox\Exception as DropboxException;
use Pep\Dropcogs\Dropbox\Client as DropboxClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Pep\Dropcogs\User;
use Pep\Dropcogs\Folder;
use Pep\Dropcogs\File;
use Pep\Dropcogs\DropboxSession;
use Carbon\Carbon;
use Pep\Dropcogs\Events\AnalyzeEvent;
use Pep\Dropcogs\Events\AnalyzedEvent;

class DropboxController extends Controller {

	public function auth(Request $request) {
		try {
			$session = DropboxAuth::finish($request->all());
		} catch (DropboxException $e) {
			return redirect()->route('pages.users.login')
				->with('message', $e->getMessage());
		}

		$client = new DropboxClient($session['userId'], $session['accessToken']);

		$accountInfo = $client->getAccountInfo();

		$user = User::where('dropbox_id', $accountInfo['uid'])
			->first();

		if (!$user) {
			$user = new User;
		}

		$user->dropbox_id = $accountInfo['uid'];
		$user->display_name = $accountInfo['display_name'];
		$user->first_name = $accountInfo['name_details']['given_name'];
		$user->last_name = $accountInfo['name_details']['surname'];
		$user->familiar_name = $accountInfo['name_details']['familiar_name'];
		$user->email = $accountInfo['email'];
		$user->country = $accountInfo['country'];
		$user->locale = $accountInfo['locale'];
		$user->referral_link = $accountInfo['referral_link'];

		$user->save();

		$dropboxSession = new DropboxSession;

		$dropboxSession->access_token = $session['accessToken'];
		$dropboxSession->dropbox_id = $session['userId'];
		$dropboxSession->url_state = $session['urlState'];
		$dropboxSession->user_id = $user->id;

		$dropboxSession->save();

		session([
			'dropbox_session' => $dropboxSession,
		]);

		return redirect()->route('pages.users.configure');
	}

	public function analyze() {
		$user = DropboxSession::getUser();

		event(new AnalyzeEvent($user));
		dd($user);

		return redirect()->route('');
	}

}