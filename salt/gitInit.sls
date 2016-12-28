salt://srv/salt/script/id_rsa:
  file:
    - managed
    - name: /root/.ssh/id_rsa
    - source: salt://script/id_rsa
    - preserve: True
    - mode: 400
    - makedirs: True
salt://srv/salt/script/git-setup.sh -f:
  cmd.script:
    - source: salt://script/git-setup.sh

