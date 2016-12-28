#!/bin/bash
backupDirectory=/root/backup_DB
. /usr/local/etc/rt-db-pass
status=0
backupSQL=$( /usr/bin/mysqldump -uroot -p$ROOTDBPASSWORD racktables  > "$backupDirectory/"$(date +%d)_racktables_backup_attempt.sql )
status=$( expr $status \| $? )
if [ $status -ne "0" ]; then
	{
		echo "Racktables mysqldump failed."
		rm "$backupDirectory/"$(date +%d)_racktables_backup_attempt.sql
	}
else
	{
		echo "Racktables mysqldump succeeded."
		mv "$backupDirectory/"$(date +/%d)_racktables_backup_attempt.sql  "$backupDirectory/"$(date +/%d)_racktables_backup.sql
		$( gzip "$backupDirectory/"$(date +/%d)_racktables_backup.sql ) 
	}
fi
