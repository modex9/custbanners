<?php

class somemodule extends Module {
    
    public function __construct() {
        $this->version = '1.0';
        $this->name = $this->l('somemodule');
        $this->author = 'Modestas Slivinskas';
        
        parent::__construct();
        $this->displayName =  $this->l('Some module');
        $this->description = $this->l('Experimental module.');
        $this->bootstrap = true;
    }

    public function install() {
        return parent::install() && $this->registerHook('backofficeHeader');
    }

    public function getContent() {

        if($this->postProcess()) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true, [], [
                'configure' => $this->name,
            ]));
        }
        $modules = Db::getInstance()->executeS(
            (new DbQuery())
            ->select("id_module")
            ->from("module")
        );
        $modules_array = [];
        foreach($modules as $module) {
            $module_obj = Module::getInstanceById($module['id_module']);
            $modules_array[] = [
                'id_module' => $module_obj->id,
                'displayName' => $module_obj->displayName,
                'name' => $module_obj->name,
                'version' => $module_obj->version,
                'author' => $module_obj->author,
                'active' => $module_obj->active,
            ];

        }

        $fields_list = array(
            'id_module' => [
                'title' => $this->trans('Module ID', [], 'Admin.Global'),
                'type' => 'text',
                'search' => true
            ],
            'name' => [
                'title' => $this->trans('Name', [], 'Admin.Global'),
                'type' => 'text',
                'search' => true
            ],
            'displayName' => [
                'title' => $this->trans('Display Name', [], 'Admin.Global'),
                'type' => 'text',
                'search' => true
            ],
            'version' => [
                'title' => $this->trans('Module Version', [], 'Admin.Global'),
                'type' => 'text',
                'search' => true
            ],
            'author' => [
                'title' => $this->trans('Module Author', [], 'Admin.Global'),
                'type' => 'text',
                'search' => true
            ],
            'active' => [
                'title' => $this->trans('Active', [], 'Admin.Global'),
                'type' => 'bool',
                'active' => 'status',
                'search' => true
            ]
        );

        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->identifier = $this->identifier;
        $helper->table = $this->table;
        $helper->no_link = true;
        $helper->show_toolbar = true;
        $helper->module = $this;
        $helper->actions = ['configure', 'translate'];
        $helper->orderBy = 'id_module';
        $helper->title = $this->trans('Module list', array(), 'Modules.Mainmenu.Admin');
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

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
                return $module->disable();
            }
            else {
                return $module->enable();
            }
        }
    }

    public function displayConfigureLink($token, $id, $name = null)
    {
        $module = Module::getInstanceByName($name);
        if(!method_exists($module, 'getContent') || !$module->active)
            return;
        $href = $this->context->link->getAdminLink('AdminModules', true, [], [
            'configure' => $name,
        ]);
        $this->context->smarty->assign(array(
            'href' => $href,
            'action' => Context::getContext()->getTranslator()->trans('Configure', array(), 'Admin.Actions'),
            'id' => $id,
        ));
        return $this->fetch('module:somemodule/views/templates/admin/list_action_configure.tpl');
    }

    public function displayTranslateLink($token, $id, $name = null)
    {
        $href = $this->context->link->getAdminLink('AdminModules', true, [], [
            'configure' => $name,
        ]);
        $this->context->smarty->assign(array(
            'href' => $href,
            'action' => Context::getContext()->getTranslator()->trans('Translate', array(), 'Admin.Actions'),
            'id' => $id,
            'translateLinks' => $this->getModuleTranslationLinks($name),
        ));
        return $this->fetch('module:somemodule/views/templates/admin/list_action_translate.tpl');
    }

    public function getModuleTranslationLinks($module_name) {
        $module = Module::getInstanceByName($module_name);
        $languages = Language::getLanguages(false);
        $translateLinks = array();
        $isNewTranslateSystem = $module->isUsingNewTranslationSystem();
        $link = Context::getContext()->link;
        foreach ($languages as $lang) {
            if ($isNewTranslateSystem) {
                $translateLinks[$lang['iso_code']] = $link->getAdminLink('AdminTranslationSf', true, array(
                    'lang' => $lang['iso_code'],
                    'type' => 'modules',
                    'selected' => $module->name,
                    'locale' => $lang['locale'],
                ));
            } else {
                $translateLinks[$lang['iso_code']] = $link->getAdminLink('AdminTranslations', true, array(), array(
                    'type' => 'modules',
                    'module' => $module->name,
                    'lang' => $lang['iso_code'],
                ));
            }
        }
        return $translateLinks;
    }

    public function hookDisplayBackofficeHeader($params) {
        if (Tools::getIsset('configure') && Tools::getValue('configure') == $this->name) {
            $this->context->controller->addCSS($this->_path . 'views/css/admin.css');
        }
    }
}