<?php
/**
* 2007-2020 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    Keng
*  @copyright Keng
*  @license  #
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use PrestaShopBundle\Form\Admin\Type\FormattedTextareaType;
use PrestaShopBundle\Form\Admin\Type\TranslatableType;
use PrestaShopBundle\Form\Admin\Type\TranslateType;
use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;

class Customfileds extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'customfileds';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Keng';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Custom Filed Category');
        $this->description = $this->l('Custom Filed Category');

        $this->confirmUninstall = $this->l('');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('ctf_enable', false);
        include(dirname(__FILE__).'/sql/install.php');
        if (_PS_VERSION_ >= '1.7.6.0') {
            return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayFooter') &&
            $this->registerHook('actionRootCategoryFormBuilderModifier') &&
            $this->registerHook('actionAfterCreateRootCategoryFormHandler') &&
            $this->registerHook('actionAfterUpdateRootCategoryFormHandler') &&
            $this->registerHook('actionCategoryFormBuilderModifier') &&
            $this->registerHook('actionAfterCreateCategoryFormHandler') &&
            $this->registerHook('actionAfterUpdateCategoryFormHandler');
        } else {
            return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayFooter');
        }
    }
    public function uninstall()
    {
        Configuration::deleteByName('ctf_enable');
        include(dirname(__FILE__).'/sql/uninstall.php');
        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */

    public function getContent()
    {
        $status = '';
        if (((bool)Tools::isSubmit('submitCustomfiledsModule')) == true) {
            if (Tools::getIsset('id_customfileds')) {
                $id_customfileds =  Tools::getValue('id_customfileds');
                $name = Tools::getValue('ctf_name');
                $label = Tools::getValue('ctf_label');
                $active = Tools::getValue('ctf_enable');
                $hook = Tools::getValue('ctf_hook');
               
                if (Db::getInstance()->update(
                    'customfileds',
                    array(
                        'name' => $name,
                        'label' => $label,
                        'hook' => $hook,
                        'active' => $active
                    ),
                    'id_customfileds = ' . (int) $id_customfileds
                )) {
                    Tools::redirectAdmin(
                        $this->context->link->getAdminLink('AdminModules', true).
                        '&conf=4&configure='.$this->name.
                        '&tab_module='.$this->tab.
                        '&module_name='.$this->name
                    );
                } else {
                    return $this->displayError("Error ! Can't update. ");
                }
            } else {
                $status = $this->postProcess();
            }
        }
        if (Tools::getIsset('deletemerged')) {
            $id_customfileds =  Tools::getValue('id_customfileds');
            if (Db::getInstance()->delete(
                'customfileds',
                '`id_customfileds` = '.(int)$id_customfileds
            )) {
                Tools::redirectAdmin(
                    $this->context->link->getAdminLink('AdminModules', true).
                    '&conf=4&configure='.$this->name.
                    '&tab_module='.$this->tab.
                    '&module_name='.$this->name
                );
            } else {
                 return $this->displayError("Error ! Can't delete. ");
            }
        }
       
        if (Tools::getIsset('activemerged')) {
            $id_customfileds = Tools::getValue('id_customfileds');
            $active = $this->toggleActive($id_customfileds);
            if (Db::getInstance()->update(
                'customfileds',
                array(
                    'active' => $active
                ),
                'id_customfileds = ' . (int) $id_customfileds
            )) {
                Tools::redirectAdmin(
                    $this->context->link->getAdminLink('AdminModules', true).
                    '&conf=4&configure='.$this->name.
                    '&tab_module='.$this->tab.
                    '&module_name='.$this->name
                );
            } else {
                return $this->displayError("Can't add new field !");
            }
        }
      
        if (Shop::getContext() == Shop::CONTEXT_GROUP || Shop::getContext() == Shop::CONTEXT_ALL) {
            return $this->display(__FILE__, 'views/templates/admin/warning.tpl');
        } else {
            return $status.$doc.$this->renderForm();
        }
    }

    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitCustomfiledsModule';
        $url_id = '';
        if (Tools::getIsset('updatemerged')) {
            $id_customfileds = Tools::getValue('id_customfileds');
            $url_id = '&id_customfileds='.$id_customfileds;
        }
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.$url_id;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );
        $show_list = '';
        if (!Tools::getIsset('updatemerged')) {
            $show_list = $this->renderList();
        }
        return $helper->generateForm(array($this->getConfigForm())).$show_list;
    }

    /**
     * Create the structure of your form.
     */

    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Add new custom field'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Enabled'),
                        'name' => 'ctf_enable',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'ctf_name',
                        'label' => $this->l('Filed Name'),
                        'desc' => $this->l('Please don\'t add space in the field name'),
                        'required' => true,
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'ctf_label',
                        'label' => $this->l('Label'),
                        'required' => true,
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'ctf_hook',
                        'label' => $this->l('hook'),
                        'desc' => $this->l('Please enter "Class/ID" that want to show them'),
                        'required' => true,
                    )                  ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }
    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        $ctf_enable = false;
        $ctf_name = '';
        $ctf_label = '';
        $ctf_hook = '';
        $ctf_id = 0;
        if (Tools::getIsset('updatemerged')) {
            $id_customfileds = Tools::getValue('id_customfileds');
            $data = $this->getCustomField($id_customfileds);
            $ctf_id = $data[0]['id_customfileds'];
            $ctf_name = $data[0]['name'];
            $ctf_label = $data[0]['label'];
            $ctf_enable = $data[0]['active'];
            $ctf_hook = $data[0]['hook'];
        }
        return array(
            'ctf_enable' => $ctf_enable,
            'ctf_name' => $ctf_name,
            'ctf_label' => $ctf_label,
            'ctf_id' => $ctf_id,
            'ctf_hook' => $ctf_hook,
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {

        $name = Tools::getValue('ctf_name');
        $active = Tools::getValue('ctf_enable');
        $label = Tools::getValue('ctf_label');
        $hook = Tools::getValue('ctf_hook');
        $context = Context::getContext();
        $id_shop = $context->shop->id;
        
        if ($name == '' || $label == '') {
             return $this->displayError('Field name must not blank');
        } else {
            $name = preg_replace('/[^A-Za-z0-9\-]/', '_', $name);
            $position = $this->getHigherPosition()+1;
            $sql = 'INSERT INTO `'._DB_PREFIX_.'customfileds`
            (`name`, `active`,`id_shop`, `hook`, `label`, `position`)
            VALUES ("'.$name.'",'.(int)$active.', "'.$id_shop.'", "'.$hook.'", "'.$label.'",'.$position.')';
            if (Db::getInstance()->execute($sql)) {
                Tools::redirectAdmin(
                    $this->context->link->getAdminLink('AdminModules', true).
                    '&conf=4&configure='.$this->name.
                    '&tab_module='.$this->tab.
                    '&module_name='.$this->name
                );
            } else {
                return $this->displayError("Can't add new field !");
            }
        }
    }

    public static function getHigherPosition()
    {
        $sql = 'SELECT MAX(`position`) FROM `' . _DB_PREFIX_ . 'customfileds`';
        $position = Db::getInstance()->getValue($sql);
        return (is_numeric($position)) ? $position : 0;
    }
    
    public function hookDisplayFooter()
    {
        if ($this->context->controller->php_self == 'category') {
            $getCustomData = Customfileds::getCustomData(Tools::getValue('id_category'));
            if (isset($getCustomData[0]['data'])) {
                $splitData = Customfileds::splitData($getCustomData[0]['data']);
                $id_lang = Context::getContext()->language->id;
                foreach ($splitData as &$value) {        
                    $value['data'] = $value['data'][$id_lang];
                    $ctf_data = $this->getCustomField($value['id']);
                    echo '<section class="customfieldcategory" data-hook="'.$ctf_data[0]['hook'].'" style="display:none;">';
                        echo '<div id="customfield_'.$value['id'].'">';
                        echo $value['data'];
                        echo '</div>';
                    echo '</section>';
                }
            }
        }
    }
    
    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    public function renderList()
    {
        if (_PS_VERSION_ >= '1.7') {
            $fields_list = array(
                'id_customfileds' => array(
                    'title' => $this->trans('ID', array(), 'Admin.Global'),
                    'search' => false,
                ),
                'name' => array(
                    'title' => $this->trans('Field Name', array(), 'Admin.Global'),
                    'search' => false,
                ),
                'label' => array(
                    'title' => $this->trans('Label', array(), 'Admin.Global'),
                    'search' => false,
                ),
                'hook' => array(
                    'title' => $this->trans('Hook (CSS/ID)', array(), 'Admin.Global'),
                    'search' => false,
                ),
                'active' => array(
                    'title' => $this->trans('Enabled', array(), 'Admin.Global'),
                    'search' => false,
                    'type' => 'bool',
                    'active' => 'active',
                )
            );
        } else {
            $fields_list = array(
                'id_customfileds' => array(
                    'title' => $this->l('ID', array(), 'Admin.Global'),
                    'search' => false,
                ),
                'name' => array(
                    'title' => $this->l('Field Name'),
                    'search' => false,
                ),
                'label' => array(
                    'title' => $this->l('Label'),
                    'search' => false,
                ),
                'hook' => array(
                    'title' => $this->l('Hook (CSS/ID)'),
                    'search' => false,
                ),
                'active' => array(
                    'title' => $this->l('Enabled'),
                    'search' => false,
                    'type' => 'bool',
                    'active' => 'active',
                )
            );
        }

        $helper_list = new HelperList();
        $helper_list->module = $this;
        if (_PS_VERSION_ >= '1.7') {
            $helper_list->title = $this->trans('List', array(), 'Modules.customfileds.Admin');
        } else {
            $helper_list->title = $this->l('List');
        }
        $helper_list->shopLinkType = '';
        $helper_list->no_link = true;
        $helper_list->show_toolbar = true;
        $helper_list->simple_header = false;
        $helper_list->identifier = 'id_customfileds';
        $helper_list->table = 'merged';
        $helper_list->currentIndex =$this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name;
        $helper_list->token = Tools::getAdminTokenLite('AdminModules');
        $helper_list->actions = array('edit','delete');

        // This is needed for displayEnableLink to avoid code duplication
        $this->_helperlist = $helper_list;

        /* Retrieve list data */
        $getCustomField = $this->getCustomField();
        //$this->pre($getCustomField);
        $helper_list->listTotal = count($getCustomField);
        return $helper_list->generateList($getCustomField, $fields_list);
    }

    public function toggleActive($id_customfileds)
    {
        $dbquery = new DbQuery();
        $dbquery->select('active');
        $dbquery->from('customfileds');
        $dbquery->where('id_customfileds = '.$id_customfileds);
        $return = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($dbquery->build());
        return $return == 1 ? 0 : 1;
    }

    public function getCustomField($id_customfileds = false)
    {
        $context = Context::getContext();
        $id_shop = $context->shop->id;
        $dbquery = new DbQuery();
        $dbquery->select('*');
        $dbquery->from('customfileds', 'c');
        if ($id_customfileds) {
            $dbquery->where('id_customfileds = '.$id_customfileds.' AND id_shop = '.$id_shop);
        } else {
            $dbquery->where('id_shop = '.$id_shop);
        }
        $customField = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($dbquery->build());
        return $customField;
    }

    public static function getCustomFieldActive($active = 1)
    {
        $context = Context::getContext();
        $id_shop = $context->shop->id;
        $dbquery = new DbQuery();
        $dbquery->select('*');
        $dbquery->from('customfileds', 'c');
        $dbquery->where('active = '.$active.' AND id_shop = '.$id_shop);
        $customField = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($dbquery->build());
        return $customField;
    }

    public function hookActionCategoryFormBuilderModifier($params)
    {
        if (_PS_VERSION_ >= '1.7.6.0') {
            if (Shop::getContext() != Shop::CONTEXT_GROUP && Shop::getContext() != Shop::CONTEXT_ALL) {
                $customField = Customfileds::getCustomFieldActive();
                $formBuilder = array();
                $formBuilder = $params['form_builder'];
                $getCustomData = Customfileds::getCustomData($params['id']);
                $locales = $this->getLocales();
                $data = array();
                foreach ($locales as $value) {
                    $data[$value['id_lang']] = '';
                }
                if (count($customField) > 0) {
                    if (count($getCustomData) > 0) {
                        $splitData = Customfileds::splitData($getCustomData[0]['data']);
                    }
                    foreach ($customField as $value) {
                        if (isset($splitData[$value['id_customfileds']])) {
                            $data = $splitData[$value['id_customfileds']]['data'];
                        } else {
                            $data = array_fill_keys(Language::getIDs(false), '');
                        }
                        $label = $value['label'];
                        $formBuilder = $params['form_builder'];
                        $formBuilder->add($value['name'], TranslateType::class, [
                            'type' => FormattedTextareaType::class,
                            'label' => $this->getTranslator()->trans($label, [], 'Modules.customfileds'),
                            'locales' => $locales,
                            'hideTabs' => false,
                            'required' => false,
                            'data' => $data,
                        ]);
                        $formBuilder->setData($params['data'], $params);
                    }
                }
            }
        }
    }

    public function hookActionRootCategoryFormBuilderModifier($params)
    {
        $this->hookActionCategoryFormBuilderModifier($params);
    }

    public function getLocales()
    {
        $context = Context::getContext();
        $id_shop = $context->shop->id;
        $dbquery = new DbQuery();
        $dbquery->select('*');
        $dbquery->from('lang');
        $return = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($dbquery->build());
        
        $shops = $this->getAllshop();
        foreach ($return as &$value) {
            $value['id_shop'] = $id_shop;
            $value['shops'] = $shops;
        }
        return $return;
    }

    public function getAllshop()
    {
        $dbquery = new DbQuery();
        $dbquery->select('id_shop');
        $dbquery->from('shop');
        $return = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($dbquery->build());
        $array = array();
        foreach ($return as $value) {
            $array[$value['id_shop']] = $value['id_shop'];
        }
        return $array;
    }

    public static function splitData($data)
    {
        return unserialize($data);
    }

    public function pre($data)
    {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }

    public function hookActionAfterCreateCategoryFormHandler($params)
    {
        Customfileds::updateCategoryFields($params['form_data'], $params);
    }

    public function hookActionAfterUpdateCategoryFormHandler($params)
    {
        Customfileds::updateCategoryFields($params['form_data'], $params);
    }

    public function hookActionAfterCreateRootCategoryFormHandler($params)
    {
        Customfileds::updateCategoryFields($params['form_data'], $params);
    }

    public function hookActionAfterUpdateRootCategoryFormHandler($params)
    {
        Customfileds::updateCategoryFields($params['form_data'], $params);
    }

    public static function getCustomData($id_category)
    {
        $context = Context::getContext();
        $shop = $context->shop;
        $dbquery = new DbQuery();
        $dbquery->select('*');
        $dbquery->from('customfileds_data');
        $dbquery->where('id_category = '.$id_category.' AND id_shop = '.$shop->id);
        $return = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($dbquery->build());
        return $return;
    }

    public static function updateCategoryFields(array $data, $params)
    {
        $customField = Customfileds::getCustomFieldActive();
        $context = Context::getContext();
        $shop = $context->shop;
        $html = array();
        if (count($data) > 0) {
            foreach ($customField as $value) {
                $html[$value['id_customfileds']] = array();
                $html[$value['id_customfileds']]['id'] = $value['id_customfileds'];
                $html[$value['id_customfileds']]['data'] =$data[$value['name']];
            }
            $data = Customfileds::replaceContent(serialize($html));
            $getCustomData = Customfileds::getCustomData($params['id']);
            if (count($getCustomData) > 0) {
                Db::getInstance()->update(
                    'customfileds_data',
                    array(
                        'data' => $data
                    ),
                    '`id_category` ='.(int) $params["id"].' AND `id_shop` = '.(int) $shop->id
                );
            } else {
                $sql = 'INSERT INTO `'._DB_PREFIX_.'customfileds_data`(`id_category`, `id_shop`, `data`) 
                VALUES ('.(int) $params['id'].',"'.(int) $shop->id.'","'.$data.'")';
                Db::getInstance()->execute($sql);
            }
        }
    }

    public function replaceContent($content)
    {
        $content =  str_replace('"', '\"', $content);
        $content =  str_replace("'", "\'", $content);
        return $content;
    }

    public static function getLang()
    {
        $dbquery = new DbQuery();
        $dbquery->select('*');
        $dbquery->from('lang');
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($dbquery->build());
    }
}
