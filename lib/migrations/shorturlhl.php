<?php

namespace Url\Shortener\Migrations;

class ShortUrlHl
{
    public const TABLE_NAME = 'b_hl_short_url';

    public static function getFields(string $hlId): array
    {
        return [
            [
                'ENTITY_ID' => 'HLBLOCK_' . $hlId,
                'FIELD_NAME' => 'UF_SHORT_CODE',
                'USER_TYPE_ID' => 'string',
                'SORT' => 100,
                'MANDATORY' => 'N',
                'EDIT_FORM_LABEL' => ['ru' => 'Код ссылки'],
                'LIST_COLUMN_LABEL' => ['ru' => 'Код ссылки'],
            ],
            [
                'ENTITY_ID' => 'HLBLOCK_' . $hlId,
                'FIELD_NAME' => 'UF_PRODUCT_IDS',
                'USER_TYPE_ID' => 'string',
                'SORT' => 200,
                'MANDATORY' => 'N',
                'EDIT_FORM_LABEL' => ['ru' => 'ID товаров'],
                'LIST_COLUMN_LABEL' => ['ru' => 'ID товаров'],
            ],
            [
                'ENTITY_ID' => 'HLBLOCK_' . $hlId,
                'FIELD_NAME' => 'UF_DATE_CREATE',
                'USER_TYPE_ID' => 'datetime',
                'SORT' => 200,
                'MANDATORY' => 'N',
                'EDIT_FORM_LABEL' => ['ru' => 'Время создания'],
                'LIST_COLUMN_LABEL' => ['ru' => 'Время создания'],
            ]
        ];
    }

}