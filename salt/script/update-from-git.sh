#!/bin/bash
cp -f /root/rt-git/holyoke/mghpcc_rack_plotting/current_tables/* /root/prev-tables
cd /root/rt-git/holyoke/mghpcc_rack_plotting/current_tables 
/usr/bin/git pull origin master
cd /root/rt-git/holyoke/mghpcc_rack_plotting/initial_bits
make
cd /root/rt-change
file=*update-csv.txt
/root/bin/update.py
file=*update-csv.txt
FILES=/root/rt-change/*-update-csv.txt
IFS=$'\n'
if ls /root/rt-change/*-update-csv.txt 1> /dev/null 2>&1;then
	 for f in $FILES;do
		 while IFS= read -r line;do 
			name='csv_text=';
			body="$line";
			data=$name$body;
			wget --delete-after -q --user=admin --password=password --post-data=$data "http://10.1.1.234/racktables/index.php?module=redirect&page=import&tab=default&op=importData";
			done < $f;
	 done;
fi
rm -f $file
