#!/bin/bash

#
# dump all data belonging to the extension 'tx_newspaper' for a consistent 
# testing environment
#
# usage:
# adjust configuration below
# $ cd <tx_newspaper base dir>
# $ ./util/dump_testdata.sh
# $ svn ci tests -m "dumped test data"
#

#
# configuration section
#

# DB user for mysqldump - should stay the same
db_user=onlinetaz
# DB password for mysqldump - should stay the same
db_password=axtimwalde
# DB to dump - adjust on a per-user basis
db=onlinetaz_2_hel
# change this if you want to call the script from somewhere else than the
# tx_newspaper base dir. trailing '/' is required.
path_prefix=
# subdirectory in which to store the dump. should stay the same.
tests_dir=tests
# filename for the dump. should stay the same.
dump_filename=testdata_newspaper.sql

#
# action section (hey, that rhymes!)
#

tables=''
for i in $(echo show tables\; | \
	   mysql -u ${db_user} --password=${db_password} ${db} | \
	   grep tx_newspaper); do 
	tables="$tables $i"
done

mysqldump -u ${db_user} --password=${db_password} ${db} ${tables} > \
	${path_prefix}${tests_dir}/${dump_filename}

# that's all folks. actually a one liner if you call it from the shell, but i
# wanted it to be as transparent as possible :-)
