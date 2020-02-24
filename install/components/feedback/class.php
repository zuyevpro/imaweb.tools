<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
use Bitrix\Main\Loader,
    Bitrix\Main\LoaderException,
    Imaweb\Tools\Feedback,
    Imaweb\Tools\Exceptions\FeedbackSaveException,
    Bitrix\Main\Application,
    Bitrix\Main\Config\Option,
    Bitrix\Main\Localization\Loc;

class imaweb_feedback extends CBitrixComponent
{

    public function onPrepareComponentParams($arParams)
    {
        $arParams['IBLOCK_ID'] = intval($arParams['IBLOCK_ID']);
        $arParams['CHECK_RECAPTCHA'] = $arParams['CHECK_RECAPTCHA'] === 'Y';
        $arParams['SEND_EVENT'] = $arParams['SEND_EVENT'] === 'Y';
        $arParams['SEND_EVENT_NAME'] = $arParams['SEND_EVENT_NAME'] ?: 'IMAWEB_FEEDBACK_' . $arParams['IBLOCK_ID'];

        return $arParams;
    }

    private function getIblockData()
    {
        return CIBlock::GetByID($this->arParams['IBLOCK_ID'])->GetNext();
    }

    public function executeComponent()
    {
        Loc::loadLanguageFile(__FILE__);

        try {
            Loader::includeModule('iblock');
            Loader::includeModule('imaweb.tools');
        }
        catch (LoaderException $e) {
            $this->arResult['MESSAGE'] = $e->getMessage();
        }

        $feedback = Feedback::factory($this->arParams['IBLOCK_ID']);

        if ($this->arParams['VALIDATE_AS_EMAIL'])
        {
            $feedback->setValidator($this->arParams['VALIDATE_AS_EMAIL'], array(
                'TYPE' => Feedback::VALIDATOR_EMAIL,
            ));
        }

        if ($this->arParams['VALIDATE_AS_PHONE'])
        {
            $feedback->setValidator($this->arParams['VALIDATE_AS_PHONE'], array(
                'TYPE' => Feedback::VALIDATOR_PHONE,
            ));
        }


        $this->arResult['FIELDS'] = $feedback->getValidatorConfig();

        $iblockData = $this->getIblockData();
        $this->arResult['DESCRIPTION'] = $iblockData['DESCRIPTION'];
        $this->arResult['~DESCRIPTION'] = $iblockData['~DESCRIPTION'];

        $request = Application::getInstance()->getContext()->getRequest();

        if ($this->arParams['IBLOCK_ID'] <= 0)
        {
            $this->arResult['MESSAGE'] = Loc::getMessage('IBLOCK_ID_NOT_SET');
        }

        if ($request->isPost() && check_bitrix_sessid())
        {
            $feedback->setData($request->toArray());
            $this->arResult['DATA'] = $feedback->getData();

            $greEnabled = Option::get('imaweb.tools', 'gre_on', 'N') === 'Y';
            $recaptchaChecked = false;
            if ($greEnabled && $this->arParams['CHECK_RECAPTCHA'])
            {
                if (defined('RECAPTCHA_CHECKED') && RECAPTCHA_CHECKED)
                {
                    $recaptchaChecked = true;
                }
            }
            else
            {
                $recaptchaChecked = true;
            }

            $agreementChecked = false;
            if (strlen($this->arParams['CHECK_AGREEMENT_FIELD']) > 0)
            {
                if ($this->arResult['DATA'][$this->arParams['CHECK_AGREEMENT_FIELD']] === 'Y')
                {
                    $agreementChecked = true;
                }
            }

            if ($feedback->validate() && $recaptchaChecked && $agreementChecked)
            {
                try {
                    $this->arResult['SUCCESS'] = $feedback->save();
                }
                catch (FeedbackSaveException $e)
                {
                    $this->arResult['SUCCESS'] = false;
                    $this->arResult['MESSAGE'] = $e->getMessage();
                }
            }
            else
            {
                $this->arResult['ERRORS'] = $feedback->getErrors();
                if (!$recaptchaChecked)
                {
                    $this->arResult['ERRORS'][] = 'RECAPTCHA';
                }

                if (!$agreementChecked)
                {
                    $this->arResult['ERRORS'][] = $this->arParams['CHECK_AGREEMENT_FIELD'];
                }
            }
        }

        if ($this->arResult['SUCCESS'] && $this->arParams['SEND_EVENT'])
        {
            \CEvent::Send($this->arParams['SEND_EVENT_NAME'], SITE_ID, $this->arResult['DATA']);
        }

        $this->includeComponentTemplate($this->arResult['SUCCESS'] ? 'success' : '');
    }
}
