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

    private $basePath;

    private $relativePath = '/css/dist/blocks/';

    /**
     * Asset constructor.
     */
    public function __construct($params = []) {
        $this->asset = \Bitrix\Main\Page\Asset::getInstance();

        if (!array_key_exists('basePath', $params)) {
            $params['basePath'] = constant('SITE_TEMPLATE_PATH');
        }

        $this->basePath = $params['basePath'];

        if (array_key_exists('relativePath', $params)) {
            $this->relativePath = $params['relativePath'];
        }
    }

    public static function getInstance($params = null) {
        if (is_null(self::$_instance)) {
            self::$_instance = new self($params);
        }

        return self::$_instance;
    }

    public function __call($name, $arguments) {
        if (!method_exists($this, $name) && method_exists($this->asset, $name)) {
            call_user_func_array([$this->asset, $name], $arguments);
        }
    }

    private function __clone() {

    }

    /**
     * @param string|array $blockName - символьный код блока или массив символьных кодов
     */
    public function addBlock($blockName = ''): void {
        if (is_array($blockName) && !empty($blockName)) {
            foreach ($blockName as $block) {
                $this->_addBlock($block);
            }
        }
        else {
            $this->_addBlock($blockName);
        }
    }

    private function _addBlock(string $blockName = ''): void {
        if (strlen($blockName) == 0) {
            return;
        }

        $this->asset->addCss($this->basePath . $this->relativePath . $blockName . '/style.min.css', true);
    }
}