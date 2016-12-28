getPlugins:
  module.run:
    - name: cp.get_dir
    - dest: /var/www/html/
    - path: salt://script/plugins
salt://srv/salt/script/secret.php:
  file:
    - managed
    - name: /var/www/html/racktables/inc/secret.php
    - source: salt://script/secret.php
    - user: apache
    - group: apache
    - mode: 466

