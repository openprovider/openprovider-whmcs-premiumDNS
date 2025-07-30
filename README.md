# NOT BEING MAINTAINED

**The Premium DNS Module will receive no updates until further notice**

# openprovider-whmcs-premiumDNS 

Openprovider’s premium DNS leverages Sectigo’s advanced DNS infrastructure to provide a single, 
integrated and easy to adopt solution which offers a 99.99% Uptime Guarantee and near real-time updates, 
features DDoS protection and blazing fast resolution. While our standard DNS service is sufficient for 
users who don’t have any special needs when it comes to DNS resolution, the premium DNS service can be an excellent 
service to your hosting services portfolio.



## Installation

 1. Copy the contents of `/modules/servers/openproviderpremiumdns` into `<WHMCS directory>/modules/servers/openproviderpremiumdns` 
 2. Set up a server and server group for the Openprovider PremiumDNS module in WHMCS.
 3. Set up a product with the Openprovider-premiumDNS module (make sure to require a domain) and start selling.
 4. Note that there is no test environment and records created with the premium DNS module will be billed to your Openprovider account.



## Premium DNS setup

Openprovider’s Premium DNS is the latest addition to our product portfolio, and you can offer it to your customers directly via WHMCS. 
We are extending our successful collaboration with Sectigo, an industry leader in SSL certificates and web security products, 
to introduce a Premium DNS service that will be the perfect complement to our current, free of charge, standard DNS offering. 
While our standard DNS service is sufficient for users who don’t have any special needs when it comes to DNS resolution, 
we recognize the increasing demand of our customers in this area, and we are launching this new product in order to address their needs.

Openprovider’s Premium DNS leverages Sectigo’s advanced DNS infrastructure to provide a single, 
integrated and easy to adopt solution that offers the following advantages to your customers:

- **Blazing Fast Resolution**: The first part of a request to your website is typically a DNS lookup. Sectigo’s Premium DNS ensures that your customers are always connected to the closest server, resulting in lower latency and faster access times to your web properties.
- **Global Presence**: All your DNS zones are pushed to Sectigo’s DNS servers around the globe, ensuring an optimal user experience for your global customers.
- **DDoS Protection**: DNS is the first step in the journey to your website. Sectigo’s Premium DNS keeps your DNS zone protected from DDoS DNS attacks at all times.
- **99.99% Uptime Guarantee**: Sectigo’s built-in smart routing and anycast capabilities ensure that your DNS zones are always online. Your websites will always be accessible regardless of regional disruptions.
- **Near Real-Time Updates**: Updates to your DNS records are carried out globally in near real-time.
- **Competitive Pricing**



## Technical dependencies

The Premium DNS module can be used with domains from any registrar, not only domains with Openprovider. 

## Features

The Openprovider Premium DNS WHMCS module provides a comprehensive set of features to manage Premium DNS zones directly from WHMCS:

- **Create Premium DNS Zone**
  - Automatically create a Premium DNS zone when registering a new domain
  - Enable Premium DNS during domain transfer
  - Set up Premium DNS for existing domains

- **View DNS Zone**
  - Retrieve and display existing Premium DNS zone details within WHMCS client area

- **Manage DNS Zone**
  - Navigate to and manage your Premium DNS zone via an integrated DNS panel

- **DNSSEC Management**
  - Activate or deactivate DNSSEC for Premium DNS zones
  - View DNSSEC records and DNSSEC status
  - Optionally users can **enable DNSSEC** during zone creation

- **Delete Premium DNS Zone**
  - Delete Premium DNS zones directly through the WHMCS client area

These features are built to ensure automation, flexibility, and security for your DNS management workflows.

## Configure the server module

1. From your WHMCS admin area, navigate to **System Settings > Servers**.  

![img](images/configure_server_1)

2. Click **Add New Server**, then select **Go to Advanced Mode**.  

![img](images/configure_server_2)

3. Add the server for API connectivity with the following details:  
   - **Name:** For example, `openprovider premiumDNS server`  
   - **Hostname:** `api.openprovider.eu`  
   - **Nameservers:**  
     ```
     ns1.sectigoweb.com  
     ns2.sectigoweb.com  
     ns3.sectigoweb.com  
     ns4.sectigoweb.com
     ```  
   - **Module:** Select `Openprovider PremiumDNS module`  
   - **Username:** Your Openprovider username  
   - **Password:** Your Openprovider password  
  
    ![img](images/configure_server_3)

    ![img](images/configure_server_4)

4. Once complete, click **Save Changes**.  
5. After the server is created, click **Create New Group**, provide a group name, assign the server you just created to the group, and **Save Changes**.  

![img](images/configure_server_5)

![img](images/configure_server_6)


## Configure premium DNS product

- Navigate to *products/services*, and create an appropriate product group if necessary.
- Create a new product, and select **Other** as product type and **Openprovider PremiumDNS** from the module dropdown
- Select the desired product group and name your DNS product something fun.

![img](images/create_new_product_step1)

- After continuing to the *edit product* page: on the *details* tab, check the *require domain* tickbox.
- Add the desired description and select welcome email options.

![img](images/create_new_product_step2)



- Set up pricing on the pricing tab. Consult your Openprovider account to determine the cost price for provisioning premium DNS.

- Under the **Module Settings** tab:  

1. Select **Openprovider PremiumDNS** as the **Module Name**.  
2. Select the **Server Group** that you created with the PremiumDNS server added.  
3. Enter the credentials for the Openprovider account you would like to use for provisioning PremiumDNS.  
4. Select your desired **Automation Settings** for provisioning this product.  


![img](images/create_new_product_step3)

- Under the *Custom Fields* tab, add a new field with the **Field Name** set to `DNSSEC`, **Field Type** as `Checkbox`, and a **Description** of your choice — this allows clients to enable DNSSEC during order placement if desired or later with client area Premium DNS zone management. Check the **Show on Order Form** box to make this option visible to clients during the ordering process.


![img](images/create_new_product_step4)

- Configure any other parameters of the product which you deem necessary and then you're ready to start offering premium DNS to your customers.



## End user workflow in WHMCS basic cart

The below example shows one way the provisioning module can work from the end user point of view. Various upsell options can be implemented via WHMCS to improve .

- End user selects from the categories sidebar “Premium DNS” (the name of your product group where premium DNS is located) and choose a premium DNS product

![img](images/end_user_workflow_step1)



- Customer will have several options for choosing a domain to be connected with the premium DNS service:



![img](images/end_user_workflow_step2)

- During checkout, users can optionally enable DNSSEC by selecting the checkbox provided if you have configured the DNSSEC custom field to appear on the order form.

![img](images/end_user_workflow_step3)

- Once the customer completes the purchase, the module will provision the premium DNS zone in your Openprovider account. 

## Client Area Premium DNS Product Features

Once a Premium DNS product is provisioned, clients can manage their service directly from the WHMCS client area. The module provides the following interactive options under the **Actions** panel:

![img](images/end_user_workflow_features1)

- **Manage PDNS**  
  Redirects the user to dnspanel in a new browser tab, allowing them to view and manage DNS records using dnspanel's DNS management interface.

- **Manage DNSSEC**  
  Opens a WHMCS-integrated screen where users can activate or deactivate DNSSEC for their Premium DNS zone. If DNSSEC is already enabled, the generated DNSSEC key will be displayed.

![img](images/end_user_workflow_features2)

- **Delete PDNS Zone**  
  Allows clients to delete the associated Premium DNS zone directly from the client area.

These features empower your clients with full control over their DNS configurations without requiring administrative intervention.
