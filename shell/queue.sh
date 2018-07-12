#!/bin/bash

name=$1
if [ "$name" ];
then
    while [ TRUE ]
    do
        /usr/bin/php /data/htdocs/huogou/yii queue/dequeue $name 10 >> /dev/null 2>&1 &
        sleep 1
    done
fi