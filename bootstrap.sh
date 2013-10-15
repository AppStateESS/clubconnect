#!/usr/bin/env bash

# This is a work-in-progress provisioning script for Vagrant.
# It's not done yet.  Don't use it.

# Configuration
DBUSER=phpwebsite
DBPASS=phpwebsite
DBNAME=phpwebsite

CONFIG=/var/phpws/config
FILES=/var/phpws/files
IMAGES=/var/phpws/images
LOGS=/var/phpws/logs

echo "==========="
echo "ClubConnect"
echo "==========="

echo "==================="
echo "Installing Packages"
echo "==================="
yum -y install http://download.fedoraproject.org/pub/epel/6/x86_64/epel-release-6-8.noarch.rpm
yum -y install httpd php-cli php-pgsql php-pecl-xdebug php-pdo php php-mbstring php-common php-soap php-gd php-xml php-pecl-apc postgresql-server postgresql phpmyadmin phpPgAdmin git

echo "====================="
echo "Setting up PostgreSQL"
echo "====================="
service postgresql initdb
cat << pgSQL > /var/lib/pgsql/data/pg_hba.conf
local phpwebsite phpwebsite           trust
host  all        postgres   0.0.0.0/0 trust
local all        postgres             trust
pgSQL
echo "listen_addresses = '*'" >> /var/lib/pgsql/data/postgresql.conf
service postgresql start
echo -e 'phpwebsite\nphpwebsite' | su - postgres -c 'createuser -SDREP phpwebsite'
su - postgres -c 'createdb -E utf8 -O phpwebsite phpwebsite'

echo "==========================="
echo "Configuring php[My|Pg]Admin"
echo "==========================="
cat << 'PGADMIN' > /etc/httpd/conf.d/phpPgAdmin.conf
Alias /phpPgAdmin /usr/share/phpPgAdmin
<Location /phpPgAdmin>
    Order deny,allow
    Allow from all
</Location>
PGADMIN
cat << 'PGADMINCFG' > /etc/phpPgAdmin/config.inc.php
<?php
$conf['servers'][0]['desc'] = 'PostgreSQL on phpWebSite Vagrant';
$conf['servers'][0]['port'] = 5432;
$conf['servers'][0]['sslmode'] = 'allow';
$conf['servers'][0]['defaultdb'] = 'template1';
$conf['servers'][0]['pg_dump_path'] = '/usr/bin/pg_dump';
$conf['servers'][0]['pg_dumpall_path'] = '/usr/bin/pg_dumpall';
$conf['servers'][0]['slony_support'] = false;
$conf['servers'][0]['slony_sql'] = '/usr/share/pgsql';
$conf['default_lang'] = 'auto';
$conf['autocomplete'] = 'default on';
$conf['extra_login_security'] = false;
$conf['owned_only'] = false;
$conf['show_comments'] = true;
$conf['show_advanced'] = false;
$conf['show_system'] = false;
$conf['show_reports'] = true;
$conf['reports_db'] = 'phppgadmin';
$conf['reports_schema'] = 'public';
$conf['reports_table'] = 'ppa_reports';
$conf['owned_reports_only'] = false;
$conf['min_password_length'] = 1;
$conf['left_width'] = 200;
$conf['theme'] = 'default';
$conf['show_oids'] = false;
$conf['max_rows'] = 30;
$conf['max_chars'] = 50;
$conf['use_xhtml_strict'] = false;
$conf['help_base'] = 'http://www.postgresql.org/docs/%s/interactive/';
$conf['ajax_refresh'] = 3;
$conf['version'] = 19;
?>
PGADMINCFG

echo "=================="
echo "Configuring Xdebug"
echo "=================="
cat << XDEBUG > /etc/php.d/xdebug.ini
zend_extension=/usr/lib64/php/modules/xdebug.so
xdebug.remote_enable=1
xdebug.remote_handler=dbgp
xdebug.remote_host=10.0.2.2
xdebug.remote_port=9000
xdebug.remote_autostart=0
XDEBUG

echo "=================="
echo "Configuring Apache"
echo "=================="
rm /etc/httpd/conf.d/welcome.conf
rm -rf /var/www/html
git clone https://github.com/AppStateESS/phpwebsite.git /var/www/html
chown apache:apache -R /var/www/html/{files,images,logs}
ln -sf /vagrant /var/www/html/mod/sdr
cp /vagrant/inc/sdr.defines.php /var/www/html/inc
service httpd start > /dev/null 2>&1

echo "===================="
echo "Configuring Firewall"
echo "===================="
iptables -I INPUT 5 -p tcp -m state --state=NEW --dport 80 -j ACCEPT
iptables -I INPUT 6 -p tcp -m state --state=NEW --dport 5432 -j ACCEPT

# Helpful Information
cat << USAGE
===============================================================================

 Thanks for trying ClubConnect!

 The server instance is now set up for you with a database full of juicy test
 data.

 Available Forwarded Ports (connect to localhost:xxxx):

            SSH: 2222
           HTTP: 7970
     PostgreSQL: 7972

 To connect to PostgreSQL from the command-line client:
     psql -h localhost -p 7972 -U postgres
 Or, through phpPgAdmin:
     http://localhost:7970/phpPgAdmin
     user: postgres
     [leave password blank]

 And of course, phpWebSite is located at:
     http://localhost:7970
 so head on over and get started!

===============================================================================
USAGE
