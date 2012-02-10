#!/bin/sh

object_file=$1
method=$2
args=$3

basedir="$(dirname "$0")"
echo nohup php execute_asynchronously.php "$1" "$2" "$3" >> /tmp/execute_asynchronously.log

# nohup php execute_asynchronously.php "$1" "$2" "$3" &

php "$basedir"/execute_asynchronously.php "$1" "$2" "$3"
