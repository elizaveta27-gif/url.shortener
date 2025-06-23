<?php

namespace Url\Shortener\Service;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Url\Shortener\Model\ShortLinkModel;

class ShortLinkProcessorService
{
    private BasketProcessorInterface $basketProcessor;
    public function __construct(BasketProcessorInterface $basketProcessor)
    {
        $this->basketProcessor = $basketProcessor;
    }

    /**
     * Добавляет данные в корзину
     *
     * @return bool
     */
    public function process()
    {
        $context = Application::getInstance()->getContext();
        $uri = $context->getRequest()->getRequestUri();
        $model = (new ShortLinkModel());


        $dynamicPath = Option::get('url.shortener', 'link_short', '/share/basket/');
        $escapedPath = ($dynamicPath === '/') ? '' : preg_quote(trim($dynamicPath, '/'), '#') . '/';

        if (preg_match("#^/{$escapedPath}([a-zA-Z0-9~]+)/?$#i", $uri, $matches)) {
            $code = $matches[1];
        } else {
            return false;
        }

        $products = $model->getProductsLink($code);

        if (empty($products)) {
            return false;
        }
        $this->addBasket($products);
        return true;
    }

    /**
     * @param array $products
     * @return bool
     */
    private function addBasket(array $products): bool
    {
        return $this->basketProcessor->addProducts($products);
    }

}