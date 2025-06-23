<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<div class="share-container">
    <button class="share-button" id="shareButton">Поделиться</button>
    <div class="notification_error" id="notificationShare"></div>
    <div class="popup" id="sharePopup">
        <button class="close-popup" id="closePopup">&times;</button>
        <h3>Поделиться ссылкой</h3>
        <div class="link-container">
            <input type="text" class="link-input" id="shareLink" value="https://example.com/page-to-share" readonly>
            <button class="copy-button" id="copyButton">Копировать</button>
            <div class="notification" id="notification">Ссылка скопирована!</div>
        </div>
    </div>
</div>
