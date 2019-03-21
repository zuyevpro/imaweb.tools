<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * @global $arResult
 */
?>
<div class="social">
	<?if (strlen($arResult['VK']) > 0):?><a class="social__item social__item_icon_vk" href="<?=$arResult['VK']?>" target="_blank"></a><?endif;?>
	<?if (strlen($arResult['FB']) > 0):?><a class="social__item social__item_icon_fb" href="<?=$arResult['FB']?>" target="_blank"></a><?endif;?>
	<?if (strlen($arResult['TELEGRAM']) > 0):?><a class="social__item social__item_icon_tm" href="<?=$arResult['TELEGRAM']?>" target="_blank"></a><?endif;?>
	<?if (strlen($arResult['WHATSAPP']) > 0):?><a class="social__item social__item_icon_wp" href="<?=$arResult['WHATSAPP']?>" target="_blank"></a><?endif;?>
	<?if (strlen($arResult['INSTAGRAM']) > 0):?><a class="social__item social__item_icon_inst" href="<?=$arResult['INSTAGRAM']?>" target="_blank"></a><?endif;?>
	<?if (strlen($arResult['YOUTUBE']) > 0):?><a class="social__item social__item_icon_yt" href="<?=$arResult['YOUTUBE']?>" target="_blank"></a><?endif;?>
	<?if (strlen($arResult['TWITTER']) > 0):?><a class="social__item social__item_icon_tw" href="<?=$arResult['TWITTER']?>" target="_blank"></a><?endif;?>
	<?if (strlen($arResult['EMAIL']) > 0):?><a class="social__item social__item_icon_mail" href="<?=$arResult['EMAIL']?>" target="_blank"></a><?endif;?>
</div>