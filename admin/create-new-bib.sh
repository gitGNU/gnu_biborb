#!/bin/sh
#
#Copyright (C) 2003  Guillaume Gardey
#
#This program is free software; you can redistribute it and/or
#modify it under the terms of the GNU General Public License
#as published by the Free Software Foundation; either version 2
#of the License, or any later version.
#
#This program is distributed in the hope that it will be useful,
#but WITHOUT ANY WARRANTY; without even the implied warranty of
#MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#GNU General Public License for more details.

# Tool to create a new bib repository
#   * creates a new bibname repertory
#   * creates the empty xml file
#   * change access mode of the xml file
#   * create description.txt


bibrep="../bibs/";

echo -n "Name of the new bib to create: ";
read bibname;
echo -n "Short Description: ";
read desc;

if [ -x $bibrep$bibname ]
then
    echo "Error: Bibliography $bibname already exists!";
else
    echo "Creating repertory $bibrep$bibname...";
    mkdir $bibrep$bibname;
    if [ $? != 0 ]
    then
	echo "Aborting...";
	exit;
    fi;

    echo "Creating papers repository $bibrep$bibname/papers";
    mkdir $bibrep$bibname"/papers";
    if [ $? != 0 ]
    then
	echo "Aborting...";
	exit;
    fi;

    echo "Creating $bibrep$bibname/$bibname.bib...";
    touch $bibrep$bibname"/"$bibname".bib";
    echo "Chmod a+w $bibrep$bibname/$bibname.bib";
    chmod a+w $bibrep$bibname"/"$bibname".bib";
    if [ $? != 0 ]
    then
	echo "Aborting...";
	exit;
    fi;

    echo "Creating $bibrep$bibname/$bibname.xml...";
    touch $bibrep$bibname"/"$bibname".xml";
    if [ $? != 0 ]
    then
	echo "Aborting...";
	exit;
    fi;
    echo "<?xml version='1.0' encoding='iso-8859-1'?>" > $bibrep$bibname"/"$bibname".xml";
    echo "<bibtex:file xmlns:bibtex='http://bibtexml.sf.net/' name='"$bibname"'>">>$bibrep$bibname"/"$bibname".xml";
    echo "</bibtex:file>" >> $bibrep$bibname"/"$bibname".xml";
    echo "Chmod a+w $bibrep$bibname/$bibname.xml";
    chmod a+w $bibrep$bibname"/"$bibname".xml";
    if [ $? != 0 ]
    then
	echo "Aborting...";
	exit;
    fi;
    echo "Creating $bibrep$bibname/description.txt";
    touch $bibrep$bibname"/description.txt";
    if [ $? != 0 ]
    then
	echo "Aborting...";
	exit;
    fi;
    echo "Fill $bibrep$bibname/description.txt with $desc";
    echo $desc > $bibrep$bibname"/description.txt";
fi;

	
