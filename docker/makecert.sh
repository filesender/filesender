#!/bin/bash
#
openssl req -newkey rsa:3072 -new -x509 -days 3652 -nodes -out assets/config/simplesamlphp/cert/localhost.crt -keyout assets/config/simplesamlphp/cert/localhost.pem -subj "/C=XX/ST=StateName/L=CityName/O=CompanyName/OU=CompanySectionName/CN=CommonNameOrHostname"
echo Remember to update assets/config/simplesamlphp/metadata/saml20-idp-remote.php.dist with the new certificate!
