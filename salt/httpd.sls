httpd:
   pkg:
     - installed
   service:
     - running
     - enable: True
     - require:
       - pkg: httpd
