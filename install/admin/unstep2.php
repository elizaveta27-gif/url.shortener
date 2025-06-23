<?php

use Bitrix\Main\Application;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

try {
    $status = true;
    $context = Application::getInstance()->getContext();
    $request = $context->getRequest();
    global $APPLICATION;
    if (!check_bitrix_sessid()) {
        return;
    }
    $path = (new Url\Shortener\Import\ImportShortLinkHl)->importDataCsv();
} catch (Throwable $e) {
    $message = $e->getMessage();
    $status = false;
}
?>

<div class="adm-detail-content-wrap" style="padding: 10px">
    <?php if($status) { ?>
        <a href="<?= $path?>" download class="button">Скачать CSV</a>
        <form action="<?=$APPLICATION->GetCurPage()?>" method="POST"  style="margin-top: 15px">
                <?= bitrix_sessid_post() ?>
                <input type="hidden" name="lang" value="<?=LANG?>">
                <input type="hidden" name="id" value="<?=$request['id']?>">
                <input type="hidden" name="uninstall" value="Y">
                <input type="hidden" name="step" value="3">
                <input type="submit" name="inst" value="Следующий шаг">
        </form>
    <?php } else {?>
        <?php CAdminMessage::ShowOldStyleError('Произошла ошибка: ' . $message);?>
        <form action="<?=$APPLICATION->GetCurPage()?>" method="POST" >
            <?= bitrix_sessid_post() ?>
            <input type="hidden" name="step" value="1">
            <input type="hidden" name="lang" value="<?=LANG?>">
            <input type="hidden" name="id" value="<?=$request['id']?>">
            <input type="hidden" name="uninstall" value="Y">
            <input type="submit" name="inst" value="Предыдущий шаг">
        </form>
    <?php }?>
</div>
