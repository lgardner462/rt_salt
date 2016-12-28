#!/usr/bin/python


import datetime
import sys
import csv
import glob
import os
from subprocess import call
from collections import defaultdict



newFilePaths = []
prevFilePaths = []
prevFileNameOnly= []

#add filenames into arrays to diff files from 2 different folders

for filename in glob.iglob('/root/rt-git/holyoke/mghpcc_rack_plotting/current_tables/*.txt'):
	if "ldev" not in filename and "all" not in filename:     
		newFilePaths.append(filename)
for filename in glob.iglob('/root/prev-tables/*.txt'):
	if "ldev" not in filename and "all" not in filename:     
		prevFilePaths.append(filename)
		prevFileNameOnly.append(filename.rsplit('/',1)[1])

for new in newFilePaths:
	if new.rsplit('/',1)[1] not in prevFileNameOnly:
		prevFileNameOnly.append(new.rsplit('/',1)[1])
                #make a new blank file in prev-tables directory to diff against
                newfp =  '/root/prev-tables/' + new.rsplit('/',1)[1]
		prevFilePaths.append(newfp)
		testText = str("'test' , '1' , 'a' , '1' , '44' , '44' , 'test' , '{ppool: test },{pgroup: test }' , 'test')")
                temp = open(newfp, 'a')
		temp.write(testText)
		temp.close()

######################
#    CSV HANDLING    #
######################
for old in prevFilePaths:
	for new in newFilePaths:
		#check if file name is in previous directory
		if new.rsplit('/',1)[1] not in prevFileNameOnly:
			prevFileNameOnly.append(new.rsplit('/',1)[1])
			#make a new blank file in prev-tables directory to diff against 			
			newfp = old.rsplit('/',1)[0] +'/' + new.rsplit('/',1)[1]
			open(newfp, 'a').close()
		if old.rsplit('/', 1)[1] == new.rsplit('/', 1)[1]:
			#Open CSV file
			try:
				rackCSVreader = csv.reader(open(old,'rU'),quotechar="'",delimiter=',',skipinitialspace=True)
			except(IOError):
				print("Inventory CSV file must be supplied in order to proceed.") 
				sys.exit(0)
			try:
				rackCSVreaderNEW = csv.reader(open(new,'rU'),quotechar="'",delimiter=',',skipinitialspace=True)
			except(IOError):
				print("Inventory CSV file must be supplied in order to proceed.") 
				sys.exit(0)
			today = datetime.date.today()
			orig_stdout = sys.stdout
			rowCounterOldCSV = 0
			devicenameOld = []
			allRanges=[]
			rownoOld = []
			podnoOld = []
			cabnoOld = []
			uloOld = []
			uhiOld = []
			devicetypeOld = []
			primarypoolOld = []
			primarygroupOld = []
			fullCSV = []
			rackTablesObjectTypeOld=[]
			rowCounterNewCSV = 0
			devicenameNew = []
			rownoNew = []
			podnoNew = []
			cabnoOld1 = []
			uloNew = []
			uhiNew = []
			devicetypeNew = []
			primarypoolNew = []
			primarygroupNew = []
			rackTablesObjectTypeNew=[]
			fullCSVnew=[]
			changeLog = ("/root/rt-change/changelogs/changelog-" + str(today))
			cl = file(changeLog,'a')
		#gather data from old CSV
			for row in rackCSVreader:
				try:
					devicenameOld.append(row[0].replace("'",""))
					rownoOld.append(row[1:2][0].replace("'",""))
					podnoOld.append(row[2:3][0].replace("'",""))
					cabnoOld.append(row[3:4][0].replace("'",""))
					uloOld.append((row[4:5][0].replace("'","")))
					uhiOld.append((row[5:6][0].replace("'","")))
					devicetypeOld.append(row[6:7][0].replace("'",""))
					fPpool=((str(row[7:8][0]).split(",",1)[0])).replace('{ppool: "',"").replace("'","").rstrip('"}')
					primarypoolOld.append(fPpool)
					fPGroup=((str(row[7:8][0]).split(",",1)[1])).replace('{pgroup: "',"").replace('"}','')
					#fPGroup+=((str(row[7:8][0]).split(",",1)[2])).replace('{pgroup: "',"").replace('"}','')
					primarygroupOld.append(fPGroup)
					rackTablesObjectTypeOld.append(row[8:9][0])
					full= row[:]
					formatFull=';'.join(full)
					fullCSV.append(formatFull)
					rowCounterOldCSV+=1
				except(IndexError):
					pass
		#gather data from new CSV
			for row in rackCSVreaderNEW:
				try:
					devicenameNew.append(row[0].replace("'",""))
					rownoNew.append(row[1:2][0].replace("'",""))
					podnoNew.append(row[2:3][0].replace("'",""))
					cabnoOld1.append(row[3:4][0].replace("'",""))
					uloNew.append((row[4:5][0].replace("'","")))
					uhiNew.append((row[5:6][0].replace("'","")))
					devicetypeNew.append(row[6:7][0].replace("'",""))
					fPpool1=((str(row[7:8][0]).split(",",1)[0])).replace('{ppool: "',"").rstrip('"}')
					primarypoolNew.append(fPpool)
					#primarypoolOld.append(row[7:8][0].replace("'","").replace('"',"").replace("ppool:","").strip("{} "))
					#fPGroup=((str(row[8:][0:])).replace("{pgroup:","").rstrip('"}\\\'[]')).lstrip("['").replace('"','').replace("'",'')
					fPGroup1=((str(row[7:8][0]).split(",",1)[1])).replace('{pgroup: "',"").rstrip('"}')
					#fPGroup1+=((str(row[7:8][0]).split(",",1)[2])).replace('{pgroup: "',"").replace('"}','')		
					primarygroupNew.append(fPGroup1)
					rackTablesObjectTypeNew.append(row[8:9])
					full=(row[:])
					formatFull=';'.join(full)
					fullCSVnew.append(formatFull)
					rowCounterNewCSV+=1
				except(IndexError):
					pass
###############################
#       ChangeLog             #
###############################
	
			modifiedEntrys = list(set(fullCSV) - set(fullCSVnew))
			newEntrysAdded = list(set(fullCSVnew)- set(fullCSV))
			newRowsAdded = []
			#Array of strings of  what items were before they were changed
			premodifiedItems = []
			#Array of strings of what the items were changed to
			postmodifiedItems = []
			#items that do not exist in the csv anymore
			deletedItems = []
			oldEntryNames = []
			newEntryNames = []
			sys.stdout = cl
			#check if entry was removed
			for i in modifiedEntrys:
				x = i.split(' ', 1)[0]
				oldEntryNames.append(x)
				for j in newEntrysAdded:
						y = j.split(' ', 1)[0]
						newEntryNames.append(y)
						if (x==y):
							#This entry was modified but is already instantianted, modify current entry
							print(str(today) + ":   ITEM WAS: " + i)
				 			print(str(today) + ":   ITEM IS NOW: " +j)
							premodifiedItems.appened(i)
							postmodifiedItems.append(j)
						#Item in old entry but not in new entry, Object name changed/deleted
				if (x not in newEntryNames):
						print(str(today) + ":   ENTRY DELETED: " + i)
						deletedItems.append(i)
			#adds in new entry
			for i in newEntrysAdded:
				x = i.split(' ', 1)[0]
				if (x not in oldEntryNames):
					print(str(today) + ":   NEW ENTRY: " + i)
					newRowsAdded.append(i.split(";"))
			postmodifiedItems.sort()
			outFile = ("/root/rt-change/R"+str(rownoNew[0]).strip() + "-P"+ podnoNew[0].upper().strip() + "-C"+ cabnoOld1[0].strip()+ "-"+ str(today) + "-update-csv.txt").strip()
			g = file(outFile, 'a')
			sys.stdout = g
##########################
# RACK TABLES FORMATTING #
##########################

			#Initializes Racktables database object, only happens once per object.
			for i in range(len(newRowsAdded)):
				try:
					#removes unneeded or unwanted characters, strings have to be formatted specifically for Racktables csv import plugin
					#further info can be found at /var/www/html/plugins/csv_import.php
					devicenameOld = str(newRowsAdded[i][0]).strip()
					rackTablesObjectTypeOld = str(newRowsAdded[i][8]).upper()
					primarypoolOld = str((newRowsAdded[i][7]).split(",",1)[0].replace('{ppool: "',"").rstrip('"}'))
					namePlusPool = devicenameOld + "-" + primarypoolOld
					primarygroupOld = str(newRowsAdded[i][7]).split(",",1)[1].replace('{pgroup: "',"").replace('"}','')
					devicetypeOld = str(newRowsAdded[i][6]).strip()
					rackName=str("r"+newRowsAdded[i][1].strip()+"-p"+newRowsAdded[i][2]).strip().upper()
					cabinetName = str("C"+newRowsAdded[i][3]).strip().upper()
					uRange= str(range((int(newRowsAdded[i][4])),(int(newRowsAdded[i][5]))+1)).strip("[]").replace(" ","")
					if uRange in allRanges:
						fibs=((len(range((int(newRowsAdded[i][4])),(int(newRowsAdded[i][5]))))))*"b,"+"b"
					else:
						allRanges.append(uRange)
					#fib indicates the location in the rack(front,inside,back) and has to be listed once for each u the object takes up.
					#checks the uloOld and uhiOldgh value from the entry row array, converts them to Ints and plugs them into Range(low,high)
						fibs=((len(range((int(newRowsAdded[i][4])),(int(newRowsAdded[i][5]))))))*"fi,"+"fi"
					out =("OBJECT;" + rackTablesObjectTypeOld+ ";" + namePlusPool + ";" + devicetypeOld +";" + namePlusPool+"\n")
					out += ("RACK;MIT;MGHPCC;"+rackName+";"+cabinetName+";44\n")
					out +=("OBJECTATTRIBUTE;"+namePlusPool+";"+"COMMENT;"+ "Primary Pool: " + primarypoolOld + "     Primary Group: " + primarygroupOld+"\n")
					out +=("RACKASSIGNMENT;"+namePlusPool+";"+cabinetName+";"+uRange+";"+fibs+";"+rackName+"\n")
					out += ("OBJECTTAG;"+namePlusPool+";"+primarypoolOld)
					#removed characters that caused POST/db issues
					out = out.replace(" ","%20")
					out = out.replace("<","(")
					out = out.replace(">",")")
					print out
				except(ValueError):
					pass
			#formats data for changing objects only, 
			for i in range(len(postmodifiedItems)):
				try:
					devicetypeOld=str(postmodifiedItems[i][6])	
					primarypoolOld= str(postmodifiedItems[i][7]).split(",",1)[0].replace('{ppool: "',"").rstrip('"}')
					primarygroupOld = str(postmodifiedItems[i][7]).split(",",1)[1].replace('{pgroup: "',"").rstrip('"} ')	
					devicenameOld=str(postmodifiedItems[i][0].strip())
					namePlusPool = devicenameOld + "-"+ primarypoolOld
					rackName=str("r"+postmodifiedItems[i][1].strip()+"-p"+postmodifiedItems[i][2]).strip().upper()
					cabinetName = str("C"+postmodifiedItems[i][3]).strip().upper()
					uRange= str(range((int(postmodifiedItems[i][4])),(int(postmodifiedItems[i][5]))+1)).strip("[]").replace(" ","")
					if uRange in allRanges:
						fibs=((len(range((int(newRowsAdded[i][4])),(int(newRowsAdded[i][5]))))))*"b,"+"b"
					else:
						allRanges.append(uRange)
						fibs=((len(range((int(newRowsAdded[i][4])),(int(newRowsAdded[i][5]))))))*"fi,"+"fi"
					#fibs=((len(range((int(postmodifiedItems[i][4])),(int(postmodifiedItems[i][5]))))))*"fib,"+"fib"
					out = ("RACK;MIT;MGHPCC;"+rackName+";"+cabinetName+";44\n")
					out += ("OBJECTATTRIBUTE;"+namePlusPool+";"+"COMMENT;"+ "Primary Pool:+" + primarypoolOld + "     Primary Group:+" + primarygroupOld+"\n")
					out += ("RACKASSIGNMENT;"+namePlusPool+";"+cabinetName+";"+uRange+";"+fibs+";"+rackName+"\n")
					out += ("OBJECTATTRIBUTE;"+namePlusPool+";"+"LABEL;"+devicetypeOld+"\n")
					out = out.replace(' ','%20')
					out = out.replace("<","(")
					out = out.replace(">",")")
					print out
				except(ValueError):
					pass
			sys.stdout = orig_stdout
			#f.close()
			g.close()
			if os.stat(outFile).st_size == 0:
				os.remove(outFile)

