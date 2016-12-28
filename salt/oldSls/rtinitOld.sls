salt://srv/salt/script/2016-08-04_racktables_backup.sql:
#  module.run:
#    - name: cp.get_file
#    - dest: /tmp/2016-08-04_racktables_backup.sql
#    - path: salt://script/2016-08-04_racktables_backup.sql
#rtInit:
#    cmd.run:
#    - name: "mysql racktables < /tmp/2016-08-04_racktables_backup.sql"

