<?php

class somemodule extends Module {
    
    public function __construct() {
        $this->version = '1.0';
        $this->name = $this->l('somemodule');
        $this->author = 'Modestas Slivinskas';
        
        parent::__construct();
        $this->displayName =  $this->l('Some module');
        $this->description = $this->l('Experimental module.');
        
    }

    public function install() {
        return parent::install();
    }

    public function uninstall() {
        return parent::uninstall();
    }

    public function getContent() {
        
    }
    
}