<?php

namespace App\Helpers;

class DbHelper
{
    public static function getQueryLog($raw = false, $last = false)
    {
        $log = \DB::getQueryLog();
        if ($raw) {
            foreach ($log as $key => $query) {
                $log[ $key ] = vsprintf(str_replace('?', "'%s'", $query['query']), $query['bindings']);
            }
        }

        return ($last) ? end($log) : $log;
    }
}