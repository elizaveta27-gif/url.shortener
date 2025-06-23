<?php
use Bitrix\Main\Config\Option;

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
    die();
}

\Bitrix\Main\Loader::includeModule('url.shortener');

try {
    $cartLink = Option::get('url.shortener', 'link_basket');
    $notFoundLink = Option::get('url.shortener', 'link_404');
    if (!class_exists(Option::get('url.shortener', 'classBasket')))
    {
        $proccessor = (new \Url\Shortener\Service\StandardBasketProcessor);
    } else {
        $proccessor = new (Option::get('url.shortener', 'classBasket'));
    }
    if ((new \Url\Shortener\Service\ShortLinkProcessorService($proccessor))->process()) {
        LocalRedirect($cartLink);
    }
} catch (\Exception $e) {

}

\CHTTP::SetStatus("404 Not Found");
LocalRedirect($notFoundLink, true, '404 Not Found');