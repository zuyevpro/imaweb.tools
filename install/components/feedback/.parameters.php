<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Loader,
    Bitrix\Main\LoaderException;

try {
    Loader::includeModule("iblock");
}
catch (LoaderException $e) {
    return;
}

$arTypes = CIBlockParameters::GetIBlockTypes();

$arIBlocks = [];

$iblockList = CIBlock::GetList([
    "SORT"=>"ASC"
], [
    "SITE_ID" => $_REQUEST["site"],
    "TYPE" => ($arCurrentValues["IBLOCK_TYPE"] != "-" ? $arCurrentValues["IBLOCK_TYPE"] : ""),
]);

while($arRes = $iblockList->Fetch())
{
    $arIBlocks[$arRes["ID"]] = "[".$arRes["ID"]."] " . $arRes["NAME"];
}

$arComponentParameters = [
    "GROUPS" => [],
    "PARAMETERS" => [
        "IBLOCK_TYPE" => [
            "PARENT" => "BASE",
            "NAME" => GetMessage("IMAWEB_FEEDBACK_IBLOCK_TYPE"),
            "TYPE" => "LIST",
            "VALUES" => $arTypes,
            "DEFAULT" => "news",
            "REFRESH" => "Y",
        ],
        "IBLOCK_ID" => [
            "PARENT" => "BASE",
            "NAME" => GetMessage("IMAWEB_FEEDBACK_IBLOCK_ID"),
            "TYPE" => "LIST",
            "VALUES" => $arIBlocks,
            "DEFAULT" => '',
            "ADDITIONAL_VALUES" => "Y",
            "REFRESH" => "Y",
        ],
        "CHECK_RECAPTCHA" => [
            "PARENT" => "BASE",
            "NAME" => GetMessage("IMAWEB_FEEDBACK_CHECK_RECAPTCHA"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "N",
        ],
        "VALIDATE_AS_EMAIL" => [
            "PARENT" => "BASE",
            "NAME" => GetMessage("IMAWEB_FEEDBACK_VALIDATE_AS_EMAIL"),
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ],
        "VALIDATE_AS_PHONE" => [
            "PARENT" => "BASE",
            "NAME" => GetMessage("IMAWEB_FEEDBACK_VALIDATE_AS_PHONE"),
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ],
        "CHECK_AGREEMENT_FIELD" => [
            "PARENT" => "BASE",
            "NAME" => GetMessage("IMAWEB_FEEDBACK_CHECK_AGREEMENT_FIELD"),
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ],
        "SEND_EVENT" => [
            "PARENT" => "BASE",
            "NAME" => GetMessage("IMAWEB_FEEDBACK_SEND_EVENT"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "N",
        ],
        "SEND_EVENT_NAME" => [
            "PARENT" => "BASE",
            "NAME" => GetMessage("IMAWEB_FEEDBACK_SEND_EVENT_NAME"),
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ],
    ],
];