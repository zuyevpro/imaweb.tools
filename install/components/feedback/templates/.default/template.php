<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Imaweb\Tools\Feedback,
    Bitrix\Main\Config\Option;

/**
 * @var array $arParams
 * @var array $arResult
 * @global CMain $APPLICATION
 * @global CUser $USER
 * @global CDatabase $DB
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $templateFile
 * @var string $templateFolder
 * @var string $componentPath
 * @var imaweb_feedback $component
 */

if (strlen($arResult['MESSAGE']) > 0)
{
    $component->__showError($arResult['MESSAGE']);
}

$greEnabled = Bitrix\Main\Config\Option::get('imaweb.tools', 'gre_on') == 'Y' && $arParams['CHECK_RECAPTCHA'];
if ($greEnabled)
{
    $greKey = Bitrix\Main\Config\Option::get('imaweb.tools', 'gre_key');
}
?>
<div class="form">
    <form method="post" action="">
        <?=bitrix_sessid_post()?>
        <div class="center form__description">
            <?=$arResult['DESCRIPTION']?>
        </div>
        <div class="form__fields">
        <?foreach ($arResult['FIELDS'] as $fieldCode => $arField):
            $bError = in_array($fieldCode, $arResult['ERRORS']);
        ?>
            <div class="group<?if ($bError):?> has-error<?endif;?>">
                <div class="row row_collapsed">
                    <div class="caption">
                        <label class="label" for="field_<?=$fieldCode?>">
                            <?=$arField['NAME']?>
                        </label>
                    </div>
                    <div class="field">
                        <?if ($arField['TYPE'] == Feedback::VALIDATOR_NUMBER):?>
                            <input
                                type="number"
                                id="field_<?=$fieldCode?>"
                                class="control"
                                name="<?=$fieldCode?>"
                                <?if ($arField['REQUIRED']):?> required<?endif;?>
                                value="<?=$arResult['DATA'][$fieldCode]?>"
                            />
                        <?elseif ($arField['TYPE'] == Feedback::VALIDATOR_LIST):?>
                            <select
                                class="control"
                                id="field_<?=$fieldCode?>"
                                name="<?=$fieldCode?>"
                                <?if ($arField['REQUIRED']):?> required<?endif;?>
                            >
                                <option value=""><?=GetMessage('SELECT_CHOOSE')?></option>
                                <?foreach ($arField['VALUES'] as $valueId => $valueTitle):?>
                                <option
                                    value="<?=$valueId?>"
                                    <?if ($valueId == $arResult['DATA'][$fieldCode]):?> selected<?endif;?>
                                ><?=$valueTitle?></option>
                                <?endforeach;?>
                            </select>
                        <?else:?>
                            <input
                                type="text"
                                id="field_<?=$fieldCode?>"
                                class="control"
                                name="<?=$fieldCode?>"
                                <?if ($arField['REQUIRED']):?> required<?endif;?>
                                value="<?=$arResult['DATA'][$fieldCode]?>"
                            />
                        <?endif;?>
                        <?if ($bError):?>
                        <div class="error">
                            <?=GetMessage('FIELD_ERROR_' . $fieldCode)?>
                        </div>
                        <?endif;?>
                    </div>
                </div>
            </div>
        <?endforeach;?>
            <?if ($greEnabled):?>
                <div class="group">
                    <div class="caption"></div>
                    <div class="field">
                        <div class="g-recaptcha" data-sitekey="<?=$greKey?>"></div>
                        <?if (in_array('RECAPTCHA', $arResult['ERRORS'])):?>
                            <div class="error"><?=GetMessage('FIELD_ERROR_RECAPTCHA')?></div>
                        <?endif;?>
                    </div>
                </div>
            <?endif;?>
        </div>
        <div class="form__agreement">
            <div class="checkbox">
                <input
		                type="checkbox"
		                class="checkbox"
		                id="feedback_agreement"
		                name="<?=$arParams['CHECK_AGREEMENT_FIELD']?>"
		                value="Y"
		                <?if ($arResult['DATA'][$arParams['CHECK_AGREEMENT_FIELD']] == 'Y'):?>checked<?endif;?>
                />
                <label for="feedback_agreement">
                    <?=GetMessage('AGREEMENT_TEXT')?>
                </label>
	            <?if (in_array($arParams['CHECK_AGREEMENT_FIELD'], $arResult['ERRORS'])):?>
	            <div class="error">
		            <?=GetMessage('FIELD_ERROR_' . $arParams['CHECK_AGREEMENT_FIELD'])?>
	            </div>
	            <?endif;?>
            </div>
        </div>
        <div class="form__btn">
            <button type="submit" class="button button_inverse"><?=GetMessage('SUBMIT')?></button>
        </div>
    </form>
</div>

<script>
	$(function() {
        <?if ($greEnabled):?>
		var loadRecaptcha = function() {
			if (grecaptcha !== void(0) && grecaptcha.render !== void(0)) {
				var sel = jQuery('.g-recaptcha');

				if (sel.length === 0) {
					return;
				}

				if (sel.data('widget-id'))
					grecaptcha.reset(sel.data('widget-id'));

				sel.data('widget-id', grecaptcha.render(sel[0], {
					'sitekey': sel.data('sitekey')
				}));
			}
			else {
				setTimeout(loadRecaptcha, 100);
			}
		};

		loadRecaptcha();
        <?endif;?>

        <?if ($arParams['MW_WIDGET_ID']):?>
		$.mw.get('<?=$arParams['MW_WIDGET_ID']?>', function(mw)
		{
			var form = $(this).find('form');

			form.submit(function(e)
			{
				e.preventDefault();
				mw.load(form.serializeArray());
			});
		});
        <?endif;?>
	});
</script>