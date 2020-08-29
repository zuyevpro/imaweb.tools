<?
/**
 * @global string $REQUEST_METHOD
 * @global string $Apply
 * @global string $Update
 * @global string $RestoreDefaults
 */
use \Bitrix\Main\Loader;

$module_id = 'imaweb.tools';
$MODULE_RIGHT = $APPLICATION->GetGroupRight($module_id);
$RIGHT_W = $MODULE_RIGHT>="R";

if ($RIGHT_W):

    IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/options.php');
    IncludeModuleLangFile(__FILE__);

    $path = getLocalPath('php_interface/include/imaweb.tools/get_options.php');
    if ($path) {
        $path = $_SERVER['DOCUMENT_ROOT'] . $path;
    }
    else {
        $path = __DIR__ . '/get_options.php';
    }

    list($aTabs, $arAllOptions) = require($path);

    $tabControl = new CAdminTabControl('tabControl', $aTabs);

    Loader::includeModule($module_id);

    if ($REQUEST_METHOD == 'POST' && strlen($Update.$Apply.$RestoreDefaults) > 0 && $RIGHT_W && check_bitrix_sessid())
    {
        if (strlen($RestoreDefaults) > 0)
        {
            COption::RemoveOption($module_id);
        }
        else
        {
            foreach($arAllOptions as $arOptionGroup)
            {
                foreach ($arOptionGroup as $arOption)
                {
                    $name = $arOption[0];
                    $val = trim($_REQUEST[$name], " \t\n\r");
                    if ($arOption[2][0] == 'checkbox')
                    {
                        $val = $val == 'on' ? 'Y' : 'N';
                    }
                    COption::SetOptionString($module_id, $name, $val, $arOption[1]);
                }
            }


        }

        ob_start();
        $Update = $Update.$Apply;
        require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/admin/group_rights2.php');
        ob_end_clean();

        if (strlen($_REQUEST['back_url_settings']) > 0)
        {
            if ((strlen($Apply) > 0) || (strlen($RestoreDefaults) > 0))
                LocalRedirect($APPLICATION->GetCurPage() . '?mid=' . urlencode($module_id) . '&lang=' . urlencode(LANGUAGE_ID) . '&back_url_settings=' . urlencode($_REQUEST['back_url_settings']) . '&' . $tabControl->ActiveTabParam());
            else
                LocalRedirect($_REQUEST['back_url_settings']);
        }
        else
        {
            LocalRedirect($APPLICATION->GetCurPage() . '?mid=' . urlencode($module_id) . '&lang=' . urlencode(LANGUAGE_ID) . '&' . $tabControl->ActiveTabParam());
        }
    }
    ?>
	<form method="post" action="<?=$APPLICATION->GetCurPage()?>?mid=<?=urlencode($module_id)?>&amp;lang=<?=LANGUAGE_ID?>">
        <?
        $tabControl->Begin();
        $tabControl->BeginNextTab();?>

        <?foreach ($arAllOptions as $key => $arOptionGroup):
            foreach ($arOptionGroup as $arOption):
                $val = COption::GetOptionString($module_id, $arOption[0]);

                $paramHtmlId = 'param_' . rand(1,99999);
                ?>
                <?if ($arOption[2][0] == 'string'):?>
				<tr>
					<td>
						<label for="<?=$paramHtmlId?>">
                            <?=$arOption[1]?>
						</label>
					</td>
					<td>
						<input type="text" name="<?=$arOption[0]?>" value="<?=$val?>" style="width: 308px;" id="<?=$paramHtmlId?>" />
					</td>
				</tr>
            <?elseif ($arOption[2][0] == 'password'):?>
				<tr>
					<td>
						<label for="<?=$paramHtmlId?>">
                            <?=$arOption[1]?>
						</label>
					</td>
					<td>
						<input type="password" name="<?=$arOption[0]?>" value="<?=$val?>" style="width: 308px;" id="<?=$paramHtmlId?>" />
					</td>
				</tr>
            <?elseif ($arOption[2][0] == 'checkbox'):?>
				<tr>
					<td>
						<label for="<?=$paramHtmlId?>">
                            <?=$arOption[1]?>
						</label>
					</td>
					<td>
						<input type="checkbox" name="<?=$arOption[0]?>"<?if ($val == "Y"):?> checked<?endif;?> id="<?=$paramHtmlId?>" />
						<div style="width: 350px; height: 1px;"></div>
					</td>
				</tr>
            <?elseif ($arOption[2][0] == 'select'):?>
				<tr>
					<td>
						<label for="<?=$paramHtmlId?>">
                            <?=$arOption[1]?>
						</label>
					</td>
					<td>
						<select name="<?=$arOption[0]?>" style="width: 320px;" id="<?=$paramHtmlId?>">
							<option value=""></option>
                            <?foreach($arOption[2]['values'] as $value => $valueTitle):?>
								<option value="<?=$value?>"<?if ($val == $value):?> selected<?endif?>><?=$valueTitle?></option>
                            <?endforeach;?>
						</select>
					</td>
				</tr>
            <?endif;?>
            <?
            endforeach;
            $tabControl->BeginNextTab();
        endforeach;?>

        <?require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/admin/group_rights2.php');?>
        <?$tabControl->Buttons();?>
		<input <?if (!$RIGHT_W) echo 'disabled' ?> type="submit" name="Update" value="<?=GetMessage('MAIN_SAVE')?>" title="<?=GetMessage('MAIN_OPT_SAVE_TITLE')?>" class="adm-btn-save">
		<input <?if (!$RIGHT_W) echo 'disabled' ?> type="submit" name="Apply" value="<?=GetMessage('MAIN_OPT_APPLY')?>" title="<?=GetMessage('MAIN_OPT_APPLY_TITLE')?>">
        <?if (strlen($_REQUEST['back_url_settings'])>0):?>
			<input <?if (!$RIGHT_W) echo 'disabled' ?> type="button" name="Cancel" value="<?=GetMessage('MAIN_OPT_CANCEL')?>" title="<?=GetMessage('MAIN_OPT_CANCEL_TITLE')?>" onclick="window.location='<?=htmlspecialcharsbx(CUtil::addslashes($_REQUEST['back_url_settings']))?>'">
			<input type="hidden" name="back_url_settings" value="<?=htmlspecialcharsbx($_REQUEST['back_url_settings'])?>">
        <?endif?>
		<input <?if (!$RIGHT_W) echo 'disabled' ?> type="submit" name="RestoreDefaults" title="<?=GetMessage('MAIN_HINT_RESTORE_DEFAULTS')?>" OnClick="confirm('<?echo AddSlashes(GetMessage('MAIN_HINT_RESTORE_DEFAULTS_WARNING'))?>')" value="<?echo GetMessage('MAIN_RESTORE_DEFAULTS')?>">
        <?=bitrix_sessid_post();?>
        <?$tabControl->End();?>
	</form>
<?endif;?>
