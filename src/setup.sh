#!/bin/bash

echo "Running SediCMS setup script...";

if [ "$EUID" -ne 0 ] ;
    then echo "Please run as root";
    exit
fi

echo "This script will make a number of changes to your system";
echo "Please cancel the script if you do not wish to do the following";
echo "- Create directories and subdirectories under /opt/sedicms/";
echo "- Create SediCMS config file";
echo "- Enable a2enmod rewrite";
echo "- Update folder ownership under /opt/sedicms/ to belong to www-data";
echo "- Enable php curl";

echo " ";
echo "Sleeping for 5 seconds...";

sleep 5

# TODO: install apache2
# TODO: install mysql/postgres/etc
# TODO: configure DB
# TODO: configure apache2 config
# TODO: install libapache2-mod-php and php-mysql --- sudo apt install php libapache2-mod-php php-mysql

# TODO: enable opcache
# edit php.ini to do this

# TODO: OPTIONAL install & enable memcached
# sudo apt-get install php-memcached && echo "php-memcached successfully installed" || echo "php-memcached not successfully installed";
# sudo systemctl enable memcached && echo "memcached successfully enabled" || echo "memcached not successfully enabled";
# telnet 127.0.0.1 11211
#set mykey 0 900 4
#test
#get mykey
# sudo apt install php-memcached -y

#a2enmod proxy
#a2enmod proxy_http
#a2enmod rewrite
#a2enmod ssl
#a2enmod headers


sudo a2enmod rewrite && echo "rewrite successfully enabled" || echo "rewrite not successfully enabled";

sudo mkdir -p /opt/sedicms/logs/worker && echo "/opt/sedicms/logs/worker successfully created" || echo "/opt/sedicms/logs/worker not successfully created";
sudo mkdir -p /opt/sedicms/logs/backend && echo "/opt/sedicms/logs/backend successfully created" || echo "/opt/sedicms/logs/backend not successfully created";
sudo mkdir -p /opt/sedicms/backups && echo "/opt/sedicms/backups successfully created" || echo "/opt/sedicms/backups not successfully created";
sudo mkdir -p /opt/sedicms/templates && echo "/opt/sedicms/templates successfully created" || echo "/opt/sedicms/templates not successfully created";
sudo mkdir -p /opt/sedicms/plugins && echo "/opt/sedicms/plugins successfully created" || echo "/opt/sedicms/plugins not successfully created";

chown www-data:www-data /opt/sedicms/logs/worker && echo "/opt/sedicms/logs/worker successfully chowned" || echo "/opt/sedicms/logs/worker not successfully chowned";
chown www-data:www-data /opt/sedicms/logs/backend && echo "/opt/sedicms/logs/backend successfully chowned" || echo "/opt/sedicms/logs/backend not successfully chowned";

config_file="/opt/sedicms/config"

sudo touch $config_file && echo "$config_file successfully created" || echo "$config_file not successfully created";


if [ `grep "^BACKEND.REQUIRE.HTTPS" "$config_file" | wc -l` -eq 0 ] ;
    then
    echo "BACKEND.REQUIRE.HTTPS not found in $config_file";
    echo "BACKEND.REQUIRE.HTTPS=true" >> $config_file
fi

if [ `grep "^BACKEND.REQUIRE.DOMAIN" "$config_file" | wc -l` -eq 0 ] ;
    then
    echo "BACKEND.REQUIRE.DOMAIN not found in $config_file";
    echo "BACKEND.REQUIRE.DOMAIN=true" >> $config_file
fi

if [ `grep "^BACKEND.DOMAIN" "$config_file" | wc -l` -eq 0 ] ;
    then
    echo "BACKEND.DOMAIN not found in $config_file";
    echo "BACKEND.DOMAIN=example.com" >> $config_file
fi

if [ `grep "^DB.USER" "$config_file" | wc -l` -eq 0 ] ;
    then
    echo "DB.USER not found in $config_file";
    echo "DB.USER=root" >> $config_file
fi

if [ `grep "^DB.PASSWORD" "$config_file" | wc -l` -eq 0 ] ;
    then
    echo "DB.PASSWORD not found in $config_file";
    echo "DB.PASSWORD=password" >> $config_file
fi

if [ `grep "^DB.IP" "$config_file" | wc -l` -eq 0 ] ;
    then
    echo "DB.IP not found in $config_file";
    echo "DB.IP=localhost" >> $config_file
fi

if [ `grep "^AUDIT.DIR" "$config_file" | wc -l` -eq 0 ] ;
    then
    echo "AUDIT.DIR not found in $config_file";
    echo "AUDIT.DIR=/opt/sedicms/audit" >> $config_file
fi

if [ `grep "^AUDIT.BUFFER_SIZE" "$config_file" | wc -l` -eq 0 ] ;
    then
    echo "AUDIT.BUFFER_SIZE not found in $config_file";
    echo "AUDIT.BUFFER_SIZE=20" >> $config_file
fi

if [ `grep "^WORKER.LOG.DIR" "$config_file" | wc -l` -eq 0 ] ;
    then
    echo "WORKER.LOG.DIR not found in $config_file";
    echo "WORKER.LOG.DIR=/opt/sedicms/logs/worker" >> $config_file
fi

if [ `grep "^BACKEND.LOG.DIR" "$config_file" | wc -l` -eq 0 ] ;
    then
    echo "BACKEND.LOG.DIR not found in $config_file";
    echo "BACKEND.LOG.DIR=/opt/sedicms/logs/backend" >> $config_file
fi

sudo apt-get install php-curl && echo "php-curl successfully installed" || echo "php-curl not successfully created";


echo " ";
echo " ";

echo "Are you using SSL? If so, update the default ssl site with your certificate information, enable ssl module for apache, enable the ssl site, and restart apache";
echo "If you are not using SSL, you can ignore this message";