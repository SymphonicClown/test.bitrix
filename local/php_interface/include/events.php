<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

$eventManager = \Bitrix\Main\EventManager::getInstance();
$eventManager->addEventHandlerCompatible(
    'main',
    'OnBeforeUserAdd',
    [
        "Creator",
        "createInfoBlockByElement"
    ],
);
