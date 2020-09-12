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
        return parent::install();
    }

    public function uninstall() {
        return parent::uninstall();
    }

    public function getContent() {

        $modules = Module::getModulesOnDisk();
        $modules_array = [];
        $tmp_module = [];
        foreach($modules as $module) {
            if($module->id != 0) {
                $tmp_module = (array) $module;
                $tmp_module['id_module'] = $module->id;
                $modules_array[] = (array) $tmp_module;
            }
        }

        $fields_list = array(
            'id_module' => [
                'title' => $this->trans('Module ID', [], 'Admin.Global'),
                'type' => 'text',
                'search' => true
            ],
            'displayName' => [
                'title' => $this->trans('Module Name', [], 'Admin.Global'),
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
        $helper->orderBy = 'id_module';
        $helper->title = $this->trans('Module list', array(), 'Modules.Mainmenu.Admin');
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        return $helper->generateList($modules_array, $fields_list);
    }
    
}