<?php

class AdminModuleManagerSettingsController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
        $this->display = 'edit';
        $this->submit_action = 'submitAddconfigurationAndStay';
        $this->fields_form = 
        [
            'input' => 
            [
                [
                    'type' => 'switch',
                    'label' => 'Translation links',
                    'name' => 'MM_TRANS_LINKS',
                    'is_bool' => true,
                        'values' =>
                        [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->module->getTranslator()->trans('Yes', [], 'Admin.Global')
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->module->getTranslator()->trans('No', [], 'Admin.Global')
                            ]
                        ],
                ],
                [
                    'type' => 'color',
                    'label' => 'Background Color',
                    'name' => 'MM_BACKGROUND_COLOR',
                ],
                [
                    'type' => 'color',
                    'label' => 'Text Color',
                    'name' => 'MM_LIST_COLOR',
                ],
                [
                    'type' => 'switch',
                    'label' => 'Bold Text',
                    'name' => 'MM_BOLD_TEXT',
                    'is_bool' => true,
                    'values' =>
                        [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->module->getTranslator()->trans('Yes', [], 'Admin.Global')
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->module->getTranslator()->trans('No', [], 'Admin.Global')
                            ]
                        ],
                ],
                [
                    'type' => 'text',
                    'label' => 'List Font Size',
                    'name' => 'MM_LIST_FONT_SIZE',
                    'suffix' => 'px',
                    'class' => 'col-lg-1 col-md-2 col-sm-2 col-xs-2'
                ],

            ],
            'submit' => [
                'title' => $this->l('Save'),
                'name'=>'submitSettings',
            ]
        ];

        $this->fields_value = 
        [
            'MM_TRANS_LINKS' => Configuration::get('MM_TRANS_LINKS'),
            'MM_BACKGROUND_COLOR' => Configuration::get('MM_BACKGROUND_COLOR'),
            'MM_LIST_COLOR' => Configuration::get('MM_LIST_COLOR'),
            'MM_BOLD_TEXT' => Configuration::get('MM_BOLD_TEXT'),
            'MM_LIST_FONT_SIZE' => Configuration::get('MM_LIST_FONT_SIZE'),
        ];
    }

    public function processSave()
    {
        $res = true;
        foreach(Tools::getAllValues() as $key => $value)
        {
            if($key == 'MM_LIST_FONT_SIZE')
            {
                if($this->validateFontField($value))
                {
                    $res &= Configuration::updateValue($key, $value);
                    if($res)
                        $this->fields_value[$key] = $value;
                }
                else
                {
                    $res = false;
                }
            }
            elseif(strpos($key, 'MM_') == 0)
            {
                $res &= Configuration::updateValue($key, $value);
                if($res) 
                    $this->fields_value[$key] = $value;
            }
        }
        if($res)
            $this->confirmations[] = $this->trans('Update Sucessful', array(), 'Admin.Notifications.Error');
        else
            $this->errors[] = $this->trans('Updating Settings failed.', array(), 'Admin.Notifications.Error');
    }

    public function validateFontField($value)
    {
        if(!Validate::isInt($value))
        {
            $this->errors[] = $this->trans('Font size must be an integer.', array(), 'Admin.Notifications.Error');
            return false;
        }
        elseif($value < 6 || $value > 40)
        {
            $this->errors[] = $this->trans('Font size must be between 6 and 40.', array(), 'Admin.Notifications.Error');
            return false;
        }
        return true;
    }
}