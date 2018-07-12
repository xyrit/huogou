#!/bin/bash

name=$1
if [ "$name" ];
then
    nohup /data/htdocs/huogou/shell/$name >> /dev/null 2>&1 &
fi