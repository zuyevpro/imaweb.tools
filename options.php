<?
$module_id = 'imaweb.tools';
$MODULE_RIGHT = $APPLICATION->GetGroupRight($module_id);
$RIGHT_W = $MODULE_RIGHT>="R";

if ($RIGHT_W):

	IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/options.php');
	IncludeModuleLangFile(__FILE__);

	$arAllOptions1 = array(
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
	$tabControl = new CAdminTabControl('tabControl', $aTabs);

	\Bitrix\Main\Loader::includeModule($module_id);

	if ($REQUEST_METHOD == 'POST' && strlen($Update.$Apply.$RestoreDefaults) > 0 && $RIGHT_W && check_bitrix_sessid())
	{
		if (strlen($RestoreDefaults) > 0)
		{
			COption::RemoveOption($module_id);
		}
		else
		{
			foreach($arAllOptions1 as $arOptionGroup)
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

		<?foreach ($arAllOptions1 as $key => $arOptionGroup):
			foreach ($arOptionGroup as $arOption):
				$val = COption::GetOptionString($module_id, $arOption[0]);
		?>
			<?if ($arOption[2][0] == 'string'):?>
			<tr>
				<td><?=$arOption[1]?></td>
				<td>
					<input type="text" name="<?=$arOption[0]?>" value="<?=$val?>" style="width: 308px;" />
				</td>
			</tr>
			<?elseif ($arOption[2][0] == 'password'):?>
			<tr>
				<td><?=$arOption[1]?></td>
				<td>
					<input type="password" name="<?=$arOption[0]?>" value="<?=$val?>" style="width: 308px;" />
				</td>
			</tr>
			<?elseif ($arOption[2][0] == 'checkbox'):?>
			<tr>
				<td><?=$arOption[1]?></td>
				<td>
					<input type="checkbox" name="<?=$arOption[0]?>"<?if ($val == "Y"):?> checked<?endif;?> />
					<div style="width: 350px; height: 1px;"></div>
				</td>
			</tr>
			<?elseif ($arOption[2][0] == 'select'):?>
			<tr>
				<td><?=$arOption[1]?></td>
				<td>
					<select name="<?=$arOption[0]?>" style="width: 320px;">
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
