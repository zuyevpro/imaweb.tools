<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Config\Option;

class imaweb_social_links extends CBitrixComponent
{
	public function executeComponent()
	{
		$this->arResult = array(
			'VK' => Option::get('imaweb.tools', 'soc_vk'),
			'FB' => Option::get('imaweb.tools', 'soc_fb'),
			'INSTAGRAM' => Option::get('imaweb.tools', 'soc_instagram'),
			'YOUTUBE' => Option::get('imaweb.tools', 'soc_youtube'),
			'TWITTER' => Option::get('imaweb.tools', 'soc_twitter'),
			'TELEGRAM' => Option::get('imaweb.tools', 'soc_telegram'),
			'WHATSAPP' => Option::get('imaweb.tools', 'soc_whatsapp'),
			'EMAIL' => Option::get('imaweb.tools', 'soc_email'),
		);

		$this->includeComponentTemplate();
	}
}