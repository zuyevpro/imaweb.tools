<?php
namespace Imaweb\Tools;

use Bitrix\Main\Web\Json;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;

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

        $sectionIds = [];
        $cached = null;
        $cache = \Bitrix\Main\Application::getInstance()->getCache();
        if ($cache->initCache(86486400, md5('all_iblock_sections_' . $this->iblockId), '/catalog'))
        {
            $sectionIds = $cache->getVars();
        }
        elseif ($cache->startDataCache())
        {
            $res = \CIBlockSection::GetList([], [
                'IBLOCK_ID' => $this->iblockId,
                'ACTIVE' => 'Y',
            ], false, [
                'ID',
            ]);

            while ($r = $res->Fetch()) {
                $sectionIds[] = $r['ID'];
            }

        	if (is_array($sectionIds))
        	{
        		$taggedCache = \Bitrix\Main\Application::getInstance()->getTaggedCache();

                $taggedCache->startTagCache('/catalog');
                $taggedCache->registerTag('iblock_id_' . $this->iblockId);
                $taggedCache->endTagCache();
        	}

        	$cache->endDataCache($sectionIds);
        }

        foreach ($sectionIds as $sectionId) {
            foreach ($_SESSION[self::COMPARE_NAME . '_SECTION_' . $sectionId][$this->iblockId]['ITEMS'] as $arItem) {
                $result[] = $arItem['ID'];
            }
        }

        return $returnAsString ? Json::encode($result) : $result;
    }

    public function getCatalogSections() {
        $result = [];
        $ids = $this->getItemIds();
        if (!empty($ids)) {
            try {
                Loader::includeModule('iblock');
            }
            catch (LoaderException $e) {
                return $result;
            }

            $res = \CIBlockElement::GetList([], [
                'IBLOCK_ID' => $this->iblockId,
                'ID' => $ids,
            ], false, false, [
                'IBLOCK_SECTION_ID',
            ]);

            while ($r = $res->Fetch()) {
                $result[$r['IBLOCK_SECTION_ID']] = false;
            }

            $result = array_keys($result);
        }

        return $result;
    }
}