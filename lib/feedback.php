<?

namespace Imaweb\Tools;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use \Imaweb\Tools\Exceptions\FeedbackSaveException;
use \CIBlockProperty;
use \CIBlockPropertyEnum;
use \CIBlockSection;
use \CIBlockElement;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

class Feedback {
    private $iblockId = 0; /// Идентификатор инфоблока, из которого собран экземпляр класса.
    private $skipValidationFields = []; /// Массив кодов полей, валидацию которых необходимо пропустить

    private $validatorConfig = []; /// Ассоциативный массив с конфигурацией валидатора

    private $data = []; /// Данные к сохранению
    private $errors = []; /// Массив кодов полей, валидация которых завершилась с ошибкой.

    const VALIDATOR_STRING = 1;
    const VALIDATOR_NUMBER = 2;
    const VALIDATOR_LIST = 3;
    const VALIDATOR_EMAIL = 4;
    const VALIDATOR_PHONE = 5;

    public static function factory(int $iblockId): Feedback {
        return new self($iblockId);
    }

    /**
     * IblockFeedback constructor.
     * @param int $iblockId - ИД инфоблока
     */
    public function __construct(int $iblockId) {
        try {
            Loader::includeModule('iblock');
        } catch (LoaderException $e) {
            return;
        }

        $this->iblockId = $iblockId;

        $obFields = CIBlockProperty::GetList([
            'SORT' => 'ASC',
            'NAME' => 'ASC',
        ], [
            'IBLOCK_ID' => $this->iblockId,
            'ACTIVE' => 'Y',
        ]);

        while ($arField = $obFields->GetNext()) {
            if ($arField['IS_REQUIRED'] !== 'Y') {
                $this->skipValidationFields[] = $arField['CODE'];
            }

            switch ($arField['PROPERTY_TYPE']) {
                case 'S':
                {
                    $this->validatorConfig[$arField['CODE']] = array(
                        'NAME' => $arField['NAME'],
                        'REQUIRED' => $arField['IS_REQUIRED'] === 'Y',
                        'TYPE' => Feedback::VALIDATOR_STRING,
                        'MIN_LENGTH' => 1,
                        'MAX_LENGTH' => 255,
                    );
                    break;
                }
                case 'N':
                {
                    $this->validatorConfig[$arField['CODE']] = array(
                        'NAME' => $arField['NAME'],
                        'REQUIRED' => $arField['REQUIRED'] === 'Y',
                        'TYPE' => Feedback::VALIDATOR_LIST,
                        'MIN' => 1,
                        'MAX' => PHP_INT_MAX,
                    );
                    break;
                }
                case 'L':
                {
                    $this->validatorConfig[$arField['CODE']] = array(
                        'NAME' => $arField['NAME'],
                        'REQUIRED' => $arField['REQUIRED'] === 'Y',
                        'TYPE' => Feedback::VALIDATOR_NUMBER,
                        'VALUES' => [],
                    );

                    if (!in_array($arField['CODE'], $this->skipValidationFields)) {
                        $res = (new CIBlockPropertyEnum())->GetList([
                            'SORT' => 'ASC',
                            'NAME' => 'ASC',
                        ], [
                            'IBLOCK_ID' => $this->iblockId,
                            'ACTIVE' => 'Y',
                        ]);

                        while ($r = $res->GetNext()) {
                            $this->validatorConfig[$arField['CODE']]['VALUES'][$r['ID']] = $r['VALUE'];
                        }
                    }

                    break;
                }
                case 'G':
                {
                    $this->validatorConfig[$arField['CODE']] = array(
                        'NAME' => $arField['NAME'],
                        'REQUIRED' => $arField['REQUIRED'] === 'Y',
                        'TYPE' => Feedback::VALIDATOR_LIST,
                        'VALUES' => [],
                    );

                    if (!in_array($arField['CODE'], $this->skipValidationFields)) {
                        $res = (new CIBlockSection())->GetList([
                            'SORT' => 'ASC',
                            'NAME' => 'ASC',
                        ], [
                            'IBLOCK_ID' => $arField['LINK_IBLOCK_ID'],
                            'ACTIVE' => 'Y',
                        ], false, [
                            'ID',
                            'NAME',
                        ]);

                        while ($r = $res->Fetch()) {
                            $this->validatorConfig[$arField['CODE']][$r['ID']] = $r['NAME'];
                        }
                    }

                    break;
                }
                case 'E':
                {
                    $this->validatorConfig[$arField['CODE']] = array(
                        'NAME' => $arField['NAME'],
                        'REQUIRED' => $arField['REQUIRED'] === 'Y',
                        'TYPE' => Feedback::VALIDATOR_LIST,
                        'VALUES' => [],
                    );

                    if (!in_array($arField['CODE'], $this->skipValidationFields)) {
                        $res = (new CIBlockElement())->GetList([
                            'SORT' => 'ASC',
                            'NAME' => 'ASC',
                        ], [
                            'IBLOCK_ID' => $arField['LINK_IBLOCK_ID'],
                            'ACTIVE' => 'Y',
                        ], false, false, [
                            'ID',
                            'NAME',
                        ]);

                        while ($r = $res->Fetch()) {
                            $this->validatorConfig[$arField['CODE']]['VALUES'][$r['ID']] = $r['NAME'];
                        }
                    }

                    break;
                }
                //TODO: multiple props, files
            }
        }

    }

    /**
     * Метод для кастомизации валидации вне класса. Например, указать валидацию по телефону/e-mail
     * (через инфоблок нельзя указать, что это поле должно проверяться именно так).
     * @param $fieldCode - символьный код поля
     * @param $arConfig - массив с конфигурацией.
     * TODO: описать структуру массив $arConfig
     * @return bool
     */
    public function setValidator($fieldCode, $arConfig): bool {
        if (!array_key_exists($fieldCode, $this->validatorConfig)) {
            return false;
        }

        $this->validatorConfig[$fieldCode] = array_merge($this->validatorConfig[$fieldCode], $arConfig);

        return true;
    }

    /**
     * @param array $data Ассоциативный массив данных к сохранению, где ключ - символьный код поля.
     */
    public function setData(array $data): void {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getErrors(): array {
        return $this->errors;
    }

    /**
     * @return array
     */
    public function getValidatorConfig(): array {
        return $this->validatorConfig;
    }

    /**
     * Метод валидации данных
     * @return bool
     */
    public function validate(): bool {
        $this->errors = [];

        foreach ($this->data as $fieldCode => $fieldValue) {
            $fieldValue = (string)$fieldValue;

            if (!array_key_exists($fieldCode, $this->validatorConfig)) {
                unset($this->data[$fieldCode]);
                continue;
            }

            if (in_array($fieldCode, $this->skipValidationFields)) {
                continue;
            }

            $arConfig =& $this->validatorConfig[$fieldCode];
            switch ($arConfig['TYPE']) {
                case Feedback::VALIDATOR_STRING:
                {
                    if (strlen($fieldValue) < $arConfig['MIN_LENGTH'] || strlen($fieldValue) > $arConfig['MAX_LENGTH']) {
                        $this->errors[] = $fieldCode;
                    }

                    break;
                }
                case Feedback::VALIDATOR_NUMBER:
                {
                    $fieldValue = (float)$fieldValue;
                    if ($fieldValue < $arConfig['MIN'] || $fieldValue > $arConfig['MAX']) {
                        $this->errors[] = $fieldCode;
                    }

                    break;
                }
                case Feedback::VALIDATOR_LIST:
                {
                    if (!array_key_exists($fieldValue, $arConfig['VALUES'])) {
                        $this->errors[] = $fieldCode;
                    }
                    break;
                }
                case Feedback::VALIDATOR_EMAIL:
                {
                    if (!filter_var($fieldValue, FILTER_VALIDATE_EMAIL)) {
                        $this->errors[] = $fieldCode;
                    }
                    break;
                }
                case Feedback::VALIDATOR_PHONE:
                {
                    $val = preg_replace('/([^0-9])/', '', $fieldValue);
                    if (strlen($val) < 6) {
                        $this->errors[] = $fieldCode;
                    }
                    break;
                }
            }
        }

        return count($this->errors) == 0;
    }

    /**
     * Метод добавления данных в БД. Внимание! Здесь нет проверки факта вызова валидации!
     * @return bool
     * @throws FeedbackSaveException
     */
    public function save(): bool {
        $arFields = [
            'IBLOCK_ID' => $this->iblockId,
            'ACTIVE' => 'N',
            'NAME' => (new \Bitrix\Main\Type\DateTime())->format('d.m.Y H:i:s'),
            'PROPERTY_VALUES' => $this->data,
        ];

        $el = new CIBlockElement();
        if (!$el->Add($arFields)) {
            throw new FeedbackSaveException($el->LAST_ERROR);
        }

        return true;
    }

    /**
     * @return array
     */
    public function getData(): array {
        return $this->data;
    }
}