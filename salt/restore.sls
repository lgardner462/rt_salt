salt://srv/salt/script/2016-09-01_racktables_backup.sql:
  module.run:
    - name: cp.get_file
    - dest: /tmp/2016-09-01_racktables_backup.sql
    - path: salt://script/2016-09-01_racktables_backup.sql
#restore:
#    cmd.run:
#    - name: "mysql racktables < /tmp/2016-09-01_racktables_backup.sql"

salt://srv/salt/script/restore.sh -f:
  cmd.script:
    - source: salt://script/restore.sh


