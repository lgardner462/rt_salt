#!/usr/bin/python


import datetime
import sys
import csv
import glob
import os
from subprocess import call
from collections import defaultdict



filenamesNew = []
filenamesOld = []
oldFileNoPath= []

#add filenames into arrays to diff files from 2 different folders

for filename in glob.iglob('/root/rt-git/holyoke/mghpcc_rack_plotting/current_tables/*.txt'):
	if "ldev" not in filename and "all" not in filename:     
		filenamesNew.append(filename)
for filename in glob.iglob('/root/prev-tables/*.txt'):
	if "ldev" not in filename and "all" not in filename:     
		filenamesOld.append(filename)
		oldFileNoPath.append(filename.rsplit('/',1)[1])

for new in filenamesNew:
	if new.rsplit('/',1)[1] not in oldFileNoPath:
		oldFileNoPath.append(new.rsplit('/',1)[1])
                #make a new blank file in prev-tables directory to diff against
                newfp =  '/root/prev-tables/' + new.rsplit('/',1)[1]
		filenamesOld.append(newfp)
		testText = str("'test' , '1' , 'a' , '1' , '44' , '44' , 'test' , '{ppool: test },{pgroup: test }' , 'test')")
                temp = open(newfp, 'a')
		temp.write(testText)
		temp.close()

######################
#    CSV HANDLING    #
######################
for old in filenamesOld:
	for new in filenamesNew:
		#check if file name is in previous directory
		if new.rsplit('/',1)[1] not in oldFileNoPath:
			oldFileNoPath.append(new.rsplit('/',1)[1])
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
			rowCounter = 0
			deviceName = []
			allRanges=[]
			rowNo = []
			podNo = []
			cabNo = []
			uLo = []
			uHi = []
			deviceType = []
			primaryPool = []
			primaryGroup = []
			fullCSV = []
			objectType=[]
			rowCounter1 = 0
			deviceName1 = []
			rowNo1 = []
			podNo1 = []
			cabNo1 = []
			uLo1 = []
			uHi1 = []
			deviceType1 = []
			primaryPool1 = []
			primaryGroup1 = []
			objectType1=[]
			fullCSVnew=[]
			changeLog = ("/root/rt-change/changelogs/changelog-" + str(today))
			cl = file(changeLog,'a')
		#gather data from old CSV
			for row in rackCSVreader:
				try:
					deviceName.append(row[0].replace("'",""))
					rowNo.append(row[1:2][0].replace("'",""))
					podNo.append(row[2:3][0].replace("'",""))
					cabNo.append(row[3:4][0].replace("'",""))
					uLo.append((row[4:5][0].replace("'","")))
					uHi.append((row[5:6][0].replace("'","")))
					deviceType.append(row[6:7][0].replace("'",""))
					fPpool=((str(row[7:8][0]).split(",",1)[0])).replace('{ppool: "',"").replace("'","").rstrip('"}')
					primaryPool.append(fPpool)
					fPGroup=((str(row[7:8][0]).split(",",1)[1])).replace('{pgroup: "',"").replace('"}','')
					#fPGroup+=((str(row[7:8][0]).split(",",1)[2])).replace('{pgroup: "',"").replace('"}','')
					primaryGroup.append(fPGroup)
					objectType.append(row[8:9][0])
					full= row[:]
					formatFull=';'.join(full)
					fullCSV.append(formatFull)
					rowCounter+=1
				except(IndexError):
					pass
		#gather data from new CSV
			for row in rackCSVreaderNEW:
				try:
					deviceName1.append(row[0].replace("'",""))
					rowNo1.append(row[1:2][0].replace("'",""))
					podNo1.append(row[2:3][0].replace("'",""))
					cabNo1.append(row[3:4][0].replace("'",""))
					uLo1.append((row[4:5][0].replace("'","")))
					uHi1.append((row[5:6][0].replace("'","")))
					deviceType1.append(row[6:7][0].replace("'",""))
					fPpool1=((str(row[7:8][0]).split(",",1)[0])).replace('{ppool: "',"").rstrip('"}')
					primaryPool1.append(fPpool)
					#primaryPool.append(row[7:8][0].replace("'","").replace('"',"").replace("ppool:","").strip("{} "))
					#fPGroup=((str(row[8:][0:])).replace("{pgroup:","").rstrip('"}\\\'[]')).lstrip("['").replace('"','').replace("'",'')
					fPGroup1=((str(row[7:8][0]).split(",",1)[1])).replace('{pgroup: "',"").rstrip('"}')
					#fPGroup1+=((str(row[7:8][0]).split(",",1)[2])).replace('{pgroup: "',"").replace('"}','')		
					primaryGroup1.append(fPGroup1)
					objectType1.append(row[8:9])
					full=(row[:])
					formatFull=';'.join(full)
					fullCSVnew.append(formatFull)
					rowCounter1+=1
				except(IndexError):
					pass
###############################
#       ChangeLog             #
###############################
	
			oldEntryChanged = list(set(fullCSV) - set(fullCSVnew))
			newEntryAdded = list(set(fullCSVnew)- set(fullCSV))
			newEntryRows = []
			#Array of strings of  what items were before they were changed
			changedItemsBef = []
			#Array of strings of what the items were changed to
			changedItemsAft = []
			#items that do not exist in the csv anymore
			deletedItems = []
			oldEntryNames = []
			newEntryNames = []
			sys.stdout = cl
			#check if entry was removed
			for i in oldEntryChanged:
				x = i.split(' ', 1)[0]
				oldEntryNames.append(x)
				for j in newEntryAdded:
						y = j.split(' ', 1)[0]
						newEntryNames.append(y)
						if (x==y):
							#This entry was modified but is already instantianted, modify current entry
							print(str(today) + ":   ITEM WAS: " + i)
				 			print(str(today) + ":   ITEM IS NOW: " +j)
							changedItemsBef.append(i.split(";"))				
							changedItemsAft.append(j.split(";"))
						#Item in old entry but not in new entry, Object name changed/deleted
				if (x not in newEntryNames):
						print(str(today) + ":   ENTRY DELETED: " + i)
						deletedItems.append(i)
			#adds in new entry
			for i in newEntryAdded:
				x = i.split(' ', 1)[0]
				if (x not in oldEntryNames):
					print(str(today) + ":   NEW ENTRY: " + i)
					newEntryRows.append(i.split(";"))


			outFile = ("/root/rt-change/R"+str(rowNo1[0]).strip() + "-P"+ podNo1[0].upper().strip() + "-C"+ cabNo1[0].strip()+ "-"+ str(today) + "-update-csv.txt").strip()
			g = file(outFile, 'a')
			sys.stdout = g
##########################
# RACK TABLES FORMATTING #
##########################

			#Initializes Racktables database object, only happens once per object.
			for i in range(len(newEntryRows)):
				try:
					#removes unneeded or unwanted characters, strings have to be formatted specifically for Racktables csv import plugin
					#further info can be found at /var/www/html/plugins/csv_import.php
					deviceName = str(newEntryRows[i][0]).strip()
					objectType = str(newEntryRows[i][8]).upper()
					primaryPool = str((newEntryRows[i][7]).split(",",1)[0].replace('{ppool: "',"").rstrip('"}'))
					namePlusPool = deviceName + "-" + primaryPool
					primaryGroup = str(newEntryRows[i][7]).split(",",1)[1].replace('{pgroup: "',"").replace('"}','')
					deviceType = str(newEntryRows[i][6]).strip()
					rackName=str("r"+newEntryRows[i][1].strip()+"-p"+newEntryRows[i][2]).strip().upper()
					cabinetName = str("C"+newEntryRows[i][3]).strip().upper()
					uRange= str(range((int(newEntryRows[i][4])),(int(newEntryRows[i][5]))+1)).strip("[]").replace(" ","")
					if uRange in allRanges:
						fibs=((len(range((int(newEntryRows[i][4])),(int(newEntryRows[i][5]))))))*"b,"+"b"
					else:
						allRanges.append(uRange)
					#fib indicates the location in the rack(front,inside,back) and has to be listed once for each u the object takes up.
					#checks the uLo and uHigh value from the entry row array, converts them to Ints and plugs them into Range(low,high)
						fibs=((len(range((int(newEntryRows[i][4])),(int(newEntryRows[i][5]))))))*"fib,"+"fib"
					out =("OBJECT;" + objectType+ ";" + namePlusPool + ";" + deviceType +";" + namePlusPool+"\n") + ("RACK;MIT;MGHPCC;"+rackName+";"+cabinetName+";44\n")
					out += ("RACK;MIT;MGHPCC;"+rackName+";"+cabinetName+";44\n")
					out +=("OBJECTATTRIBUTE;"+namePlusPool+";"+"COMMENT;"+ "Primary Pool: " + primaryPool + "     Primary Group: " + primaryGroup+"\n")
					out +=("RACKASSIGNMENT;"+namePlusPool+";"+cabinetName+";"+uRange+";"+fibs+";"+rackName+"\n")
					out += ("OBJECTTAG;"+namePlusPool+";"+primaryPool)
					#removed characters that caused POST/db issues
					out = out.replace(" ","%20")
					out = out.replace("<","(")
					out = out.replace(">",")")
					print out
				except(ValueError):
					pass
			#formats data for changing objects only, 
			for i in range(len(changedItemsAft)):
				try:
					deviceType=str(changedItemsAft[i][6])	
					primaryPool= str(changedItemsAft[i][7]).split(",",1)[0].replace('{ppool: "',"").rstrip('"}')
					primaryGroup = str(changedItemsAft[i][7]).split(",",1)[1].replace('{pgroup: "',"").rstrip('"} ')	
					deviceName=str(changedItemsAft[i][0].strip())
					namePlusPool = deviceName + "-"+ primaryPool
					rackName=str("r"+changedItemsAft[i][1].strip()+"-p"+changedItemsAft[i][2]).strip().upper()
					cabinetName = str("C"+changedItemsAft[i][3]).strip().upper()
					uRange= str(range((int(changedItemsAft[i][4])),(int(changedItemsAft[i][5]))+1)).strip("[]").replace(" ","")
					if uRange in allRanges:
						fibs=((len(range((int(newEntryRows[i][4])),(int(newEntryRows[i][5]))))))*"b,"+"b"
					else:
						allRanges.append(uRange)
						fibs=((len(range((int(newEntryRows[i][4])),(int(newEntryRows[i][5]))))))*"fib,"+"fib"
					#fibs=((len(range((int(changedItemsAft[i][4])),(int(changedItemsAft[i][5]))))))*"fib,"+"fib"
					out = ("RACK;MIT;MGHPCC;"+rackName+";"+cabinetName+";44\n")
					out += ("OBJECTATTRIBUTE;"+namePlusPool+";"+"COMMENT;"+ "Primary Pool:+" + primaryPool + "     Primary Group:+" + primaryGroup+"\n")
					out += ("RACKASSIGNMENT;"+namePlusPool+";"+cabinetName+";"+uRange+";"+fibs+";"+rackName+"\n")
					out += ("OBJECTATTRIBUTE;"+namePlusPool+";"+"LABEL;"+deviceType+"\n")
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

