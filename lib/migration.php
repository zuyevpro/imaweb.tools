<?php

namespace Imaweb\Tools;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\Validator;
use Bitrix\Main\ArgumentException;

class MigrationTable extends DataManager {
    public static function getTableName() {
        return 'imaweb_tools_migrations';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap() {
        return array(
            'ID' => array(
                'data_type' => 'integer',
                'primary' => true,
                'autocomplete' => true,
                'title' => 'ID',
            ),
            'NAME' => array(
                'data_type' => 'string',
                'validation' => array(__CLASS__, 'validateName'),
                'title' => 'Name',
            ),
            'RUN_AT' => array(
                'data_type' => 'datetime',
                'title' => 'Run at',
            ),
        );
    }

    /**
     * Returns validators for NAME field.
     *
     * @return array
     */
    public static function validateName() {
        try {
            return array(
                new Validator\Length(null, 255),
            );
        } catch (ArgumentException $e) {
            return array();
        }
    }
}