#!/bin/sh
if [ ! -f "./n98-magerun.phar" ]; then
    wget https://raw.githubusercontent.com/netz98/n98-magerun/master/n98-magerun.phar
fi

php n98-magerun.phar install --noDownload --dbHost="127.0.0.1" --dbUser="root" --dbPass="topsecret" --dbName="magento" --useDefaultConfigParams=yes --installationFolder="src" --baseUrl="http://localhost:8081/"
php n98-magerun.phar --root-dir="src" cache:flush