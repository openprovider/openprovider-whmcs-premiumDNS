<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'constants.php';

use OpenproviderPremiumDns\controller\AccountController;
use OpenproviderPremiumDns\controller\DNSController;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/**
 * Define module related meta data.
 *
 * Values returned here are used to determine module related abilities and
 * settings.
 *
 * @see https://developers.whmcs.com/provisioning-modules/meta-data-params/
 *
 * @return array
 */
function openproviderpremiumdns_MetaData()
{
    return array(
        'DisplayName' => MODULE_NAME,
        'APIVersion' => API_VERSION,
        'RequiresServer' => REQUIRES_SERVER,
        'DefaultNonSSLPort' => DEFAULT_NON_SSL_PORT,
        'DefaultSSLPort' => DEFAULT_SSL_PORT,
        'ServiceSingleSignOnLabel' => SERVICE_SINGLE_SIGN_ON_LABEL,
        'AdminSingleSignOnLabel' => ADMIN_SINGLE_SIGN_ON_LABEL,
    );
}

function openproviderpremiumdns_ConfigOptions()
{
    return [
        // a text field type allows for single line text input
        CONFIG_OPTION_LOGIN_NAME => [
            'Type' => 'text',
            'Description' => CONFIG_OPTION_LOGIN_DESCRIPTION,
            'SimpleMode' => true,
        ],
        // a password field type allows for masked text input
        CONFIG_OPTION_PASSWORD_NAME => [
            'Type' => 'password',
            'Description' => CONFIG_OPTION_PASSWORD_DESCRIPTION,
            'SimpleMode' => true,
        ],
    ];
}

/**
 * Provision a new instance of a product/service.
 *
 * Attempt to provision a new instance of a given product/service. This is
 * called any time provisioning is requested inside of WHMCS. Depending upon the
 * configuration, this can be any of:
 * * When a new order is placed
 * * When an invoice for a new order is paid
 * * Upon manual request by an admin user
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return string "success" or an error message
 */
function openproviderpremiumdns_CreateAccount(array $params)
{
    $controller = new AccountController();
    return $controller->createAccount($params);
}

/**
 * Additional actions a client user can invoke.
 *
 * Define additional actions a client user can perform for an instance of a
 * product/service.
 *
 * Any actions you define here will be automatically displayed in the available
 * list of actions within the client area.
 *
 * @return array
 */
function openproviderpremiumdns_ClientAreaCustomButtonArray()
{
    return array(
        "Manage PDNS" => "manage_pdns"
    );
}

/**
 * Custom function for performing manage pdns.
 *
 * Similar to all other module call functions, they should either return
 * 'success' or an error message to be displayed.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return string "success" or an error message
 */
function openproviderpremiumdns_manage_pdns(array $params)
{
    $controller = new DNSController();
    return $controller->showManagePdns($params);
}