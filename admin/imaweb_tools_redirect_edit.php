<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Application;
use Imaweb\Tools\RedirectTable;

$arEditableFields = [
    'SITE_ID',
    'ACTIVE',
    'OLD_URL',
    'NEW_URL',
    'TYPE',
];

/**
 * Result of extract() inside of core..
 * @global string $REQUEST_METHOD
 * @global string $action
 * @global string $save
 * @global string $apply
 * @global string $POST_RIGHT
 * @global int $ID
 * @global string $ACTIVE
 * @global string $SITE_ID
 * @global string $OLD_URL
 * @global string $NEW_URL
 * @global string $TYPE
 */

$getListPath = function() {
    return '/bitrix/admin/imaweb_tools_redirects.php?lang=' . LANGUAGE_ID;
};

$getEditPagePath = function ($rowId) {
    return '/bitrix/admin/imaweb_tools_redirect_edit.php?lang=' . LANGUAGE_ID . '&ID=' . $rowId;
};

$userRights = $APPLICATION->GetUserRight($moduleId);

if ($userRights < 'R') {
    $APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}

$APPLICATION->ShowHead();

$APPLICATION->SetTitle($ID > 0 ? 'Изменение редиректа' : 'Создание редиректа');


$arAllSites = [];

$by = 'ID';
$order = 'ASC';

$obSites = CSite::GetList($by, $order, [
    'ACTIVE' => 'Y',
]);

while ($arSite = $obSites->GetNext(true, false)) {
    $arAllSites[$arSite['ID']] = $arSite['NAME'];
}

$arErrors = [];
$arFields = [];

if ($REQUEST_METHOD == "POST" && !empty($save.$apply) && $userRights == "W" && check_bitrix_sessid()) {
    $request = Application::getInstance()->getContext()->getRequest();
    foreach ($request->getPostList()->toArray() as $field => $value) {
        if (in_array($field, $arEditableFields)) {
            $arFields[$field] = $value;
        }
    }

    if (empty($arFields['ACTIVE'])) {
        $arFields['ACTIVE'] = 'N';
    }

    if (empty($arFields['SITE_ID'])) {
        $arErrors['SITE_ID'] = 'Укажите сайт';
    }

    if (empty($arFields['OLD_URL'])) {
        $arErrors['OLD_URL'] = 'Укажите, с какого URL редирект';
    }

    if (empty($arFields['NEW_URL'])) {
        $arErrors['NEW_URL'] = 'Укажите, на какой URL редирект';
    }

    if (empty($arFields['TYPE'])) {
        $arErrors['TYPE'] = 'Укажите тип редиректа';
    }

    if (empty($arErrors)) {
    	if ($ID > 0) {
            $result = RedirectTable::update($ID, $arFields);
	    }
    	else {
            $result = RedirectTable::add($arFields);
        }

        if (!$result->isSuccess()) {
            $arErrors = $result->getErrorMessages();
        }
        else {
            $ID = $result->getId();
            if (empty($apply)) {
                LocalRedirect($getListPath());
            }
            else {
                LocalRedirect($getEditPagePath($ID));
            }
        }
    }
}
elseif ($REQUEST_METHOD == 'GET' && $action == 'delete') {
	$result = RedirectTable::delete($ID);
	if ($result->isSuccess()) {
		LocalRedirect($getListPath());
	}
	else {
		$arErrors = $result->getErrorMessages();
	}
}

$arData = [
    'ACTIVE' => 'Y',
    'TYPE' => '301',
];

if ($ID > 0) {
    $res = RedirectTable::getList([
        'filter' => [
            '=ID' => $ID,
        ]
    ]);

    if ($r = $res->fetch()) {
        foreach ($arEditableFields as $field) {
            $arData[$field] = $r[$field];
        }
    }

    if (!empty($arFields)) {
        $arData = array_merge($arData, $arFields);
    }
}

$topMenu = new CAdminContextMenu([
    [
        'TEXT' => 'К списку',
        'TITLE' => 'Список AB-тестов',
        'LINK' => $getListPath(),
    ],
]);

$tabControl = new CAdminTabControl("tabControl", [
    [
        'DIV' => 'edit1',
        'TAB' => 'AB-тест',
        'TITLE' => 'Информация о редиректе',
    ]
]);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$topMenu->Show();

if (!empty($arErrors)) {
    $mess = new CAdminMessage(implode("\n", $arErrors));
    echo $mess->Show();
}
?>
<div class="adm-workarea">
    <form method="POST" action="<?echo $APPLICATION->GetCurPage()?>" ENCTYPE="multipart/form-data" name="post_form">
        <input type="hidden" name="ID" value="<?=$ID?>" />
        <?=bitrix_sessid_post()?>
        <?
        $tabControl->Begin();
        $tabControl->BeginNextTab();
        ?>
        <tr>
            <td>Активность</td>
            <td>
                <input type="checkbox" name="ACTIVE" value="Y"<?if ($arData['ACTIVE'] == 'Y'):?> checked<?endif;?> />
            </td>
        </tr>
        <?if (count($arAllSites) == 1):?>
        <input type="hidden" name="SITE_ID" value="<?=key($arAllSites)?>" />
        <?else:?>
        <tr>
            <td>
	            <b>Сайт</b>
            </td>
            <td>
                <select name="SITE_ID" style="width: 320px;">
                    <option value="">Выберите сайт</option>
                    <?foreach ($arAllSites as $siteId => $siteName):?>
                    <option
                        value="<?=$siteId?>"
                        <?if ($arData['SITE_ID'] == $siteId):?> selected<?endif;?>
                    ><?=$siteName?></option>
                    <?endforeach;?>
                </select>
            </td>
        </tr>
        <?endif;?>
        <tr>
            <td>
	            <b>Откуда</b>
            </td>
            <td>
                <input type="text" name="OLD_URL" value="<?=$arData['OLD_URL'];?>" size="30" maxlength="255" />
            </td>
        </tr>
        <tr>
            <td>
	            <b>Куда</b>
            </td>
            <td>
                <input type="text" name="NEW_URL" value="<?=$arData['NEW_URL'];?>" size="30" maxlength="255" />
            </td>
        </tr>
        <tr>
            <td>
	            <b>Тип редиректа</b>
            </td>
            <td>
                <select name="TYPE">
                    <option value="301"<?if ($arData['TYPE'] == 301):?> selected<?endif;?>>301</option>
                    <option value="302"<?if ($arData['TYPE'] == 302):?> selected<?endif;?>>302</option>
                </select>
            </td>
        </tr>
        <?
        $tabControl->Buttons(
            [
                "disabled" => ($userRights < "W"),
                "back_url" => $getListPath(),
            ]
        );

        $tabControl->End();
        ?>
    </form>
</div>
<?php

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
