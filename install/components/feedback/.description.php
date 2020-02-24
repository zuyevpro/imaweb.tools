<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
    "NAME" => GetMessage("IMAWEB_FEEDBACK_COMPONENT_NAME"),
    "DESCRIPTION" => GetMessage("IMAWEB_FEEDBACK_COMPONENT_DESCRIPTION"),
    "ICON" => "/images/news_detail.gif",
    "SORT" => 10,
    "CACHE_PATH" => "Y",
    "PATH" => array(
        "ID" => "content",
        "CHILD" => array(
            "ID" => "feedback",
            "NAME" => GetMessage("IMAWEB_FEEDBACK_COMPONENT_NAME"),
            "SORT" => 10,
        ),
    ),
);