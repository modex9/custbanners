<?php

class AdminModuleListController extends ModuleAdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->_select = "a.name AS displayName, a.name AS author";
        $this->shopLinkType = '';
        $this->table = 'module';
        $this->_orderBy = 'id_module';
        $this->toolbar_btn = false;
        $this->identifier = 'id_module';
        $this->list_no_link = true;
        $this->colorOnBackground = true;
        $this->bootstrap = true;
        $this->actions = ['configure'];
        if(Configuration::get('MM_TRANS_LINKS'))
            $this->actions[] = 'translate';
        $this->fields_list = [
            'id_module' => [
                'title' => $this->trans('Module ID', [], 'Admin.Global'),
                'type' => 'text',
            ],
            'name' => [
                'title' => $this->trans('Name', [], 'Admin.Global'),
                'type' => 'text',
            ],
            'displayName' => [
                'title' => $this->trans('Display Name', [], 'Admin.Global'),
                'type' => 'text',
                'callback' => 'getModuleDisplayName',
                'search' => false,
            ],
            'version' => [
                'title' => $this->trans('Module Version', [], 'Admin.Global'),
                'type' => 'text',
            ],
            'author' => [
                'title' => $this->trans('Module Author', [], 'Admin.Global'),
                'type' => 'text',
                'callback' => 'getModuleAuthor',
                'search' => false,
            ],
            'active' => [
                'title' => $this->trans('Active', [], 'Admin.Global'),
                'type' => 'bool',
                'active' => 'status',
            ]
        ];
        $this->tpl_list_vars = [
            'bg_color' => Configuration::get('MM_BACKGROUND_COLOR'),
            'list_text_color' => Configuration::get('MM_LIST_COLOR'),
            'list_bold' => Configuration::get('MM_BOLD_TEXT'),
            'list_font_size' => Configuration::get('MM_LIST_FONT_SIZE'),
        ];
    }

    public function postProcess() {
        if (Tools::isSubmit('statusmodule') && Tools::getValue('id_module'))
        {
            // Change status of module
            $id_module = Tools::getValue('id_module');
            if (!$id_module || !Validate::isUnsignedId($id_module))
            {
                $this->_errors[] = $this->l('Invalid module ID.');
                return false;
            }

            $module = Module::getInstanceById($id_module);
            if (!Validate::isLoadedObject($module))
            {
                $this->_errors[] = $this->l('Can\'t find module with this ID.');
                return false;
            }

            if($module->active)
            {
                $module->disable();
            }
            else {
                $module->enable();
            }
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModuleList'));
        }
        else
            parent::postProcess();
    }

    public function getModuleDisplayName($module_name)
    {
        $module = Module::getInstanceByName($module_name);
        return $module->displayName;
    }

    public function getModuleAuthor($module_name)
    {
        $module = Module::getInstanceByName($module_name);
        return $module->author;
    }

    public function initToolbar()
    {
        $this->toolbar_btn = [];
    }

    public function setHelperDisplay(Helper $helper)
    {
        parent::setHelperDisplay($helper);
        $this->helper->bulk_actions = false;
        $this->helper->module = $this->module;
    }
}