<?

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
    die();
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin.php');

$moduleId = 'url.shortener';

if (!$USER->IsAdmin()) {
    $APPLICATION->AuthForm('Доступ запрещён');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid()) {
    foreach ($_POST as $key => $value) {
        \Bitrix\Main\Config\Option::set($moduleId, $key, $value);
    }

    LocalRedirect($APPLICATION->GetCurPage() . '?mid=' . $moduleId . '&lang=' . LANGUAGE_ID);
}

$aTabs = [
    [
        'DIV' => 'edit1',
        'TAB' => 'Основные',
        'OPTIONS' => [
            ['link_basket', 'Ссылка на корзину', '/personal/cart', ['text', 100]],
            ['link_404', 'Ссылка на 404', '404',  ['text', 100]],
            ['link_short', 'Url для генерации короткой ссылки', '/share/basket/',  ['text', 100]],
            ['classBasket',
                'Реализация класса по добавлению товара в корзину(если класса не существует  будет отрабатывать стандартный обработчик)',
                '\Url\Shortener\Service\StandardBasketProcessor',
                ['text', 100]
            ],
        ]
    ]
];

$tabControl = new CAdminTabControl('tabControl', $aTabs);
$tabControl->Begin();
?>
    <form method="POST" action="<?= $APPLICATION->GetCurPage() ?>?mid=<?= htmlspecialcharsbx($moduleId) ?>&lang=<?= LANGUAGE_ID ?>">
        <?php
        foreach ($aTabs as $tab) {
            $tabControl->BeginNextTab();
            __AdmSettingsDrawList($moduleId, $tab['OPTIONS']);
        }
        $tabControl->Buttons();
        echo bitrix_sessid_post();
        ?>
        <input type="submit" name="apply" value="Применить" class="adm-btn-save">
    </form>
<?php
$tabControl->End();

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');