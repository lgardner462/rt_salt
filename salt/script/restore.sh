#!/bin/bash
. /usr/local/etc/rt-db-pass
mysql -uroot -p$ROOTDBPASSWORD racktables < /tmp/2016-09-01_racktables_backup.sql

