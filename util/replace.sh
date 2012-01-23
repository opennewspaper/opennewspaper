#!/bin/bash

# adds pre-defined lines to files which have been edited in the kickstarter
# assumes these lines come last in the file, so beware!

function replace() {
	file="$1"
	to_add="$2"
	close="${3}"
	
	# exclude the line which is to add and the optional closing brace (in PHP files)
	cat ${file} | grep -v "${to_add}" | grep -v ${close} > "${file}".bak
	echo ${to_add} >> "${file}".bak
	echo $close >> "${file}".bak

	tail -n 5 "${file}".bak
	mv  "${file}".bak "${file}"
}

function add_file() {
	cat "$1" > "$1".bak
	cat "$2" >> "$1".bak
	tail -n 20 "$1".bak
	mv  "$1".bak "$1"
}

function remove_line() {
	file="$1"
	remove_lines_with_string="$2"
	# remove lines containing search string
	cat ${file} | grep -v "${remove_lines_with_string}" > "${file}".bak
	mv  "${file}".bak "${file}"	
}

function change_main_module() {
  for file in ./mod*/conf.php
  do
    # switch main module for modules to 'newspaper' main module
    sed -i 's/web_/txnewspaperMmain_/g' $file
  done
}	


set +xv
replace "tca.php" \
		"require_once(PATH_typo3conf . 'ext/newspaper/tca_addon.php');" \
		'?>'

replace "ext_tables.php" \
		"require_once(PATH_typo3conf . 'ext/newspaper/ext_tables_addon.php');" \
		'?>'

replace "ext_localconf.php" \
		"require_once(PATH_typo3conf . 'ext/newspaper/ext_localconf_addon.php');" \
		'?>'

add_file "ext_tables.sql" "util/ext_tables_addon.sql"

# modules are added in ext_tables_addon.php
remove_line "ext_tables.php" \
		"t3lib_extMgm::addModule("
remove_line "ext_tables.php" \
		"t3lib_extMgm::addTCAColumns("

change_main_module

replace "tca.php" \
		"require_once(PATH_typo3conf . 'ext/newspaper/tca_addon.php');" \
		'?>'

echo "Enter commit message:"
read commit_message
svn ci -m "$commit_message"

#for addon in ../newspaper_?*; do 
#	if [ -x ${addon}/util/replace.sh ]; then
#		cd ${addon}
#		./util/replace.sh
#		cd ../newspaper
#	fi
#done

echo "###"
echo "###"
echo "### Don't forget to open the extension in the ExtMgr and make the SQL alterations\!"
echo "###"
echo "###"

