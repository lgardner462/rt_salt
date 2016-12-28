#!/bin/bash
mkdir /root/bin
mkdir /root/prev-tables
mkdir /root/backup_DB
mkdir /root/rt-change
mkdir /root/rt-change/changelogs
mkdir /root/rt-git
cd /root/rt-git && git init
git clone git@github.com:mghpcc-projects/holyoke.git
crontab -l > mycron
echo "5 0 * * * /root/bin/mysql-dump.sh" >> mycron
echo "4 0 * * * /root/bin/update-from-git.sh" >> mycron
crontab mycron
rm mycron


