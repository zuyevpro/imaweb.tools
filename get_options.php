<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arAllOptions = array(
    array(
        array(
            "custom_js_css",
            GetMessage('IMAWEB_TOOLS_ADD_CUSTOM_JS_CSS'),
            array(
                "checkbox",
            ),
        ),
        array(
            "set_default_iblock_rights",
            GetMessage('IMAWEB_TOOLS_SET_DEFAULT_IBLOCK_RIGHTS'),
            array(
                "checkbox",
            ),
        ),
        array(
            "show_migrations_in_menu",
            GetMessage('IMAWEB_TOOLS_SHOW_MIGRATIONS_IN_MENU'),
            array(
                "checkbox",
            ),
        ),
    ),
    array(
        array(
            "soc_vk",
            GetMessage('IMAWEB_TOOLS_SOC_LINK_VK'),
            array(
                "string",
            ),
        ),
        array(
            "soc_fb",
            GetMessage('IMAWEB_TOOLS_SOC_LINK_FB'),
            array(
                "string",
            ),
        ),
        array(
            "soc_youtube",
            GetMessage('IMAWEB_TOOLS_SOC_LINK_YOUTUBE'),
            array(
                "string",
            ),
        ),
        array(
            "soc_twitter",
            GetMessage('IMAWEB_TOOLS_SOC_LINK_TWITTER'),
            array(
                "string",
            ),
        ),
        array(
            "soc_instagram",
            GetMessage('IMAWEB_TOOLS_SOC_LINK_INSTAGRAM'),
            array(
                "string",
            ),
        ),
        array(
            "soc_whatsapp",
            GetMessage('IMAWEB_TOOLS_SOC_LINK_WHATSAPP'),
            array(
                "string",
            ),
        ),
        array(
            "soc_telegram",
            GetMessage('IMAWEB_TOOLS_SOC_LINK_TELEGRAM'),
            array(
                "string",
            ),
        ),
        array(
            "soc_email",
            GetMessage('IMAWEB_TOOLS_SOC_LINK_EMAIL'),
            array(
                "string",
            ),
        ),
    ),
    array(
        array(
            "gre_on",
            GetMessage('IMAWEB_TOOLS_GRE_ON'),
            array(
                "checkbox",
            ),
        ),
        array(
            "gre_key",
            GetMessage('IMAWEB_TOOLS_GRE_KEY'),
            array(
                "string",
            ),
        ),
        array(
            "gre_secret",
            GetMessage('IMAWEB_TOOLS_GRE_SECRET'),
            array(
                "string",
            ),
        ),
    ),

);



$aTabs = array(
    array('DIV' => 'edit0', 'TAB' => GetMessage('IMAWEB_TOOLS_MAIN'), 'ICON' => '', 'TITLE' => GetMessage('IMAWEB_TOOLS_MAIN_SETTINGS')),
    array('DIV' => 'edit1', 'TAB' => GetMessage('IMAWEB_TOOLS_SOC_LINKS'), 'ICON' => '', 'TITLE' => GetMessage('IMAWEB_TOOLS_SOC_LINKS')),
    array('DIV' => 'edit2', 'TAB' => GetMessage('IMAWEB_TOOLS_GRE'), 'ICON' => '', 'TITLE' => GetMessage('IMAWEB_TOOLS_GRE')),
    array('DIV' => 'edit3', 'TAB' => GetMessage('MAIN_TAB_RIGHTS'), 'ICON' => '', 'TITLE' => GetMessage('MAIN_TAB_TITLE_RIGHTS')),
);

return [
    $aTabs,
    $arAllOptions,
];