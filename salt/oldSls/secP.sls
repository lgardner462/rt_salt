#salt://srv/salt/script/secP.sh -f:
#  cmd.script:
#    - source: salt://script/secP.sh
#
salt://srv/salt/script/secret.php:
  file:
    - managed
    - name: /var/www/html/racktables/inc/secret.php
    - source: salt://script/secret.php
    - preserve: True


