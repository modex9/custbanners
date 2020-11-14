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

        $cols = Db::getInstance()->ExecuteS('describe ' . _DB_PREFIX_ . 'module');
        $positions_col_exists = false;
        foreach ($cols as $col)
        {
            if($col['Field'] == 'position')
            {
                $positions_col_exists = true;
                break;
            }
        }
        $res = true;
        if(!$positions_col_exists)
        {
            $sql = "ALTER TABLE `" ._DB_PREFIX_ ."module` ADD `position` int(10) NULL;
                    ALTER TABLE `" ._DB_PREFIX_ ."module`
                    ADD UNIQUE KEY `position` (`position`)";
            $res &= Db::getInstance()->execute($sql);
            $res &= $this->initModulesPositions();
        }
        $res &= $this->initModulesPositions();
        return $res && parent::install()
            && $this->registerHook('actionModuleInstallAfter')
            && $this->registerHook('actionModuleUninstallAfter')
            && $this->installAdminTabs();
    }

    public function uninstall() {
        return parent::uninstall() && $this->uninstallAdminTabs();
    }

    protected function getAdminTabs()
    {
        $tabs = [
            ['name'=>$this->l('Custom Module Manager'), 'class_name' => 'AdminModuleManagerMain', 'id_parent' => 'AdminParentModulesSf', 'active' => true],
            ['name'=>$this->l('Module List'), 'class_name' => 'AdminModuleList', 'id_parent' => 'AdminModuleManagerMain', 'active' => true],
            ['name'=>$this->l('Manager Settings'), 'class_name' => 'AdminModuleManagerSettings', 'id_parent' => 'AdminModuleManagerMain', 'active' => true],
        ];
        return $tabs;
    }

    protected function installAdminTabs()
    {
        $available_lang = Language::getLanguages();

        foreach ($this->getAdminTabs() as $tab)
        {
            $admin_tab = new Tab();
            $admin_tab->module = $this->name;
            $admin_tab->class_name = $tab['class_name'];
            $admin_tab->id_parent = Tab::getIdFromClassName($tab['id_parent']);
            $admin_tab->active = $tab['active'];

            foreach ($available_lang as $lang)
            {
                $admin_tab->name[$lang['id_lang']] = $tab['name'];
            }

            if (!$admin_tab->save())
            {
                $this->_errors[] = $this->l('Unable to install admin tab: '. $tab['class_name']);
                return false;
            }
        }
        return true;
    }

    protected function uninstallAdminTabs()
    {
        foreach ($this->getAdminTabs() as $tab)
        {
            $id_tab = Tab::getIdFromClassName($tab['class_name']);
            if ($id_tab)
            {
                $admin_tab = new Tab($id_tab);

                if (!Validate::isLoadedObject($admin_tab)) {
                    if (!$admin_tab->delete()) {
                        $this->_errors[] = $this->l('Unable to delete admin tab: ' . $tab['class_name']);
                        return false;
                    }
                }
            }
        }
        return true;
    }

    public function getContent() {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminModuleList'));
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
            'module_languages' => Language::getLanguages(false),
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
}