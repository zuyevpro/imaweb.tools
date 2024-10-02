<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

define('GRID_ID', 'imaweb_redirects');

$APPLICATION->SetTitle("Редиректы");

$arDisplayColumns = [
    [
        'id' => 'ID',
        'name' => 'ID',
        'sort' => 'ID',
        'default' => true,
    ],
    [
        'id' => 'ACTIVE',
        'name' => 'Активность',
        'sort' => 'ACTIVE',
        'default' => true,
    ],
	[
        'id' => 'SITE_ID',
        'name' => 'Сайт',
        'sort' => 'SITE_ID',
        'default' => true,
    ],
    [
        'id' => 'OLD_URL',
        'name' => 'Откуда',
        'sort' => 'OLD_URL',
        'default' => true,
    ],
    [
        'id' => 'NEW_URL',
        'name' => 'Куда',
        'sort' => 'NEW_URL',
        'default' => true,
    ],
    [
        'id' => 'TYPE',
        'name' => 'Тип',
        'sort' => 'TYPE',
        'default' => true,
    ],
];

$errorMessage = null;

$gridOptions = new Bitrix\Main\Grid\Options(GRID_ID);
$filterOption = new Bitrix\Main\UI\Filter\Options(GRID_ID);

$sort = $gridOptions->GetSorting([
    'sort' => [
        'ID' => 'DESC'
    ],
    'vars' => [
        'by' => 'by',
        'order' => 'order'
    ]
]);

$filter = $filterOption->getFilter([]);
if (array_key_exists('FIND', $filter) && !empty($filter['FIND'])) {
    $filter['OLD_URL'] = '%' . $filter['FIND'] . '%';
    $filter['NEW_URL'] = '%' . $filter['FIND'] . '%';
    unset($filter['FIND']);
}

$navParams = $gridOptions->GetNavParams();

$nav = new Bitrix\Main\UI\PageNavigation(GRID_ID);
$nav->allowAllRecords(true)
    ->setPageSize($navParams['nPageSize'])
    ->initFromUri();

if ($nav->allRecordsShown()) {
    $navParams = false;
} else {
    $navParams['iNumPage'] = $nav->getCurrentPage();
}

$arAllSites = [];

$by = 'ID';
$order = 'ASC';

$obSites = CSite::GetList($by, $order, [
    'ACTIVE' => 'Y',
]);

while ($arSite = $obSites->GetNext(true, false)) {
    $arAllSites[$arSite['ID']] = $arSite['NAME'];
}

$filterSettings = [
    [
        'id' => 'OLD_URL',
        'name' => 'Откуда',
        'type' => 'string',
    ],
    [
        'id' => 'NEW_URL',
        'name' => 'Куда',
        'type' => 'string',
    ],
    [
        'id' => 'TYPE',
        'name' => 'Тип редиректа',
        'type' => 'list',
        'items' => [
            301 => 301,
            302 => 302,
        ]
    ],

];

if (count($arAllSites) > 1) {
    array_unshift($arAllSites, [
        'id' => 'SITE_ID',
        'name' => 'Сайт',
        'items' => $arAllSites,
    ]);
}

$arRows = [];

// Первый запрос для построения постранички, второй - для получения данных
$obRows = \Imaweb\Tools\RedirectTable::getList([
    'order' => $sort['sort'],
    'filter' => $filter,
    'select' => [
        'ID',
    ],
]);

$nav->setRecordCount($obRows->getSelectedRowsCount());

$obRows = \Imaweb\Tools\RedirectTable::getList([
    'order' => $sort['sort'],
    'filter' => $filter,
    'limit' => $navParams['nPageSize'],
    'offset' => $navParams['nPageSize'] * ($navParams['iNumPage']-1)
]);

while ($arRow = $obRows->fetch()) {

	$arRow['ACTIVE'] = $arRow['ACTIVE'] == 'Y' ? 'Да' : 'Нет';

    $arRows[$arRow['ID']] = [
        'data' => $arRow,
        'actions' => [
            [
                'text' => 'Редактировать',
                'onclick' => "location.href = '/bitrix/admin/imaweb_tools_redirect_edit.php?lang={LANGUAGE_ID}&ID={$arRow['ID']}';",
            ],
	        [
                'text' => 'Удалить',
                'onclick' => "location.href = '/bitrix/admin/imaweb_tools_redirect_edit.php?lang={LANGUAGE_ID}&ID={$arRow['ID']}&action=delete';",
            ],

        ],
    ];
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

//if (!is_null($errorMessage)) {
//    echo $errorMessage->Show();
//}
?>
<div class="adm-toolbar-panel-container">
    <div class="adm-toolbar-panel-flexible-space">
        <?$APPLICATION->IncludeComponent('bitrix:main.ui.filter', '', [
            'FILTER_ID' => GRID_ID,
            'GRID_ID' => GRID_ID,
            'FILTER' => $filterSettings,
            'ENABLE_LIVE_SEARCH' => true,
            'ENABLE_LABEL' => true
        ]);?>
    </div>
    <div class="adm-toolbar-panel-align-right">
        <div class="btn-panel ui-btn-primary">
            <a class="ui-btn-main" href="/bitrix/admin/imaweb_tools_redirect_edit.php?lang=<?=LANGUAGE_ID?>">Добавить</a>
	        <a class="ui-btn-main" href="/bitrix/admin/imaweb_tools_redirects_import.php?lang=<?=LANGUAGE_ID?>">Импорт</a>
        </div>

    </div>
</div>

<?$APPLICATION->IncludeComponent('bitrix:main.ui.grid', '', [
    'GRID_ID' => GRID_ID,
    'COLUMNS' => $arDisplayColumns,
    'ROWS' => $arRows,
    'SHOW_ROW_CHECKBOXES' => false,
    'NAV_OBJECT' => $nav,
    'AJAX_MODE' => 'Y',
    'AJAX_ID' => \CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''),
    'PAGE_SIZES' => [
        ['NAME' => "5", 'VALUE' => '5'],
        ['NAME' => '10', 'VALUE' => '10'],
        ['NAME' => '20', 'VALUE' => '20'],
        ['NAME' => '50', 'VALUE' => '50'],
        ['NAME' => '100', 'VALUE' => '100']
    ],
    'AJAX_OPTION_JUMP' => 'N',
    'SHOW_CHECK_ALL_CHECKBOXES' => false,
    'SHOW_ROW_ACTIONS_MENU' => true,
    'SHOW_GRID_SETTINGS_MENU' => true,
    'SHOW_NAVIGATION_PANEL' => true,
    'SHOW_PAGINATION' => true,
    'SHOW_SELECTED_COUNTER' => false,
    'SHOW_TOTAL_COUNTER' => false,
    'SHOW_PAGESIZE' => true,
    'SHOW_ACTION_PANEL' => true,
    'ACTION_PANEL' => [],
    'ALLOW_COLUMNS_SORT' => true,
    'ALLOW_COLUMNS_RESIZE' => true,
    'ALLOW_HORIZONTAL_SCROLL' => true,
    'ALLOW_SORT' => true,
    'ALLOW_PIN_HEADER' => true,
    'AJAX_OPTION_HISTORY' => 'N'
]);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");

