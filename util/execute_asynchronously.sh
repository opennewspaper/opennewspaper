#!/bin/sh

# "set -xv" for debugging, "set +xv" for none
set +xv

object_file="$1"
method="$2"
args="$3"

basedir="$(dirname "$0")"

# echo nohup php "${basedir}"/execute_asynchronously.php "$1" "$2" "$3" >> /tmp/execute_asynchronously.log

nohup php "${basedir}"/execute_asynchronously.php "$1" "$2" "$3" &
