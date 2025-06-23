<?php

namespace Url\Shortener\Handler;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Config\Option;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class RouteHandler
{
    /**
     * Добавляет правило для urlrewrite, для поддержания коротких ссылок
     *
     * @return void
     * @throws ArgumentNullException
     */
    public static function handle(): void
    {
        $url = Option::get('url.shortener', 'link_short', '/share/basket/');
        $rule =   [
            'CONDITION' => "#^$url([a-zA-Z0-9~]+)/?#",
            'RULE' => 'code=$1',
            'ID' => '',
            'PATH' => '/local/modules/url.shortener/share_handler.php',
            'SORT' => 0,
        ];

        \Bitrix\Main\UrlRewriter::add(
            SITE_ID,
            $rule
        );

    }
}