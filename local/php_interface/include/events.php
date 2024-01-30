<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

$eventManager = Bitrix\Main\EventManager::getInstance();

$eventManager->addEventHandler(
    "iblock",
    "OnAfterIBlockElementAdd",
    function (&$fields) {
        if (empty($fields["RESULT"])) return false;

        $IBTypeOne = "type_1";
        $IBTypeTwo = "type_2";
        $ib = new CIBlock;

        $arIblockTypeOne = \Bitrix\Iblock\IblockTable::getList(array(
            'filter' => array('ID' => $fields["IBLOCK_ID"])
        ))->fetch();

        // Проверяем что это нужный инфоблок
        if (($arIblockTypeOne["IBLOCK_TYPE_ID"] !==  $IBTypeOne)
            && ($arIblockTypeOne["CODE"] !==  $IBTypeOne)
        ) return false;

        //===================================//
        // Создаем инфоблок //
        //===================================//

        // Настройка доступа
        $arAccess = array(
            "2" => "R", // Все пользователи
        );
        $arFields = array(
            "ACTIVE" => "Y",
            "NAME" => $fields["NAME"],
            "CODE" => $fields["CODE"],
            "IBLOCK_TYPE_ID" => $IBTypeTwo,
            "SITE_ID" => $arIblockTypeOne["LID"],
            "SORT" => "5",
            "GROUP_ID" => $arAccess, // Права доступа
            "FIELDS" => array(
                // Символьный код элементов
                "CODE" => array(
                    "IS_REQUIRED" => "Y", // Обязательное
                    "DEFAULT_VALUE" => array(
                        "UNIQUE" => "Y", // Проверять на уникальность
                        "TRANSLITERATION" => "Y", // Транслитерировать
                        "TRANS_LEN" => "30", // Максмальная длина транслитерации
                        "TRANS_CASE" => "L", // Приводить к нижнему регистру
                        "TRANS_SPACE" => "-", // Символы для замены
                        "TRANS_OTHER" => "-",
                        "TRANS_EAT" => "Y",
                        "USE_GOOGLE" => "N",
                    ),
                ),
                // Символьный код разделов
                // "SECTION_CODE" => array(
                //     "IS_REQUIRED" => "Y",
                //     "DEFAULT_VALUE" => array(
                //         "UNIQUE" => "Y",
                //         "TRANSLITERATION" => "Y",
                //         "TRANS_LEN" => "30",
                //         "TRANS_CASE" => "L",
                //         "TRANS_SPACE" => "-",
                //         "TRANS_OTHER" => "-",
                //         "TRANS_EAT" => "Y",
                //         "USE_GOOGLE" => "N",
                //     ),
                // ),
                "DETAIL_TEXT_TYPE" => array(      // Тип детального описания
                    "DEFAULT_VALUE" => "html",
                ),
                "SECTION_DESCRIPTION_TYPE" => array(
                    "DEFAULT_VALUE" => "html",
                ),
                "IBLOCK_SECTION" => array(         // Привязка к разделам обязательноа
                    "IS_REQUIRED" => "Y",
                ),
                "LOG_SECTION_ADD" => array("IS_REQUIRED" => "Y"), // Журналирование
                "LOG_SECTION_EDIT" => array("IS_REQUIRED" => "Y"),
                "LOG_SECTION_DELETE" => array("IS_REQUIRED" => "Y"),
                "LOG_ELEMENT_ADD" => array("IS_REQUIRED" => "Y"),
                "LOG_ELEMENT_EDIT" => array("IS_REQUIRED" => "Y"),
                "LOG_ELEMENT_DELETE" => array("IS_REQUIRED" => "Y"),
            ),

            // Шаблоны страниц
            "LIST_PAGE_URL" => "#SITE_DIR#/catalog/",
            "SECTION_PAGE_URL" => "#SITE_DIR#/catalog/#SECTION_CODE#/",
            "DETAIL_PAGE_URL" => "#SITE_DIR#/catalog/#SECTION_CODE#/#ELEMENT_CODE#/",

            "VERSION" => 2, // Хранение элементов в общей таблице

            "ELEMENT_NAME" => "Элемент",
            "ELEMENTS_NAME" => "Элементы",
            "ELEMENT_ADD" => "Добавить элемент",
            "ELEMENT_EDIT" => "Изменить элемент",
            "ELEMENT_DELETE" => "Удалить элемент",
            "SECTION_NAME" => "Категории",
            "SECTIONS_NAME" => "Категория",
            "SECTION_ADD" => "Добавить категорию",
            "SECTION_EDIT" => "Изменить категорию",
            "SECTION_DELETE" => "Удалить категорию",
        );

        $ID = $ib->Add($arFields);
        if ($ID > 0) {
            $message = "&mdash; инфоблок \"Каталог товаров\" успешно создан<br />";
        } else {
            $message = "&mdash; ошибка создания инфоблока \"Каталог товаров\"<br />";
            // return false;
        }
        $qwe[] = $message;


        //=======================================//
        // Добавляем свойства //
        //=======================================//

        // Определяем, есть ли у инфоблока свойства
        $dbProperties = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $ID));
        if ($dbProperties->SelectedRowsCount() <= 0) {
            $ibp = new CIBlockProperty;

            //Строка
            $arPropertyFields = [
                "NAME" => "Строка",
                "ACTIVE" => "Y",
                "SORT" => 500,
                "CODE" => "TEXT",
                "PROPERTY_TYPE" => "S", // Строка
                "ROW_COUNT" => 3, // Количество строк
                "COL_COUNT" => 70, // Количество столбцов
                "IBLOCK_ID" => $ID,
                "HINT" => "",
            ];
            $propId = $ibp->Add($arPropertyFields);
            if ($propId > 0) {
                $arPropertyFields["ID"] = $propId;
                $arCommonProps[$arPropertyFields["CODE"]] = $arPropertyFields;
                $message = "&mdash; Добавлено свойство " . $arPropertyFields["NAME"] . "<br />";
            } else
                $message = "&mdash; Ошибка добавления свойства " . $arPropertyFields["NAME"] . "<br />";


            // Файл
            $arPropertyFields = [
                "NAME" => "Файл",
                "ACTIVE" => "Y",
                "MULTIPLE" => "N",
                "SORT" => 500,
                "CODE" => "FILE",
                "PROPERTY_TYPE" => "F", // Файл
                "FILE_TYPE" => "jpg, gif, bmp, png, jpeg",
                "IBLOCK_ID" => $ID,
                "HINT" => "",
            ];
            $propId = $ibp->Add($arPropertyFields);
            if ($propId > 0) {
                $arPropertyFields["ID"] = $propId;
                $arCommonProps[$arPropertyFields["CODE"]] = $arPropertyFields;
                $message = "&mdash; Добавлено свойство " . $arPropertyFields["NAME"] . "<br />";
            } else
                $message = "&mdash; Ошибка добавления свойства " . $arPropertyFields["NAME"] . "<br />";


            // да/нет
            $arPropertyFields = [
                "NAME" => "Да/нет",
                "ACTIVE" => "Y",
                "SORT" => 500, // Сортировка
                "CODE" => "YES",
                "PROPERTY_TYPE" => "L", // Список
                "LIST_TYPE" => "C", // Тип списка - "флажки"
                "FILTRABLE" => "Y", // Выводить на странице списка элементов поле для фильтрации по этому свойству
                "VALUES" => [
                    "VALUE" => "Y",
                ],
                "IBLOCK_ID" => $ID
            ];
            $propId = $ibp->Add($arPropertyFields);
            if ($propId > 0) {
                $arPropertyFields["ID"] = $propId;
                $arCommonProps[$arPropertyFields["CODE"]] = $arPropertyFields;
                $message = "&mdash; Добавлено свойство " . $arPropertyFields["NAME"] . "<br />";
            } else
                $message = "&mdash; Ошибка добавления свойства " . $arPropertyFields["NAME"] . "<br />";


            // Справочник
            $arPropertyFields = [
                "NAME" => "Справочник",
                "ACTIVE" => "Y",
                "SORT" => 500, // Сортировка
                "CODE" => "LIST",
                "PROPERTY_TYPE" => "L", // Список
                "LIST_TYPE" => "C", // Тип списка - "флажки"
                "FILTRABLE" => "Y", // Выводить на странице списка элементов поле для фильтрации по этому свойству
                "VALUES" => [
                    "1111",
                    "2222",
                    "3333",
                    "4444",
                ],
                "IBLOCK_ID" => $ID
            ];
            $propId = $ibp->Add($arPropertyFields);
            if ($propId > 0) {
                $arPropertyFields["ID"] = $propId;
                $arCommonProps[$arPropertyFields["CODE"]] = $arPropertyFields;
                $message = "&mdash; Добавлено свойство " . $arPropertyFields["NAME"] . "<br />";
            } else
                $message = "&mdash; Ошибка добавления свойства " . $arPropertyFields["NAME"] . "<br />";
        } else
            $message = "&mdash; Для данного инфоблока уже существуют свойства<br />";
    }
);
