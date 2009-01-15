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
	mv -i "${file}".bak "${file}"
}

function add_file() {
	cat "$1" > "$1".bak
	cat "$2" >> "$1".bak
	tail -n 20 "$1".bak
	mv -i "$1".bak "$1"
}
set +xv
replace "tca.php" \
		"require_once(PATH_typo3conf . 'ext/newspaper/tca_addon.php');" \
		'?>'

replace "ext_tables.php" \
		"require_once(PATH_typo3conf . 'ext/newspaper/ext_tables_addon.php');" \
		'?>'

add_file "ext_tables.sql" "util/ext_tables_addon.sql"
