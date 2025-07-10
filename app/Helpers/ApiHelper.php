<?php

namespace App\Helpers;

class ApiHelper
{
    /**
     * APIのベースURLを取得
     *
     * @return string
     */
    public static function baseUrl(): string
    {
        $apiDomain = config('app.api_domain');
        $scheme = parse_url(config('app.url'), PHP_URL_SCHEME) ?? 'http';
        
        return $scheme . '://' . $apiDomain;
    }

    /**
     * APIのURLを生成
     *
     * @param string $path
     * @return string
     */
    public static function url(string $path): string
    {
        $path = ltrim($path, '/');
        return self::baseUrl() . '/' . $path;
    }
}