<?php

class AdminModuleListController extends ModuleAdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->_select = "a.name AS displayName, a.name AS author";
        $this->shopLinkType = '';
        $this->table = 'module';
        $this->_orderBy = 'position';
        $this->toolbar_btn = false;
        $this->identifier = 'id_module';
        $this->position_identifier = 'id_module';
        $this->list_no_link = true;
        $this->colorOnBackground = true;
        $this->bootstrap = true;
        $this->actions = ['configure'];
        if(Configuration::get('MM_TRANS_LINKS'))
            $this->actions[] = 'translate';
        $this->fields_list = [
            'position' => [
                'title' => $this->trans('Position', array(), 'Admin.Global'),
                'position' => 'position',
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'id_module' => [
                'title' => $this->trans('Module ID', [], 'Admin.Global'),
                'type' => 'text',
                'class' => 'fixed-width-xs',
                'align' => 'center',
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
                'class' => 'fixed-width-xs',
                'align' => 'center',
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
                'align' => 'center',
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
                $this->errors[] = $this->trans('Can\'t find module with this ID.', [], 'Shop.Theme.Global');
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

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->addJS(_PS_MODULE_DIR_ . $this->module->name . '/views/js/module_list.js');
        $this->addCSS(_PS_MODULE_DIR_ . $this->module->name . '/views/css/admin.css');
    }

    public function ajaxProcessUpdatePositions()
    {
        $new_rows = Tools::getValue($this->table);

        $id_module = Tools::getValue('id', 0);

        // dnd uses growl to display messages, but it only displays the success messages, so errors are useless for now...
        if($id_module < 1)
        {
            $error =  $this->trans('Invalid module ID: %s', [$id_module], 'Shop.Theme.Global');
            echo '{"hasError" : true, "errors" : "'.$error.'"}';
        }

        $new_pos = $old_pos = 0;
        $paginatinon = Tools::getValue('selected_pagination');
        $page = Tools::getValue('page');
        foreach ($new_rows as $i => $row)
        {
            $row_elements = explode('_', $row);
            if(count($row_elements) != 4)
                continue;
            if($row_elements[2] == $id_module)
            {
                $old_pos = $row_elements[3];
                $new_pos = $i + ($page - 1) * $paginatinon;
                break;
            }
        }
        //todo: check if way is 0 or 1, error if otherwise
        $way = Tools::getValue('way', -1);

        //if way: 0, get old position and new position. Update all positions, which are greater than new position and less then the old 1, by adding 1.
        //if way: 1, get old position and new position. Update all positions, which are greater than old position and less then the new 1, by subtracting 1.
        if(in_array($way, [0, 1]) && $old_pos >= 0 && $new_pos >= 0 && ($old_pos !== $new_pos))
        {
            Db::getInstance()->update('module', ['position' => [
                'type' => 'sql',
                'value' => '`position`' . ($way ? " - 1" : " + 1")
            ]], "`position` >= " . ($way ? $old_pos : $new_pos) . " AND `position` <= " . ($way ? $new_pos : $old_pos));
            Db::getInstance()->update('module', ['position' => $new_pos], "`id_module` = " . $id_module);
        }
    }
}