<?php namespace Pep\Dropcogs;

use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{

    protected $table = 'folders';

    public function files()
    {
        return $this->hasMany('Pep\Dropcogs\File');
    }

    public static function isIncluded($path)
    {
        $pathPieces = explode('/', $path);

        while (count($pathPieces)) {
            $pathPieced = implode('/', $pathPieces);

            $excludingFolder = self::where('path', $pathPieced)
                ->where('include', false)
                ->first();

            if ($excludingFolder) {
                return false;
            }

            $includingFolder = self::where('path', $pathPieced)
                ->first();

            if ($includingFolder) {
                return true;
            }

            array_pop($pathPieces);
        }

        return false;
    }
}
