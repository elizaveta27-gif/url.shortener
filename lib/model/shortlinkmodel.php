<?php

namespace Url\Shortener\Model;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use CBXShortUri;

class ShortLinkModel
{
    public const ENTITY_NAME = 'ShortUrl';

    private $entity;

    public function __construct()
    {
        Loader::includeModule('highloadblock');
        $this->entity = (HighloadBlockTable::compileEntity(self::ENTITY_NAME))->getDataClass();
    }

    /**
     * Генерация короткой ссылки
     *
     * @param array $aIdsProducts
     * @return mixed|string
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function generateShortCode(array $aIdsProducts): mixed
    {
        $sProducts = $this->formatProductsDb($aIdsProducts);
        $result = $this->entity::getList([
            'select' => ['UF_SHORT_CODE'],
            'filter' => ['UF_PRODUCT_IDS' => $sProducts],
        ])->fetch();

        if ($result) {
            return $result['UF_SHORT_CODE'];
        }
        $shortCode = CBXShortUri::GenerateShortUri();
        $this->entity::add([
            'UF_DATE_CREATE' => new DateTime(),
            'UF_PRODUCT_IDS' => $sProducts,
            'UF_SHORT_CODE' => $shortCode
        ]);

        return $shortCode;
    }

    public function getProductsLink(string $sLink): ?array
    {
        $result = $this->entity::getList([
            'select' => ['UF_SHORT_CODE', 'UF_PRODUCT_IDS'],
            'filter' => ['UF_SHORT_CODE' => $sLink],
        ])->fetch();

        return $result['UF_PRODUCT_IDS'] ? $this->formatProductsCode($result['UF_PRODUCT_IDS']) : null;
    }

    /**
     * @param string $sProducts
     * @return array
     */
    private function formatProductsCode(string $sProducts): array
    {
        return json_decode($sProducts, true) ?? [];
    }

    /**
     * @param array $aIdsProducts
     * @return string
     */
    private function formatProductsDb(array $aIdsProducts): string
    {
        ksort($aIdsProducts);
        return json_encode($aIdsProducts);
    }

}