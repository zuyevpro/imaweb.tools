<?php
namespace Imaweb\Tools;

/**
 * Class Asset
 * @package Imaweb\Tools
 * @warn Нельзя унаследоваться от \Bitrix\Main\Page\Asset из-за реализации getInstance(). Поэтому __call()
 */
class Asset {

    /**
     * @var \Bitrix\Main\Page\Asset
     */
    private $asset;

    private static $_instance;

    /**
     * Asset constructor.
     */
    public function __construct()
    {
        $this->asset = \Bitrix\Main\Page\Asset::getInstance();
    }

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function __call($name, $arguments)
    {
        if (!method_exists($this, $name) && method_exists($this->asset, $name)) {
            call_user_func_array([$this->asset, $name], $arguments);
        }
    }

    private function __clone()
    {

    }

    /**
     * @param string|array $blockName - символьный код блока или массив символьных кодов
     * @param string|null $siteTemplatePath - путь к директории, в которой лежит сборка стилей. По умолчанию SITE_TEMPLATE_PATH
     */
    public function addBlock($blockName = '', string $siteTemplatePath = null): void {
        if (is_array($blockName) && !empty($blockName)) {
            foreach ($blockName as $block) {
                $this->_addBlock($block, $siteTemplatePath);
            }
        }
        else {
            $this->_addBlock($blockName, $siteTemplatePath);
        }
    }

    private function _addBlock(string $blockName = '', string $siteTemplatePath = null): void {
        if (strlen($blockName) == 0) {
            return;
        }

        if (is_null($siteTemplatePath)) {
            $siteTemplatePath = constant('SITE_TEMPLATE_PATH');
        }

        $this->asset->addCss($siteTemplatePath . '/css/dist/blocks/' . $blockName . '/style.css', true);
    }
}