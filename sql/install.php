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

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'customfileds` (
    `id_customfileds` int(11) NOT NULL AUTO_INCREMENT,
    `name` text NOT NULL,
    `id_shop` int(2) NOT NULL,
    `hook` text NOT NULL,
    `label` text NOT NULL,
	`active` tinyint(4) DEFAULT "1",
	`position` int(11) NOT NULL,
	PRIMARY KEY  (`id_customfileds`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';


$sql[] ='CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'customfileds_data` (
  `id_customfileds_data` int(11) NOT NULL AUTO_INCREMENT,
  `id_category` int(11) NOT NULL,
  `id_shop` int(2) NOT NULL,
  `data` text,
  PRIMARY KEY  (`id_customfileds_data`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
