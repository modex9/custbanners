<?php

class AdminModuleListController extends ModuleAdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
    }

    public function renderList() {
        $fields_list = array(
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
            ],
            'version' => [
                'title' => $this->trans('Module Version', [], 'Admin.Global'),
                'type' => 'text',
            ],
            'author' => [
                'title' => $this->trans('Module Author', [], 'Admin.Global'),
                'type' => 'text',
            ],
            'active' => [
                'title' => $this->trans('Active', [], 'Admin.Global'),
                'type' => 'bool',
                'active' => 'status',
            ]
        );

        $modules_array = $this->module->getModules();
        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->identifier = 'id_module';
        $helper->table = 'module';
        $helper->no_link = true;
        $helper->show_toolbar = true;
        $helper->module = $this->module;
        $helper->colorOnBackground = 1;
        $helper->actions = ['configure'];
        if(Configuration::get('MM_TRANS_LINKS'))
            $helper->actions[] = 'translate';
        $helper->orderBy = 'id_module';
        $helper->listTotal = count($modules_array);
        $helper->title = $this->trans('Module list', array(), 'Modules.Mainmenu.Admin');
        $className = get_class($this);
        $controllerName = substr($className, 0, strlen($className) - 10);
        $helper->token = Tools::getAdminTokenLite($controllerName);
        $helper->currentIndex = Context::getContext()->link->getAdminLink($controllerName, false);
        $helper->tpl_vars = [
            'bg_color' => Configuration::get('MM_BACKGROUND_COLOR'),
            'list_text_color' => Configuration::get('MM_LIST_COLOR'),
            'list_bold' => Configuration::get('MM_BOLD_TEXT'),
            'list_font_size' => Configuration::get('MM_LIST_FONT_SIZE'),
        ];

        return $helper->generateList($modules_array, $fields_list);
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
    }
}