<?
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Imaweb\Tools\RedirectTable;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

try {
    Loader::includeModule('imaweb.tools');
}
catch (LoaderException $e) {

}

$errorMessage = null;
$infoMessage = null;

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$step = intval($request->getPost('step'));
if (!$step) {
    $step = 1;
}

if ($request->isPost()) {
    if ($step === 1) {
        // handling uploaded file..
        $dataFile = $request->getPost('DATA_FILE');
        if (!empty($dataFile)) {
            if ($dataFile['error']) {
                $errorMessage = 'Ошибка загрузки файла';
            }

            $path = '/upload/tmp' . $dataFile['tmp_name'];
            if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) {
                $errorMessage = 'Ошибка загрузки файла: файл не сохранён.';
            }

            if (is_null($errorMessage)) {
                $_SESSION['IMPORT_PROCESS'] = [
                    'FILE' => $path,
                ];

                $step++;
            }
        }
        else {
            $errorMessage = 'Загрузите файл';
        }
    }

    if ($step === 2) {
    	$path = $_SERVER['DOCUMENT_ROOT'] . $_SESSION['IMPORT_PROCESS']['FILE'];
        if (file_exists($path)) {
            $f = fopen($path, 'r');
            $i = 0;
            $success = 0;
            $failed = 0;
    	    while (($row = fgetcsv($f, 1024, ';')) !== false) {
    	    	$i++;
    	    	if ($i == 1) {
    	    		continue;
		        }

    	    	$arFields = [
                    'ACTIVE' => 'Y',
			        'SITE_ID' => 's1', //TODO: make as param!
			        'OLD_URL' => $row[0],
			        'NEW_URL' => $row[1],
			        'TYPE' => 301, //TODO: make as a param
		        ];

    	    	//TODO: pretty validation, warnings from import process
    	    	if (!empty($arFields['OLD_URL']) && !empty($arFields['NEW_URL'])) {
    	    		try {
    	    			$result = RedirectTable::add($arFields);
                        if ($result->isSuccess()) {
                            $success++;
                        }
                        else {
                            $failed++;
                        }
                    }
                    catch (\Exception $e) {
    	    			$failed++;
                    }
		        }
    	    }

    	    if ($success + $failed > 0) {
    	    	$infoMessage = sprintf("Импорт завершен.<br/>Добавлено: %d, не удалось добавить: %d", $success, $failed);
	        }
	    }
    }
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if ($step === 1) {
    $APPLICATION->SetTitle('Загрузка файла [beta]');

    // Чистим конфигурацию импорта, если мы начинаем его заново.
    if (!$request->isPost()) {
        unset($_SESSION['IMPORT_PROCESS']);
    }
}
elseif ($step === 2) {
    $APPLICATION->SetTitle('Импорт [beta]');
}

if (!is_null($errorMessage)) {
    $message = new CAdminMessage([
        'TYPE' => 'ERROR',
        'MESSAGE' => $errorMessage,
    ]);

    echo $message->Show();
}

if (!is_null($infoMessage)) {
    $message = new CAdminMessage([
        'TYPE' => 'OK',
        'HTML' => true,
        'MESSAGE' => $infoMessage,
    ]);

    echo $message->Show();
}

$aTabs = array(
    array('DIV' => 'edit1', 'TAB' => 'Загрузка файла', 'ICON' => '', 'TITLE' => 'Загрузите файл с данными.'),
);
$tabControl = new CAdminTabControl('tabControl', $aTabs, false, true);

?>
<form action="" method="POST" enctype="multipart/form-data">
    <?=bitrix_sessid_post()?>
    <?if($step < 3):?>
	    <input type="hidden" name="step" value="<?=$step?>" />
    <?else:?>
	    <input type="hidden" name="step" value="<?=$step+1?>" />
    <?endif;?>

    <? $tabControl->Begin(); ?>

    <? $tabControl->BeginNextTab(); ?>

	<?
	if ($step === 1) {
    ?>
		<tr>
			<td>
                <?
                echo (new CAdminMessage([
                    'TYPE' => 'OK',
                    'HTML' => true,
                    'MESSAGE' => 'Скачайте пример CSV-файла <a href="' . getLocalPath('modules/imaweb.tools/templates/import_redirects.csv') . '" target="_blank">здесь</a>',
                ]))->Show();

                
                echo \Bitrix\Main\UI\FileInput::createInstance([
                    "name" => "DATA_FILE",
                    "description" => false,
                    "upload" => true,
                    "allowUpload" => "F",
                    "medialib" => false,
                    "fileDialog" => true,
                    "cloud" => false,
                    "delete" => false,
                    "maxCount" => 1,
                    "allowUploadExt" => "csv",
                ])->show([], false);
                ?>
			</td>
		</tr>
	<?
	}
	?>
    <? $tabControl->EndTab(); ?>

    <? $tabControl->Buttons(); ?>
        <input type="submit" value="Начать импорт" name="submit_btn" class="adm-btn-save">
</form>
<script type="text/javascript">
    <?if ($step == 1): ?>
    tabControl.SelectTab('edit1');
    tabControl.DisableTab('edit2');
    <?elseif ($step == 2): ?>
    tabControl.SelectTab('edit2');
    tabControl.DisableTab('edit1');
    <?endif; ?>
</script>

<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");