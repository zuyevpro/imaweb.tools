<?php
namespace Imaweb\Tools;

use Bitrix\Main\Web\Json;

class CompareController {
	const COMPARE_NAME = "COMPARE_LIST";

	private static $_instances = []; // iblock id => instance
    private $iblockId;

    private function __construct($iblockId)
    {
        $this->iblockId = $iblockId;
    }


    public static function getInstance($iblockId) {
        if (!self::$_instances[$iblockId]) {
            self::$_instances[$iblockId] = new self($iblockId);
        }

        return self::$_instances[$iblockId];
    }

	public function getCount() {
	    return count($_SESSION[self::COMPARE_NAME][$this->iblockId]['ITEMS']);
    }

    public function getItemIds($returnAsString = false) {
        $result = [];
        foreach ($_SESSION[self::COMPARE_NAME][$this->iblockId]['ITEMS'] as $arItem) {
            $result[] = $arItem['ID'];
        }

        return $returnAsString ? Json::encode($result) : $result;
    }
}