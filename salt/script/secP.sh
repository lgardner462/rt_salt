#!/bin/bash
. rt-db-pass
touch /var/www/html/racktables/inc/secret.php
chown apache:apache /var/www/html/racktables/inc/secret.php
chmod 466 /var/www/html/racktables/inc/secret.php
secretphp=/var/www/html/racktables/inc/secret.php
echo "<?php"  >> $secretphp
echo "\$pdo_dsn = 'mysql:host=localhost;dbname=racktables';" >> $secretphp
echo "\$db_username = 'rackuser';" >> $secretphp
echo "\$db_password = $RACKUSERPASSWORD;" >> $secretphp
echo "\$user_auth_src = 'database';" >> $secretphp
echo "\$require_local_account = TRUE;" >> $secretphp
echo "?>" >> $secretphp

