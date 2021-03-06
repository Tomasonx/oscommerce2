<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  class cm_account_sage_pay_cards {
    var $code;
    var $group;
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function cm_account_sage_pay_cards() {
      $this->code = get_class($this);
      $this->group = basename(dirname(__FILE__));

      $this->title = MODULE_CONTENT_ACCOUNT_SAGE_PAY_CARDS_TITLE;
      $this->description = MODULE_CONTENT_ACCOUNT_SAGE_PAY_CARDS_DESCRIPTION;

      if ( defined('MODULE_CONTENT_ACCOUNT_SAGE_PAY_CARDS_STATUS') ) {
        $this->sort_order = MODULE_CONTENT_ACCOUNT_SAGE_PAY_CARDS_SORT_ORDER;
        $this->enabled = (MODULE_CONTENT_ACCOUNT_SAGE_PAY_CARDS_STATUS == 'True');
      }

      $this->public_title = MODULE_CONTENT_ACCOUNT_SAGE_PAY_CARDS_LINK_TITLE;

      $sage_pay_enabled = false;

      if ( defined('MODULE_PAYMENT_INSTALLED') && tep_not_null(MODULE_PAYMENT_INSTALLED) && in_array('sage_pay_direct.php', explode(';', MODULE_PAYMENT_INSTALLED)) ) {
        if ( !class_exists('sage_pay_direct') ) {
          include(DIR_FS_CATALOG . 'includes/languages/' . $_SESSION['language'] . '/modules/payment/sage_pay_direct.php');
          include(DIR_FS_CATALOG . 'includes/modules/payment/sage_pay_direct.php');
        }

        $sage_pay_direct = new sage_pay_direct();

        if ( $sage_pay_direct->enabled ) {
          $sage_pay_enabled = true;

          if ( MODULE_PAYMENT_SAGE_PAY_DIRECT_TRANSACTION_SERVER == 'Test' ) {
            $this->title .= ' [Test]';
            $this->public_title .= ' (' . $sage_pay_direct->code . '; Test)';
          }
        }
      }

      if ( $sage_pay_enabled !== true ) {
        $this->enabled = false;

        $this->description = '<div class="secWarning">' . MODULE_CONTENT_ACCOUNT_SAGE_PAY_CARDS_ERROR_MAIN_MODULE . '</div>' . $this->description;
      }
    }

    function execute() {
      global $oscTemplate;

      $oscTemplate->_data['account']['account']['links']['sage_pay_cards'] = array('title' => $this->public_title,
                                                                                   'link' => OSCOM::link('ext/modules/content/account/sage_pay/cards.php', '', 'SSL'),
                                                                                   'icon' => 'newwin');
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_CONTENT_ACCOUNT_SAGE_PAY_CARDS_STATUS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Sage Pay Card Management',
        'configuration_key' => 'MODULE_CONTENT_ACCOUNT_SAGE_PAY_CARDS_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to enable the Sage Pay Card Management module?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_CONTENT_ACCOUNT_SAGE_PAY_CARDS_SORT_ORDER',
        'configuration_value' => '0',
        'configuration_description' => 'Sort order of display. Lowest is displayed first.',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'date_added' => 'now()'
      ]);
    }

    function remove() {
      return Registry::get('Db')->query('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->keys()) . '")')->rowCount();
    }

    function keys() {
      return array('MODULE_CONTENT_ACCOUNT_SAGE_PAY_CARDS_STATUS', 'MODULE_CONTENT_ACCOUNT_SAGE_PAY_CARDS_SORT_ORDER');
    }
  }
?>
