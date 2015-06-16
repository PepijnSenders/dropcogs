<?php namespace Pep\Dropcogs\Events;

use Pep\Dropcogs\Events\Event;

use Illuminate\Queue\SerializesModels;
use Pep\Dropcogs\User;

class FilesReadyEvent extends Event
{

    use SerializesModels;

    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }
}
