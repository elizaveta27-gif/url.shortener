<?php

namespace Url\Shortener\Service;

use Bitrix\Catalog\Product\CatalogProvider;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;

class StandardBasketProcessor implements BasketProcessorInterface
{
    /**
     * Добавляет товары в корзину по короткой ссылке
     *
     * @param array $products
     * @return bool
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws ArgumentTypeException
     * @throws LoaderException
     * @throws NotImplementedException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     */
    public function addProducts(array $products): bool
    {
        $s1 = Context::getCurrent()->getSite();
        Loader::includeModule('sale');
        $basket = Basket::loadItemsForFUser(
            \Bitrix\Sale\Fuser::getId(),
            $s1
        );

        foreach ($products as $id => $quantity) {
            $basketItem = $this->getBasketItemByProductId($basket, $id);
            if ($basketItem) {
                $quantity = $basketItem->getQuantity() +  intval($quantity);
                $basketItem->setFields([
                    'QUANTITY' => $quantity,
                ]);
            } else {
                $basketItem = $basket->createItem('catalog', $id);
                $basketItem->setFields([
                    'QUANTITY' => $quantity,
                    'LID' => $s1,
                    'CURRENCY' => \Bitrix\Currency\CurrencyManager::getBaseCurrency(),
                    'PRODUCT_PROVIDER_CLASS' => CatalogProvider::class,
                ]);
            }
        }

        $result = $basket->save();
        $basket->refresh();

        return $result->isSuccess();
    }


    /**
     * Получение элемента корзины по ИД товара
     *
     * @param Basket $basket
     * @param $iProductId
     * @return BasketItem|null
     */
    protected function getBasketItemByProductId(Basket $basket, $iProductId): ?BasketItem
    {
        foreach ($basket->getBasket() as $oItem) {
            if ($oItem->getProductId() === $iProductId) {
                return $oItem;
            }
        }

        return null;
    }

}