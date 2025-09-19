<?php
namespace Itb\Core\Helpers;

class FilesHelper
{
    private function __construct() {}
    /**
     * @param array $files $_FILES
     */
    public static function getFormattedToSafe(?array $files) : array
    {
        if(empty($files)){
            return [];
        }
        $toSavefiles = [];
        $diff = count($files) - count($files, COUNT_RECURSIVE);
        if ($diff == 0) {
            $toSavefiles = [$files];
        } else {
            foreach ($files as $k => $l) {
                foreach ($l as $i => $v) {
                    $toSavefiles[$i][$k] = $v;
                }
            }	
        }
        return $toSavefiles;
    }
}