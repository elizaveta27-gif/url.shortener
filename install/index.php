<?php

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Application;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Result;
use Bitrix\Main\Error;
use Url\Shortener\Migrations\ShortUrlHl;
use Url\Shortener\Model\ShortLinkModel;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class url_shortener extends CModule
{

    public function __construct()
    {
        $this->MODULE_ID = Loc::getMessage('MODULE_ID');
        $this->MODULE_VERSION = Loc::getMessage('MODULE_VERSION');
        $this->MODULE_VERSION_DATE = Loc::getMessage('MODULE_VERSION_DATE');
        $this->MODULE_NAME = Loc::getMessage('MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('MODULE_DESCRIPTION');
        $this->MODULE_GROUP_RIGHTS = Loc::getMessage('MODULE_GROUP_RIGHTS');
        $this->MODULE_UNINSTALL_FILES = [
            'unstep1.php',
            'unstep2.php'
        ];
    }

    /**
     * @return bool
     */
    public function DoInstall(): bool
    {
        try {
            require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

            ModuleManager::registerModule($this->MODULE_ID);

            if (!\Bitrix\Main\Loader::includeModule($this->MODULE_ID)) {
                return false;
            }
            $this->installDB();
            $this->installEvents();
            $this->installFiles();
            return true;

        } catch (Throwable $exception) {
            CAdminMessage::ShowOldStyleError('Произошла ошибка: ' . $exception->getMessage());
            return false;
        }
    }

    /**
     * @return bool
     */
    public function DoUninstall(): bool
    {
        try {
            global $APPLICATION;

            $context = Application::getInstance()->getContext();
            $request = $context->getRequest();

            if (!Loader::includeModule($this->MODULE_ID)) {
                return false;
            }

            if ($request['step'] < 2) {
                $APPLICATION->IncludeAdminFile('Удаление модуля', $_SERVER["DOCUMENT_ROOT"] . "/local/modules/$this->MODULE_ID/install/admin/unstep1.php");
            } elseif ($request['step'] == 2 && $request['save_data'] == 'Y') {
                $APPLICATION->IncludeAdminFile('Удаление модуля', $_SERVER["DOCUMENT_ROOT"] . "/local/modules/$this->MODULE_ID/install/admin/unstep2.php");
            } elseif ($request['step'] == 3 || ($request['save_data'] == 'N' && $request['step'] == 2)) {
                $this->uninstallDB();
                $this->uninstallEvents();
                $this->unInstallFiles();
                ModuleManager::unregisterModule($this->MODULE_ID);
            }
            return true;
        } catch (Throwable $exception) {
            CAdminMessage::ShowOldStyleError('Произошла ошибка: ' . $exception->getMessage());
            return false;
        }
    }

    /**
     * Добавляет компоненты модуля в папку /bitrix/components
     *
     * @return void
     * @throws FileCopyException
     */
    function installFiles(): void
    {
        $this->unInstallFiles();
        $res = CopyDirFiles(
            __DIR__ . '/components',
            $_SERVER["DOCUMENT_ROOT"] . '/bitrix/components',
            true, // Перезаписывает файлы
            true  // Копирует рекурсивно
        );
        if (!$res) {
            throw new FileCopyException('Ошибка установки, не удалось скопировать компоненты,   
              рекомендуется скопировать их вручную, из папки
             /bitrix/modules/имя.модуля/install/components в /bitrix/components'
            );
        }
    }

    /**
     * Удаляет файлы компонента из папки bitrix/components
     *
     * @return bool
     * @throws Exception
     */
    function unInstallFiles(): bool
    {
        $res = true;

        if (is_dir($_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/" . $this->MODULE_ID)) {
            $res = DeleteDirFilesEx("/bitrix/components/" . $this->MODULE_ID);
        }

        if (!$res) {
            throw new Exception('Ошибка удаления модуля, не удалось удалить компоненты, рекомендуется очистить их вручную,
                        в папке /bitrix/components/имя.модуля');
        }

        return true;
    }

    /**
     * Регистрирует события
     *
     * @return void
     */
    function installEvents(): void
    {

        EventManager::getInstance()->registerEventHandler(
            'main',
            'OnPageStart',
            $this->MODULE_ID,
            '\Url\Shortener\Handler\RouteHandler',
            'handle'
        );
    }

    /**
     * Удаляет события модуля
     *
     * @return void
     */
    function unInstallEvents(): void
    {
        EventManager::getInstance()->unRegisterEventHandler(
            'main',
            'OnPageStart',
            $this->MODULE_ID,
            '\Url\Shortener\Handler\RouteHandler',
            'handle'
        );
    }

    /**
     * @return void
     * @throws Throwable
     * @throws \Bitrix\Main\DB\SqlQueryException
     */
    public function installDb(): void
    {
        try {
            $connection = Application::getConnection();
            $connection->startTransaction();
            $this->createShortUrlHl();

            $connection->commitTransaction();

        } catch (Throwable $exception) {
            if (isset($connection) && $connection->isConnected()) {
                $connection->rollbackTransaction();
            }
            throw $exception;
        }
    }

    /**
     * @return void
     * @throws Throwable
     * @throws \Bitrix\Main\DB\SqlQueryException
     */
    public function unInstallDB(): void
    {
        try {
            $connection = Application::getConnection();
            $connection->startTransaction();
            $this->deleteShortUrlHl();
            $connection->commitTransaction();
        } catch (Throwable $exception) {
            if (isset($connection) && $connection->isConnected()) {
                $connection->rollbackTransaction();
            }

           throw $exception;
        }

    }

    /**
     * Создает hl-блок для хранения готовых ссылок
     *
     * @return void
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\SystemException
     */
    private function createShortUrlHl(): void
    {
        Loader::includeModule('highloadblock');
        $hlData = [
            'NAME' => ShortLinkModel::ENTITY_NAME,
            'TABLE_NAME' => ShortUrlHl::TABLE_NAME,
        ];

        $result = HighloadBlockTable::add($hlData);
        if (!$result->isSuccess()) {
            throw new Exception(implode(', ', $result->getErrorMessages()));
        }

        $hlId = $result->getId();

        $result = $this->addHlFields($hlId);

        if (!$result->isSuccess()) {
            throw new Exception(implode(', ', $result->getErrorMessages()));
        }
    }

    /**
     * @param $hlId
     * @return Result
     */
    private function addHlFields($hlId): Result
    {

        $userTypeEntity = new CUserTypeEntity();
        $result = new Result;

        foreach (ShortUrlHl::getFields($hlId) as $field) {
            $id = $userTypeEntity->Add($field);
            if (empty($id)) {
                $result->addError(new Error('Произошла ошибка при создании поля' . $field['FIELD_NAME']));
            }
        }
        return $result;
    }

    /** Удаляет hl для хранения готовых ссылок
     *
     * @return void
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    private function deleteShortUrlHl(): void
    {
        Loader::includeModule('highloadblock');

        $hlblock = HighloadBlockTable::getList([
            'filter' => ['=NAME' => ShortLinkModel::ENTITY_NAME],
        ])->fetch();

        if ($hlblock) {
            HighloadBlockTable::delete($hlblock['ID']);
        }
    }

}