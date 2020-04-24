<?php
namespace T2G\Common\FormFields;

use T2G\Common\Repository\GiftCodeRepository;
use TCG\Voyager\FormFields\AbstractHandler;

/**
 * Class GiftCodeTypeFormField
 *
 * @package \\${NAMESPACE}
 */
class GiftCodeTypeFormField extends AbstractHandler
{
    protected $codename = 'giftcode_type_dropdown';

    public function createContent($row, $dataType, $dataTypeContent, $options)
    {
        $options = new \StdClass();
        $options->options = GiftCodeRepository::getTypes();

        return view('voyager::formfields.select_dropdown', [
            'row'             => $row,
            'options'         => $options,
            'dataType'        => $dataType,
            'dataTypeContent' => $dataTypeContent,
        ]);
    }
}
