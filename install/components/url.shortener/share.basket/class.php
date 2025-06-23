<?php

use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\ActionFilter\HttpMethod;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorableImplementation;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Uri;
use Bitrix\Sale\Basket;
use Bitrix\Sale\Fuser;

class Share_Basket extends CBitrixComponent implements Controllerable, Errorable
{
    use ErrorableImplementation;

    private string $url;

    public function __construct($component = null)
    {
        $this->url = Option::get('url.shortener', 'link_short', '/share/basket/');
        $this->errorCollection = new ErrorCollection();
        parent::__construct($component);
    }

    public function executeComponent()
    {
        $this->includeComponentTemplate();
    }

    /**
     * @return array[]
     */
    public function configureActions(): array
    {
        return [
            'generateLink' => [
                'prefilters' => [
                    new HttpMethod([
                        Bitrix\Main\Engine\ActionFilter\HttpMethod::METHOD_POST,
                    ]),
                    new Csrf(),
                ],
                'postfilters' => []
            ]
        ];
    }

    /**
     * @return array
     * @throws \Bitrix\Main\LoaderException
     */
    public function generateLinkAction(): array
    {
        Loader::includeModule('sale');

        $aProductsIds = $this->getBasketProducts();

        if (empty($aProductsIds)) {
            $this->errorCollection->add([new \Bitrix\Main\Error('В корзине нет товаров')]);
            return [];
        }

        $code = (new \Url\Shortener\Model\ShortLinkModel)->generateShortCode($aProductsIds);
        $currentUrl = (new Uri($this->url. $code))->withPort($_SERVER['SERVER_PORT'])->toAbsolute()->getUri();
        return ['code' => $currentUrl];
    }

    /**
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentTypeException
     * @throws \Bitrix\Main\NotImplementedException
     */
    private function getBasketProducts()
    {

        $fUserId = Fuser::getId(true);

        if (!isset($fUserId)) {
            return [];
        }
        $aProductsIds = [];
        $basket = Basket::loadItemsForFUser($fUserId, Context::getCurrent()->getSite());
        foreach ($basket->getBasketItems() as $basketTtem) {
            $aProductsIds[$basketTtem->getProductId()] = $basketTtem->getQuantity();
        }

        return $aProductsIds;
    }

}