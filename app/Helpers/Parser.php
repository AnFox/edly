<?php


namespace App\Helpers;


/**
 * Class Parser
 * @package App\Helpers
 */
class Parser
{
    /**
     * @param string $url
     * @return string|null
     */
    public static function getYouTubeEmbedVideoId(string $url)
    {
        $re = '/(?:https?:)?(?:\/\/)?(?:[0-9A-Z-]+\.)?(?:youtu\.be\/|youtube(?:-nocookie)?\.com\/\S*?[^\w\s-])((?!videoseries)[\w-]{11})(?=[^\w-]|$)(?![?=&+%\w.-]*(?:[\'"][^<>]*>|<\/a>))[?=&+%\w.-]*/im';
        preg_match($re, $url, $matches);

        return count($matches) === 2 ? $matches[1] : null;
    }
}