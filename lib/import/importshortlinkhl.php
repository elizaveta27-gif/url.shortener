<?php

namespace Url\Shortener\Import;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\IO\FileNotOpenedException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Url\Shortener\Model\ShortLinkModel;

class ImportShortLinkHl
{

    /**
     * Формирует csv и записывает данные о коротких ссылках
     *
     * @return string
     * @throws FileNotOpenedException
     * @throws \Throwable
     */
    public function importDataCsv(): string
    {
        try {
            $filename = md5(uniqid() . time()) . '.csv';
            $filePath = "/upload/$filename";
            $fullFilePath = $_SERVER['DOCUMENT_ROOT'] . $filePath;
            $fp = fopen($fullFilePath,'w+');

            if (!$fp) {
                throw new FileNotOpenedException('Не удалось открыть файл для импорта');
            }

            $bFillHeaders = false;
            $generator = $this->getData();
            foreach ($generator as $row) {
                //заполняем заголовки
                if (!$bFillHeaders) {
                    if (fputcsv($fp, array_keys($row), ';') === false) {
                        throw new \RuntimeException('Ошибка записи заголовков в CSV');
                    }
                    $bFillHeaders = true;
                }
                if ($row['UF_DATE_CREATE']) {
                    $row['UF_DATE_CREATE'] = $row['UF_DATE_CREATE']->format('Y-m-d H:i:s');
                }

                if (fputcsv($fp, $row, ';') === false) {
                    throw new \RuntimeException('Ошибка записи данных в CSV');
                }
            }
            fclose($fp);
        } catch (\Throwable $e) {
            if (is_resource($fp)) {
                fclose($fp);
            }
            if (file_exists($fullFilePath)) {
                unlink($fullFilePath);
            }
            throw $e;
        } finally {
            if (is_resource($fp)) {
                fclose($fp);
            }
        }

        return $filePath;
    }

    /**
     * Возвращает пакеты данных
     *
     * @return \Generator
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private function getData(): \Generator
    {
        $entity = (HighloadBlockTable::compileEntity(ShortLinkModel::ENTITY_NAME))->getDataClass();
        $offset = 0;

        do{
            $data = $entity::getList([
                'select' => ['*'],
                'limit' => 100,
                'offset' => $offset * 100,
            ])->fetchAll();

            if ($data) {
                yield from $data;
                $offset++;
            }

        } while ($data != false);
    }

}