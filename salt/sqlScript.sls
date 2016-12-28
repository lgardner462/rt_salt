salt://srv/salt/script/rt-db-pass:
  file:
    - managed
    - name: /usr/local/etc/rt-db-pass
    - source: salt://script/rt-db-pass
    - preserve: True
    - mode: 400
salt://srv/salt/script/create-secure-db.sh -f:
  cmd.script:
    - source: salt://script/create-secure-db.sh

