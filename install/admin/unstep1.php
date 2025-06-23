<?

use Bitrix\Main\Application;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

global $APPLICATION;

if (!check_bitrix_sessid()) {
    return;
}
global $APPLICATION;
$context = Application::getInstance()->getContext();
$request = $context->getRequest();

?>

<form action="<?=$APPLICATION->GetCurPage()?>" method="POST" >
    <?= bitrix_sessid_post() ?>
    <div class="adm-detail-content-wrap" style="padding: 10px">
        <input type="hidden" name="lang" value="<?=LANG?>">
        <input type="hidden" name="id" value="<?=$request['id']?>">
        <input type="hidden" name="uninstall" value="Y">
        <input type="hidden" name="step" value="2">

        <div style="margin: 20px;">
            <h3>Сохранить данные модуля?</h3>
            <p>
                <label>
                    <input type="radio" name="save_data" value="Y" checked>
                    Да, сохранить данные
                </label>
            </p>
            <p>
                <label>
                    <input type="radio" name="save_data" value="N">
                    Нет, удалить все данные
                </label>
            </p>
        </div>

        <input type="submit" name="inst" value="Продолжить">
    </div>
</form>