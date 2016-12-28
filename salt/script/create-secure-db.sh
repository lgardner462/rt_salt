#!/bin/bash
. /usr/local/etc/rt-db-pass
mysql -uroot -e "
UPDATE mysql.user SET Password=PASSWORD('$ROOTDBPASSWORD') WHERE User='root';
DELETE FROM mysql.user WHERE User='';
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
DROP DATABASE IF EXISTS test;
DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';
FLUSH PRIVILEGES;
create database if not exists racktables;
grant all on racktables.* to root;
grant all on racktables.* to root@localhost;
grant all on racktables.* to rackuser;
grant all on racktables.* to rackuser@localhost;
set password for rackuser@localhost=password('$RACKUSERPASSWORD');"


