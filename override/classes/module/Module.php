<?php

class Module extends ModuleCore
{
    public $position;

    public function __construct($name = null, Context $context = null)
    {
        parent::__construct($name, $context);
        if(isset($this->id) && $position = Db::getInstance()->getValue('SELECT `position` FROM '. _DB_PREFIX_ . 'module WHERE `id_module`=' . $this->id))
        {
            $this->position = $position;
        }
    }

    public function uninstall()
    {
        $result = parent::uninstall();
        if($result)
        {
            Hook::exec('actionModuleUninstallAfter', array('module' => $this));
        }
        return $result;
    }
}