#!/bin/bash

DEFAULT_LOCALE="en_US"
LOCALE_TO_CHECK=$1
BIBORB_PO="LC_MESSAGES/biborb.po"

rm -f msgids missing duplicated

grep msgid  ../locale/${DEFAULT_LOCALE}/${BIBORB_PO} > msgids
grep msgid  ../locale/${LOCALE_TO_CHECK}/${BIBORB_PO} >> msgids
sort msgids | sed -e 's/  */ /g' | uniq -u  > missing

if [ -s missing ]; then
    echo "The following entries are missing in ../locale/${LOCALE_TO_CHECK}/${BIBORB_PO}"
    echo "-----------------------------------------------------------------------------"
    cat missing
    echo "-----------------------------------------------------------------------------"
    FAILED=0
fi
exit
grep msgid  ../locale/${LOCALE_TO_CHECK}/${BIBORB_PO} > msgids
sort msgids | uniq -d | uniq > duplicated

if [ -s duplicated ]; then
    echo "The following entries are duplicated in ../locale/${LOCALE_TO_CHECK}/${BIBORB_PO}"
    echo "-----------------------------------------------------------------------------"
    cat duplicated
    echo "-----------------------------------------------------------------------------"
    FAILED=0
fi


if [ -n FAILED ]; then
    echo "Nothing weird"
fi
