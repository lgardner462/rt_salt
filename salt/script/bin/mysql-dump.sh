#!/bin/bash
. /usr/local/etc/rt-db-pass
/usr/bin/mysqldump -uroot -p$ROOTDBPASSWORD racktables  > /root/backup_DB/$(date +\%F)_racktables_backup.sql

