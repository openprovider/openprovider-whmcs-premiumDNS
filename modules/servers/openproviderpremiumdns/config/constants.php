<?php

const CREATE_DNS_ZONE_TYPE = 'master';

const SUCCESS_MESSAGE = 'success';

const MODULE_NAME = 'Openprovider PremiumDNS';
const API_VERSION = '1.1'; // Use API Version 1.1
const REQUIRES_SERVER = true; // Set true if module requires a server to work
const DEFAULT_NON_SSL_PORT = '1111'; // Default Non-SSL Connection Port
const DEFAULT_SSL_PORT = '1112'; // Default SSL Connection Port
const SERVICE_SINGLE_SIGN_ON_LABEL = 'Login to Panel as User';
const ADMIN_SINGLE_SIGN_ON_LABEL = 'Login to Panel as Admin';

const CONFIG_OPTION_LOGIN_NAME = 'Login';
const CONFIG_OPTION_LOGIN_DESCRIPTION = 'Enter Openprovider login';

const CONFIG_OPTION_PASSWORD_NAME = 'Password';
const CONFIG_OPTION_PASSWORD_DESCRIPTION = 'Enter Openprovider password';

const ERROR_API_CLIENT_IS_NOT_CONFIGURED = 'Credentials are incorrect or api is not configured!';

const ERROR_NO_PRODUCT_ID_IN_QUERY_PARAMS = 'You have no product id in query parameters: "pid" does not exist!';
const ERROR_DOMAIN_NOT_FOUND_IN_OPENPROVIDER = 'This domain not found in Openprovider or something went wrong!';