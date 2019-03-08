<?
global $APPLICATION;
IncludeModuleLangFile(__FILE__); 
if ($GLOBALS["uninstall_step"] == 2):
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
<form action="<?=$APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>" />
	<input type="submit" name="" value="<?=GetMessage("MOD_BACK")?>" />
</form>
<?
	return;
endif;
?>
<form action="<?echo $APPLICATION->GetCurPage()?>">
<?=bitrix_sessid_post()?>
	<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>" />
	<input type="hidden" name="id" value="imaweb.tools" />
	<input type="hidden" name="uninstall" value="Y" />
	<input type="hidden" name="step" value="2" />
	<?
	$message = new CAdminMessage(GetMessage("MOD_UNINST_WARN"));
	echo $message->Show();
	?>
	<p>
		<label>
			<input type="checkbox" name="savedata" id="savedata" value="Y" />
			<?=GetMessage('MOD_UNINST_SAVEDATA')?>
		</label>
	</p>
	<input type="submit" name="inst" value="<?=GetMessage("MOD_UNINST_DEL")?>" />
</form>