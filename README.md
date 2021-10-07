# openprovider-whmcs-premiumDNS

WHMCS Module for providing premium DNS in Openprovider.

## Installation

 1. Copy `./modules/servers/openproviderpremiumdns` into `<WHMCS directory>/modules/servers/openproviderpremiumdns` 

That's all.

## Usage:

### Create _Service_ with this module;

### Turn on a checkbox to confirm usage service with domains;

![img](images/service-setting-require-domain-checkbox.png)

### Configure Openprovider account data;

![img](images/service-setting-module-settings.png)

### Now you can use this service like common service;

 1. Choose the product;
 2. Select a domain that exists in Openprovider, or register a new one;
![img](images/shopping-cart-service-choose-domain.png)
 3. Confirm the order.
 4. The Openprovider Api sends request to activate premium dns:
![img](images/module-logs-premium-dns-activate.png)
