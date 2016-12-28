#getRTupdateScripts:
#  module.run:
#    - name: cp.get_dir
#    - dest: /root
#    - path: salt://script/bin
salt://srv/salt/script/bin/update-from-git.sh:
  file:
    - managed
    - name: /root/bin/update-from-git.sh
    - source: salt://script/bin/update-from-git.sh
    - preserve: True
    - mode: 755
salt://srv/salt/script/bin/mysql-dump.sh:
  file:
    - managed
    - name: /root/bin/mysql-dump.sh
    - source: salt://script/bin/mysql-dump.sh
    - preserve: True
    - mode: 755
salt://srv/salt/script/bin/update.py:
  file:
    - managed
    - name: /root/bin/update.py
    - source: salt://script/bin/update.py
    - preserve: True
    - mode: 755

