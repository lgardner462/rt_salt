mariadb:
   pkg:
     - installed
   service:
     - running
     - enable: True
     - require:
       - pkg: mariadb

