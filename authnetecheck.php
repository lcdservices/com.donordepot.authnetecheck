<?php
/**
 * https://civicrm.org/licensing
 */

require_once 'authnetecheck.civix.php';
require_once __DIR__.'/vendor/autoload.php';

use CRM_AuthNetEcheck_ExtensionUtil as E;

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function authnetecheck_civicrm_config(&$config) {
  _authnetecheck_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function authnetecheck_civicrm_xmlMenu(&$files) {
  _authnetecheck_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function authnetecheck_civicrm_install() {
  _authnetecheck_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function authnetecheck_civicrm_postInstall() {
  // Create an Direct Debit Payment Instrument
  CRM_Core_Payment_AuthorizeNetTrait::createPaymentInstrument(['name' => 'EFT']);
  _authnetecheck_civix_civicrm_postInstall();
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function authnetecheck_civicrm_uninstall() {
  _authnetecheck_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function authnetecheck_civicrm_enable() {
  _authnetecheck_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function authnetecheck_civicrm_disable() {
  _authnetecheck_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function authnetecheck_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  _authnetecheck_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function authnetecheck_civicrm_managed(&$entities) {
  _authnetecheck_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function authnetecheck_civicrm_caseTypes(&$caseTypes) {
  _authnetecheck_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function authnetecheck_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _authnetecheck_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_check().
 */
function authnetecheck_civicrm_check(&$messages) {
  $messages = CRM_AuthorizeNet_Webhook::check();
}

function authnetecheck_civicrm_navigationMenu(&$params) {
  $pages = array(
    'settings_page' => array(
      'label'      => 'AuthorizeNet Payments Settings',
      'name'       => 'AuthorizeNet Payments Settings',
      'url'        => 'civicrm/admin/contribute/authorizenetsettings',
      'parent'     => array('Administer', 'CiviContribute'),
      'permission' => 'access CiviContribute,administer CiviCRM',
      'operator'   => 'AND',
      'separator'  => NULL,
      'active'     => 1,
    ),
  );
  foreach ($pages as $item) {
    // Check that our item doesn't already exist.
    $menu_item_search = array('url' => $item['url']);
    $menu_items = array();
    CRM_Core_BAO_Navigation::retrieve($menu_item_search, $menu_items);
    if (empty($menu_items)) {
      $path = implode('/', $item['parent']);
      unset($item['parent']);
      _authnetecheck_civix_insert_navigation_menu($params, $path, $item);
    }
  }
}

/**
 * helper to determine if the contrib page has a authnet processor enabled
 *
 * @param $form
 *
 * @return bool
 */
function _authnetecheck_authnetEnabled($form) {
  $paymentProcessors = array();
  $formName = get_class($form);
  if ($formName == 'CRM_Contribute_Form_Contribution_Main') {
    $paymentProcessors = $form->getVar('_paymentProcessors');
  }
  elseif ($formName == 'CRM_Contribute_Form_Contribution_Confirm') {
    $paymentProcessor = $form->getVar('_paymentProcessor');
    $paymentProcessors = array($paymentProcessor);
  }
  foreach ($paymentProcessors as $processor) {
    if (in_array($processor['class_name'], array(
      'Payment_AuthNetCreditcard',
      'Payment_AuthNetEcheck',
    )) && $processor['is_active']
    ) {
      return TRUE;
    }
  }

  return FALSE;
}

/**
 * helper to determine if the contrib page has a authnet processor selected
 *
 * @param $form
 *
 * @return bool
 */
function _authnetecheck_authnetSelected($form) {
  $pp = civicrm_api(
    'PaymentProcessor',
    'getsingle',
    array(
      'id'      => $form->_params['payment_processor_id'],
      'version' => 3,
    )
  );
  return in_array($pp['class_name'], array('Payment_AuthNetCreditcard', 'Payment_AuthNetEcheck'));
}

function authnetecheck_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Contribute_Form_Contribution_Main' &&
    _authnetecheck_authnetEnabled($form)
  ) {
    $settings = CRM_Core_BAO_Setting::getItem('AuthorizeNet Payments Extension', 'authorizenet_settings');

    if (!empty($settings['enable_public_future_start'])) {
      $allow_days = empty($settings['days']) ? array('-1') : $settings['days'];
      $datetime   = $form->get('future_receive_date_time');
      if (empty($datetime)) {
        // this is to make sure we not rebuilding array indexes, as user goes back and forward
        // on the wizard. Otherwise element default doesn't work.
        $datetime = time();
        $form->set('future_receive_date_time', $datetime);
      }
      $start_dates = _authnetecheck_get_future_monthly_start_dates($datetime, $allow_days);
      $form->add('select', 'future_receive_date', ts('Transaction Date'), $start_dates);

      CRM_Core_Region::instance('price-set-1')->add(array(
        'template' => 'CRM/Contribute/Form/Contribution/Main.futureStartDate.tpl',
      ));
    }
  }
  if ($formName == 'CRM_Contribute_Form_Contribution_Confirm' &&
    _authnetecheck_authnetEnabled($form)
  ) {
    CRM_Core_Region::instance('page-body')->add(array(
      'template' => "CRM/Contribute/Form/Contribution/Confirm.futureStartDate.tpl",
    ));
    $session    = CRM_Core_Session::singleton();
    $futureDate = $session->get("future_receive_date_{$form->_params['qfKey']}");

    // override civi's receive date
    // 1. this makes contribution receive-date and recur start-date created by
    //    civi, set to future date, and hook doesn't require working it out.
    // 2. Also thankyou template displays correct date.
    // 3. Receipt will use same dates.
    $futureDate = $futureDate ? $futureDate : date('YmdHis');
    $form->_params['receive_date'] = $futureDate;
    $form->assign('receive_date', $futureDate);
  }
}

function authnetecheck_civicrm_postProcess($formName, &$form) {
  if (($formName == 'CRM_Contribute_Form_Contribution_Main') &&
    _authnetecheck_authnetEnabled($form)
  ) {
    $session = CRM_Core_Session::singleton();
    if (!empty($form->_submitValues['future_receive_date'])) {
      $session->set("future_receive_date_{$form->_submitValues['qfKey']}", $form->_submitValues['future_receive_date']);
    }
    else {
      $session->set("future_receive_date_{$form->_submitValues['qfKey']}", NULL);
    }
  }
}

/**
 * Function _authnetecheck_get_future_start_dates
 *
 * @string $start_date a timestamp, only return dates after this.
 * @array $allow_days an array of allowable days of the month.
 */
function _authnetecheck_get_future_monthly_start_dates($start_date, $allow_days) {
  // Future date options.
  $start_dates = array();
  // special handling for today - it means immediately or now.
  $today = date('YmdHis');
  $todaysDay = date('j');
  // If not set, only allow for the first 28 days of the month.
  if (max($allow_days) <= 0) {
    $allow_days = range(1, 31);
  }
  if (!in_array($todaysDay, $allow_days)) {
    $start_dates[''] = ts('Now');
  }
  for ($j = 0; $j < count($allow_days); $j++) {
    // So I don't get into an infinite loop somehow ..
    $i = 0;
    $dp = getdate($start_date);
    while (($i++ < 60) && !in_array($dp['mday'], $allow_days)) {
      $start_date += (24 * 60 * 60);
      $dp = getdate($start_date);
    }
    $key = date('YmdHis', $start_date);
    // special handling
    if ($key == $today) {
      $display = ts('Now');
      // date('YmdHis');
      $key = '';
    }
    else {
      $display = strftime('%B %e, %Y', $start_date);
    }
    $start_dates[$key] = $display;
    $start_date += (24 * 60 * 60);
  }
  return $start_dates;
}
