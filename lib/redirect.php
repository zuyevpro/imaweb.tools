<?php
namespace Imaweb\Tools;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;

Loc::loadMessages(__FILE__);

/**
 * Class RedirectTable
 *
 * Fields:
 * <ul>
 * <li> id int mandatory
 * <li> old_url string(255) mandatory
 * <li> new_url string(255) mandatory
 * <li> type unknown mandatory
 * </ul>
 *
 * @package Bitrix\Tools
 **/

class RedirectTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'imaweb_tools_redirect';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return [
            new IntegerField(
                'ID',
                [
                    'primary' => true,
                    'autocomplete' => true,
                    'title' => Loc::getMessage('REDIRECT_ENTITY_ID_FIELD')
                ]
            ),
            new StringField(
                'ACTIVE',
                [
                    'title' => Loc::getMessage('REDIRECT_ENTITY_ACTIVE_FIELD')
                ]
            ),
            new StringField(
                'SITE_ID',
                [
                    'validation' => [__CLASS__, 'validateSiteId'],
                    'title' => Loc::getMessage('REDIRECT_ENTITY_SITE_ID_FIELD')
                ]
            ),

            new StringField(
                'OLD_URL',
                [
                    'validation' => [__CLASS__, 'validateOldUrl'],
                    'title' => Loc::getMessage('REDIRECT_ENTITY_OLD_URL_FIELD')
                ]
            ),
            new StringField(
                'NEW_URL',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateNewUrl'],
                    'title' => Loc::getMessage('REDIRECT_ENTITY_NEW_URL_FIELD')
                ]
            ),
            new IntegerField(
            'TYPE',
				[
                    'required' => true,
                    'title' => Loc::getMessage('REDIRECT_ENTITY_TYPE_FIELD')
                ]
			),
		];
	}

    public static function validateOldUrl()
    {
        return [
            new LengthValidator(null, 255),
        ];
    }

    public static function validateNewUrl()
    {
        return [
            new LengthValidator(null, 255),
        ];
    }

    public static function validateSiteId()
    {
        return [
            new LengthValidator(null, 8),
        ];
    }


}