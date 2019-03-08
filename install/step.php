<?
global $APPLICATION;
IncludeModuleLangFile(__FILE__);
if ($GLOBALS["install_step"] == 2):
	if(!check_bitrix_sessid()) 
		return;

	if($ex = $APPLICATION->GetException())
	{
		$message = new CAdminMessage(array(
			"TYPE" => "ERROR",
			"MESSAGE" => GetMessage("MOD_INST_ERR"),
			"DETAILS" => $ex->GetString(),
			"HTML" => true,
		));
	}
	else
	{
		$message = new CAdminMessage(GetMessage("MOD_INST_OK"));
	}

	echo $message->Show();
?>
<form action="<?echo $APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>" />
	<input type="submit" name="" value="<?=GetMessage("MOD_BACK")?>" />
</form>
<?
	return;
endif;
?>
<form action="<?=$APPLICATION->GetCurPage()?>" name="form1">
<?=bitrix_sessid_post()?>
<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>" />
<input type="hidden" name="id" value="imaweb.tools" />
<input type="hidden" name="install" value="Y" />
<input type="hidden" name="step" value="2" />

<input type="submit" name="inst" value="<?= GetMessage("MOD_INSTALL")?>" />
</form>