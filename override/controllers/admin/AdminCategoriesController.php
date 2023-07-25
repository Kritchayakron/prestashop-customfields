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
*/

class AdminCategoriesController extends AdminCategoriesControllerCore
{
    public function renderForm()
    {
        if (_PS_VERSION_ >= '1.7' && _PS_VERSION_ < '1.7.6.0') {
            $this->initToolbar();
            $obj = $this->loadObject(true);
            $context = Context::getContext();
            $id_shop = $context->shop->id;
            $selected_categories = array(
                (isset($obj->id_parent)
                    && $obj->isParentCategoryAvailable($id_shop))?
                    (int)$obj->id_parent : (int)Tools::getValue('id_parent', Category::getRootCategory()->id)
                );
            $unidentified = new Group(Configuration::get('PS_UNIDENTIFIED_GROUP'));
            $guest = new Group(Configuration::get('PS_GUEST_GROUP'));
            $default = new Group(Configuration::get('PS_CUSTOMER_GROUP'));
            $unidentified_group_information = sprintf(
                $this->trans(
                    '%s - All people without a valid customer account.',
                    array(),
                    'Admin.Catalog.Feature'
                ),
                '<b>'.$unidentified->name[$this->context->language->id].'</b>'
            );
            $guest_group_information = sprintf(
                $this->trans(
                    '%s - Customer who placed an order with the guest checkout.',
                    array(),
                    'Admin.Catalog.Feature'
                ),
                '<b>'.$guest->name[$this->context->language->id].'</b>'
            );
            $default_group_information = sprintf(
                $this->trans(
                    '%s - All people who have created an account on this site.',
                    array(),
                    'Admin.Catalog.Feature'
                ),
                '<b>'.$default->name[$this->context->language->id].'</b>'
            );
            if (!($obj = $this->loadObject(true))) {
                return;
            }
            $image = _PS_CAT_IMG_DIR_.$obj->id.'.'.$this->imageType;
            $image_url = ImageManager::thumbnail(
                $image,
                $this->table.'_'.(int)$obj->id.'.'.$this->imageType,
                350,
                $this->imageType,
                true,
                true
            );
            $image_size = file_exists($image) ? filesize($image) / 1000 : false;
            $images_types = ImageType::getImagesTypes('categories');
            $format = array();
            $thumb = $thumb_url = '';
            $formatted_category= ImageType::getFormattedName('category');
            $formatted_small = ImageType::getFormattedName('small');
            foreach ($images_types as $k => $image_type) {
                if ($formatted_category == $image_type['name']) {
                    $format['category'] = $image_type;
                } elseif ($formatted_small == $image_type['name']) {
                    $format['small'] = $image_type;
                    $thumb = _PS_CAT_IMG_DIR_.$obj->id.'-'.$image_type['name'].'.'.$this->imageType;
                    if (is_file($thumb)) {
                        $thumb_url = ImageManager::thumbnail(
                            $thumb,
                            $this->table.'_'.(int)$obj->id.'-thumb.'.$this->imageType,
                            (int)$image_type['width'],
                            $this->imageType,
                            true,
                            true
                        );
                    }
                }
            }
            if (!is_file($thumb)) {
                $thumb = $image;
                $thumb_url = ImageManager::thumbnail(
                    $image,
                    $this->table.'_'.(int)$obj->id.'-thumb.'.$this->imageType,
                    125,
                    $this->imageType,
                    true,
                    true
                );
                ImageManager::resize(
                    _PS_TMP_IMG_DIR_.$this->table.'_'.(int)$obj->id.'-thumb.'.$this->imageType,
                    _PS_TMP_IMG_DIR_.$this->table.'_'.(int)$obj->id.'-thumb.'.$this->imageType,
                    (int)$image_type['width'],
                    (int)$image_type['height']
                );
            }
            $thumb_size = file_exists($thumb) ? filesize($thumb) / 1000 : false;
            $menu_thumbnails = [];
            for ($i = 0; $i < 3; $i++) {
                if (file_exists(_PS_CAT_IMG_DIR_.(int)$obj->id.'-'.$i.'_thumb.jpg')) {
                    $menu_thumbnails[$i]['type'] = HelperImageUploader::TYPE_IMAGE;
                    $menu_thumbnails[$i]['image'] = ImageManager::thumbnail(
                        _PS_CAT_IMG_DIR_.(int)$obj->id.'-'.$i.'_thumb.jpg',
                        $this->context->controller->table.'_'.(int)$obj->id.'-'.$i.'_thumb.jpg',
                        100,
                        'jpg',
                        true,
                        true
                    );
                    $menu_thumbnails[$i]['delete_url'] = Context::getContext()->link->getAdminLink('AdminCategories').
                    '&deleteThumb='.$i.'&id_category='.(int)$obj->id;
                }
            }
            $this->fields_form = array(
                'tinymce' => true,
                'legend' => array(
                    'title' => $this->trans('Category', array(), 'Admin.Catalog.Feature'),
                    'icon' => 'icon-tags'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Name', array(), 'Admin.Global'),
                        'name' => 'name',
                        'lang' => true,
                        'required' => true,
                        'class' => 'copy2friendlyUrl',
                        'hint' => $this->trans('Invalid characters:', array(), 'Admin.Notifications.Info').' <>;=#{}',
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->trans('Displayed', array(), 'Admin.Global'),
                        'name' => 'active',
                        'required' => false,
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->trans('Enabled', array(), 'Admin.Global')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->trans('Disabled', array(), 'Admin.Global')
                            )
                        )
                    ),
                    array(
                        'type'  => 'categories',
                        'label' => $this->trans('Parent category', array(), 'Admin.Catalog.Feature'),
                        'name'  => 'id_parent',
                        'tree'  => array(
                            'id'                  => 'categories-tree',
                            'selected_categories' => $selected_categories,
                            'disabled_categories' => (!Tools::isSubmit('add'.$this->table)
                                && !Tools::isSubmit('submitAdd'.$this->table)) ? array($this->_category->id) : null,
                            'root_category'       => $context->shop->getCategory()
                        )
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->trans('Description', array(), 'Admin.Global'),
                        'name' => 'description',
                        'autoload_rte' => true,
                        'lang' => true,
                        'hint' => $this->trans('Invalid characters:', array(), 'Admin.Notifications.Info').' <>;=#{}'
                    ),
                    array(
                        'type' => 'file',
                        'label' => $this->trans('Category Cover Image', array(), 'Admin.Catalog.Feature'),
                        'name' => 'image',
                        'display_image' => true,
                        'image' => $image_url ? $image_url : false,
                        'size' => $image_size,
                        'delete_url' => self::$currentIndex.'&'.$this->identifier.'='.
                        $this->_category->id.'&token='.$this->token.'&deleteImage=1',
                        'hint' => $this->trans(
                            'This is the main image for your category, displayed in the category page.
                            The category description will overlap this image and appear in its top-left corner.',
                            array(),
                            'Admin.Catalog.Help'
                        ),
                        'format' => $format['category']
                    ),
                    array(
                        'type' => 'file',
                        'label' => $this->trans('Category thumbnail', array(), 'Admin.Catalog.Feature'),
                        'name' => 'thumb',
                        'display_image' => true,
                        'image' => $thumb_url ? $thumb_url : false,
                        'size' => $thumb_size,
                        'format' => isset($format['small']) ? $format['small'] : $format['category'],
                        'hint' => $this->trans(
                            'Displays a small image in the parent category\'s page, if the theme allows it.',
                            array(),
                            'Admin.Catalog.Help'
                        ),
                    ),
                    array(
                        'type' => 'file',
                        'label' => $this->trans('Menu thumbnails', array(), 'Admin.Catalog.Feature'),
                        'name' => 'thumbnail',
                        'ajax' => true,
                        'multiple' => true,
                        'max_files' => 3,
                        'files' => $menu_thumbnails,
                        'url' => Context::getContext()->link->getAdminLink('AdminCategories').'
                        &ajax=1&id_category='.$this->id.'&action=uploadThumbnailImages',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Meta title', array(), 'Admin.Global'),
                        'name' => 'meta_title',
                        'maxlength' => 70,
                        'maxchar' => 70,
                        'lang' => true,
                        'rows' => 5,
                        'cols' => 100,
                        'hint' => $this->trans('Forbidden characters:', array(), 'Admin.Notifications.Info').' <>;=#{}'
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->trans('Meta description', array(), 'Admin.Global'),
                        'name' => 'meta_description',
                        'maxlength' => 160,
                        'maxchar' => 160,
                        'lang' => true,
                        'rows' => 5,
                        'cols' => 100,
                        'hint' => $this->trans('Forbidden characters:', array(), 'Admin.Notifications.Info').' <>;=#{}'
                    ),
                    array(
                        'type' => 'tags',
                        'label' => $this->trans('Meta keywords', array(), 'Admin.Global'),
                        'name' => 'meta_keywords',
                        'lang' => true,
                        'hint' => $this->trans(
                            'To add "tags," click in the field, write something, and then press "Enter."',
                            array(),
                            'Admin.Catalog.Help'
                        ).'&nbsp;'.$this->trans(
                            'Forbidden characters:',
                            array(),
                            'Admin.Notifications.Info'
                        ).' <>;=#{}'
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Friendly URL', array(), 'Admin.Global'),
                        'name' => 'link_rewrite',
                        'lang' => true,
                        'required' => true,
                        'hint' => $this->trans(
                            'Only letters, numbers, underscore (_) and the minus (-) character are allowed.',
                            array(),
                            'Admin.Catalog.Help'
                        )
                    ),
                
                    array(
                        'type' => 'group',
                        'label' => $this->trans('Group access', array(), 'Admin.Catalog.Feature'),
                        'name' => 'groupBox',
                        'values' => Group::getGroups(Context::getContext()->language->id),
                        'info_introduction' => $this->trans(
                            'You now have three default customer groups.',
                            array(),
                            'Admin.Catalog.Help'
                        ),
                        'unidentified' => $unidentified_group_information,
                        'guest' => $guest_group_information,
                        'customer' => $default_group_information,
                        'hint' => $this->trans(
                            'Mark all of the customer groups which you would like to have access to this category.',
                            array(),
                            'Admin.Catalog.Help'
                        )
                    )
                ),
                'submit' => array(
                    'title' => $this->trans('Save', array(), 'Admin.Actions'),
                    'name' => 'submitAdd'.$this->table.($this->_category->is_root_category
                        && !Tools::isSubmit('add'.$this->table)
                        && !Tools::isSubmit('add'.$this->table.'root') ? '': 'AndBackToParent')
                )
            );
            $this->tpl_form_vars['shared_category'] = Validate::isLoadedObject($obj) && $obj->hasMultishopEntries();
            $this->tpl_form_vars['PS_ALLOW_ACCENTED_CHARS_URL']= (int)Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL');
            $this->tpl_form_vars['displayBackOfficeCategory'] = Hook::exec('displayBackOfficeCategory');
            if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') && Tools::isSubmit('add'.$this->table.'root')) {
                $this->fields_form['input'][] = array(
                    'type' => 'switch',
                    'label' => $this->trans('Root Category', array(), 'Admin.Catalog.Feature'),
                    'name' => 'is_root_category',
                    'required' => false,
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'is_root_on',
                            'value' => 1,
                            'label' => $this->trans('Yes', array(), 'Admin.Global')
                        ),
                        array(
                            'id' => 'is_root_off',
                            'value' => 0,
                            'label' => $this->trans('No', array(), 'Admin.Global')
                        )
                    )
                );
                unset($this->fields_form['input'][2], $this->fields_form['input'][3]);
            }

            if (Shop::isFeatureActive()) {
                $this->fields_form['input'][] = array(
                    'type' => 'shop',
                    'label' => $this->trans('Shop association', array(), 'Admin.Global'),
                    'name' => 'checkBoxShopAsso',
                );
            }

            if ($this->_category->id_parent == (int)Configuration::get('PS_ROOT_CATEGORY') &&
                Tools::isSubmit('updatecategory')) {
                foreach ($this->fields_form['input'] as $k => $input) {
                    if (in_array($input['name'], array('id_parent', 'is_root_category'))) {
                        unset($this->fields_form['input'][$k]);
                    }
                }
            }

            if (!($obj = $this->loadObject(true))) {
                return;
            }

            $image = ImageManager::thumbnail(
                _PS_CAT_IMG_DIR_.'/'.$obj->id.'.'.$this->imageType,
                $this->table.'_'.(int)$obj->id.'.'.$this->imageType,
                350,
                $this->imageType,
                true
            );
            $this->fields_value = array(
                'image' => $image ? $image : false,
                'size' => $image ? filesize(_PS_CAT_IMG_DIR_.'/'.$obj->id.'.'.$this->imageType) / 1000 : false
            );
            $category_groups_ids = $obj->getGroups();
            $groups = Group::getGroups($this->context->language->id);
            if (empty($category_groups_ids)) {
                $preselected = array(
                    Configuration::get('PS_UNIDENTIFIED_GROUP'),
                    Configuration::get('PS_GUEST_GROUP'),
                    Configuration::get('PS_CUSTOMER_GROUP')
                );
                $category_groups_ids = array_merge($category_groups_ids, $preselected);
            }
            foreach ($groups as $group) {
                $this->fields_value['groupBox_'.$group['id_group']] = Tools::getValue(
                    'groupBox_'.$group['id_group'],
                    (
                        in_array(
                            $group['id_group'],
                            $category_groups_ids
                        )
                    )
                );
            }
            $this->fields_value['is_root_category'] = (bool)Tools::isSubmit('add'.$this->table.'root');
            $customField = Customfileds::getCustomFieldActive();
            if (count($customField) > 0) {
                $id_category =  Tools::getValue('id_category');
                $getLang = Customfileds::getLang();
                $ctf_value = array();

                if ($id_category) {
                    $getCustomData = Customfileds::getCustomData($id_category);
                }
                if (count($getCustomData) > 0) {
                    $splitData = Customfileds::splitData($getCustomData[0]['data']);
                }
                foreach ($customField as $value) {
                    $this->fields_form['input'][] = array(
                        'type' => 'textarea',
                        'label' => $this->trans($value['label'], array(), 'Admin.Global'),
                        'name' => $value['name'],
                        'autoload_rte' => true,
                        'lang' => true,
                        'hint' => $this->trans('Invalid characters:', array(), 'Admin.Notifications.Info').' <>;=#{}',
                            
                    );
                    foreach ($getLang as $lang) {
                        if (isset($splitData[$value['id_customfileds']]['data'][$lang['id_lang']])) {
                            $ccf = $splitData[$value['id_customfileds']]['data'][$lang['id_lang']];
                        } else {
                            $ccf = '';
                        }
                        $ctf_value[$lang['id_lang']] = $ccf;
                    }
                    if (isset($splitData[$value['id_customfileds']])) {
                        $this->fields_value[$value['name']] = $ctf_value;
                    }
                }
            }
        } else {
            $this->initToolbar();

            /** @var Category $obj */
            $obj = $this->loadObject(true);
            $context = Context::getContext();
            $id_shop = $context->shop->id;
            $selected_categories = array(
                (isset($obj->id_parent)
                    && $obj->isParentCategoryAvailable($id_shop))?
                    (int)$obj->id_parent : (int)Tools::getValue('id_parent', Category::getRootCategory()->id)
                );
            $unidentified = new Group(Configuration::get('PS_UNIDENTIFIED_GROUP'));
            $guest = new Group(Configuration::get('PS_GUEST_GROUP'));
            $default = new Group(Configuration::get('PS_CUSTOMER_GROUP'));

            $unidentified_group_information = sprintf(
                $this->l(
                    '%s - All people without a valid customer account.'
                ),
                '<b>'.$unidentified->name[$this->context->language->id].'</b>'
            );
            $guest_group_information = sprintf(
                $this->l(
                    '%s - Customer who placed an order with the guest checkout.'
                ),
                '<b>'.$guest->name[$this->context->language->id].'</b>'
            );
            $default_group_information = sprintf(
                $this->l(
                    '%s - All people who have created an account on this site.'
                ),
                '<b>'.$default->name[$this->context->language->id].'</b>'
            );

            if (!($obj = $this->loadObject(true))) {
                return;
            }

            $image = _PS_CAT_IMG_DIR_.$obj->id.'.'.$this->imageType;
            $image_url = ImageManager::thumbnail(
                $image,
                $this->table.'_'.(int)$obj->id.'.'.$this->imageType,
                350,
                $this->imageType,
                true,
                true
            );

            $image_size = file_exists($image) ? filesize($image) / 1000 : false;
            $images_types = ImageType::getImagesTypes('categories');
            $format = array();
            $thumb = $thumb_url = '';
            $formated_category= ImageType::getFormatedName('category');
            $formated_medium = ImageType::getFormatedName('medium');
            foreach ($images_types as $k => $image_type) {
                if ($formated_category == $image_type['name']) {
                    $format['category'] = $image_type;
                } elseif ($formated_medium == $image_type['name']) {
                    $format['medium'] = $image_type;
                    $thumb = _PS_CAT_IMG_DIR_.$obj->id.'-'.$image_type['name'].'.'.$this->imageType;
                    if (is_file($thumb)) {
                        $thumb_url = ImageManager::thumbnail(
                            $thumb,
                            $this->table.'_'.(int)$obj->id.'-thumb.'.$this->imageType,
                            (int)$image_type['width'],
                            $this->imageType,
                            true,
                            true
                        );
                    }
                }
            }

            if (!is_file($thumb)) {
                $thumb = $image;
                $thumb_url = ImageManager::thumbnail(
                    $image,
                    $this->table.'_'.(int)$obj->id.'-thumb.'.$this->imageType,
                    125,
                    $this->imageType,
                    true,
                    true
                );
                ImageManager::resize(
                    _PS_TMP_IMG_DIR_.$this->table.'_'.(int)$obj->id.'-thumb.'.$this->imageType,
                    _PS_TMP_IMG_DIR_.$this->table.'_'.(int)$obj->id.'-thumb.'.$this->imageType,
                    (int)$image_type['width'],
                    (int)$image_type['height']
                );
            }

            $thumb_size = file_exists($thumb) ? filesize($thumb) / 1000 : false;

            $this->fields_form = array(
                'tinymce' => true,
                'legend' => array(
                    'title' => $this->l('Category'),
                    'icon' => 'icon-tags'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Name'),
                        'name' => 'name',
                        'lang' => true,
                        'required' => true,
                        'class' => 'copy2friendlyUrl',
                        'hint' => $this->l('Invalid characters:').' <>;=#{}',
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Displayed'),
                        'name' => 'active',
                        'required' => false,
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        )
                    ),
                    array(
                        'type'  => 'categories',
                        'label' => $this->l('Parent category'),
                        'name'  => 'id_parent',
                        'tree'  => array(
                            'id'                  => 'categories-tree',
                            'selected_categories' => $selected_categories,
                            'disabled_categories' => (!Tools::isSubmit('add'.$this->table)
                                && !Tools::isSubmit('submitAdd'.$this->table)) ? array($this->_category->id) : null,
                            'root_category'       => $context->shop->getCategory()
                        )
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Description'),
                        'name' => 'description',
                        'autoload_rte' => true,
                        'lang' => true,
                        'hint' => $this->l('Invalid characters:').' <>;=#{}'
                    ),
                    array(
                        'type' => 'file',
                        'label' => $this->l('Category Cover Image'),
                        'name' => 'image',
                        'display_image' => true,
                        'image' => $image_url ? $image_url : false,
                        'size' => $image_size,
                        'delete_url' => self::$currentIndex.'&'.$this->identifier.'='.
                        $this->_category->id.'&token='.$this->token.'&deleteImage=1',
                        'hint' => $this->l(
                            'This is the main image for your category, displayed in the category page. The
                            category description will overlap this image and appear in its top-left corner.'
                        ),
                        'format' => $format['category']
                    ),
                    array(
                        'type' => 'file',
                        'label' => $this->l('Category thumbnail'),
                        'name' => 'thumb',
                        'display_image' => true,
                        'image' => $thumb_url ? $thumb_url : false,
                        'size' => $thumb_size,
                        'format' => $format['medium']
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Meta title'),
                        'name' => 'meta_title',
                        'maxchar' => 70,
                        'lang' => true,
                        'rows' => 5,
                        'cols' => 100,
                        'hint' => $this->l('Forbidden characters:').' <>;=#{}'
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Meta description'),
                        'name' => 'meta_description',
                        'maxchar' => 160,
                        'lang' => true,
                        'rows' => 5,
                        'cols' => 100,
                        'hint' => $this->l('Forbidden characters:').' <>;=#{}'
                    ),
                    array(
                        'type' => 'tags',
                        'label' => $this->l('Meta keywords'),
                        'name' => 'meta_keywords',
                        'lang' => true,
                        'hint' => $this->l(
                            'To add "tags," click in the field, write something, and then press "Enter."'
                        ).
                        '&nbsp;'.$this->l('Forbidden characters:').' <>;=#{}'
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Friendly URL'),
                        'name' => 'link_rewrite',
                        'lang' => true,
                        'required' => true,
                        'hint' => $this->l(
                            'Only letters, numbers, underscore (_) and the minus (-) character are allowed.'
                        )
                    ),
                    array(
                        'type' => 'group',
                        'label' => $this->l('Group access'),
                        'name' => 'groupBox',
                        'values' => Group::getGroups(Context::getContext()->language->id),
                        'info_introduction' => $this->l('You now have three default customer groups.'),
                        'unidentified' => $unidentified_group_information,
                        'guest' => $guest_group_information,
                        'customer' => $default_group_information,
                        'hint' => $this->l(
                            'Mark all of the customer groups which you would like to have access to this category.'
                        )
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'submitAdd'.$this->table.($this->_category->is_root_category
                        && !Tools::isSubmit('add'.$this->table)
                        && !Tools::isSubmit('add'.$this->table.'root') ? '': 'AndBackToParent')
                )
            );

            $this->tpl_form_vars['shared_category'] = Validate::isLoadedObject($obj) && $obj->hasMultishopEntries();
            $this->tpl_form_vars['PS_ALLOW_ACCENTED_CHARS_URL']= (int)Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL');
            $this->tpl_form_vars['displayBackOfficeCategory'] = Hook::exec('displayBackOfficeCategory');

            // Display this field only if multistore option is enabled
            if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') && Tools::isSubmit('add'.$this->table.'root')) {
                $this->fields_form['input'][] = array(
                    'type' => 'switch',
                    'label' => $this->l('Root Category'),
                    'name' => 'is_root_category',
                    'required' => false,
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'is_root_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'is_root_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    )
                );
                unset($this->fields_form['input'][2], $this->fields_form['input'][3]);
            }
            // Display this field only if multistore option is enabled AND there are several stores configured
            if (Shop::isFeatureActive()) {
                $this->fields_form['input'][] = array(
                    'type' => 'shop',
                    'label' => $this->l('Shop association'),
                    'name' => 'checkBoxShopAsso',
                );
            }
            if ($this->_category->id_parent == (int)Configuration::get('PS_ROOT_CATEGORY') &&
                Tools::isSubmit('updatecategory')) {
                foreach ($this->fields_form['input'] as $k => $input) {
                    if (in_array($input['name'], array('id_parent', 'is_root_category'))) {
                        unset($this->fields_form['input'][$k]);
                    }
                }
            }

            if (!($obj = $this->loadObject(true))) {
                return;
            }

            $image = ImageManager::thumbnail(
                _PS_CAT_IMG_DIR_.'/'.$obj->id.'.'.$this->imageType,
                $this->table.'_'.(int)$obj->id.'.'.$this->imageType,
                350,
                $this->imageType,
                true
            );

            $this->fields_value = array(
                'image' => $image ? $image : false,
                'size' => $image ? filesize(_PS_CAT_IMG_DIR_.'/'.$obj->id.'.'.$this->imageType) / 1000 : false
            );

            // Added values of object Group
            $category_groups_ids = $obj->getGroups();

            $groups = Group::getGroups($this->context->language->id);
            // if empty $carrier_groups_ids : object creation : we set the default groups
            if (empty($category_groups_ids)) {
                $preselected = array(
                    Configuration::get('PS_UNIDENTIFIED_GROUP'),
                    Configuration::get('PS_GUEST_GROUP'),
                    Configuration::get('PS_CUSTOMER_GROUP')
                );
                $category_groups_ids = array_merge($category_groups_ids, $preselected);
            }
            foreach ($groups as $group) {
                $this->fields_value['groupBox_'.$group['id_group']] = Tools::getValue(
                    'groupBox_'.$group['id_group'],
                    (
                        in_array(
                            $group['id_group'],
                            $category_groups_ids
                        )
                    )
                );
            }

            $this->fields_value['is_root_category'] = (bool)Tools::isSubmit('add'.$this->table.'root');
            $customField = Customfileds::getCustomFieldActive();
           
            if (count($customField) > 0) {
                $id_category =  Tools::getValue('id_category');
                $getLang = Customfileds::getLang();
                $ctf_value = array();

                if ($id_category) {
                    $getCustomData = Customfileds::getCustomData($id_category);
                }
                if (count($getCustomData) > 0) {
                    $splitData = Customfileds::splitData($getCustomData[0]['data']);
                }
                foreach ($customField as $value) {
                    $this->fields_form['input'][] = array(
                        'type' => 'textarea',
                        'label' => $this->l($value['label']),
                        'name' => $value['name'],
                        'autoload_rte' => true,
                        'lang' => true,
                        'hint' => $this->l('Invalid characters'),
                    );
                    foreach ($getLang as $lang) {
                        if (isset($splitData[$value['id_customfileds']]['data'][$lang['id_lang']])) {
                            $ccf = $splitData[$value['id_customfileds']]['data'][$lang['id_lang']];
                        } else {
                            $ccf = '';
                        }
                        $ctf_value[$lang['id_lang']] = $ccf;
                    }

                    if (isset($splitData[$value['id_customfileds']])) {
                        $this->fields_value[$value['name']] = $ctf_value;
                    }
                }
            }
        }
        return AdminController::renderForm();
    }

    public function postProcess()
    {
        if ((bool)Tools::isSubmit('submitAddcategoryAndBackToParent') || (bool)Tools::isSubmit('submitAddcategory')) {
            $params = array();
            $params['id'] = Tools::getValue('id_category');
            $data = array();
            $customField = Customfileds::getCustomFieldActive();
            $getLang = Customfileds::getLang();
            foreach ($customField as $cf) {
                $data[$cf['name']] = array();
                foreach ($getLang as $value) {
                    $data[$cf['name']][$value['id_lang']] = Tools::getValue($cf['name'].'_'.$value['id_lang']);
                }
            }
            Customfileds::updateCategoryFields($data, $params);
        }
        return parent::postProcess();
    }
}
