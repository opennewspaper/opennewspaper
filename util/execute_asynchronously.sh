#!/bin/sh

object_file=$1
method=$2
args=$3

nohup php execute_asynchronously.php $1 $2 $3 &
