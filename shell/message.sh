#!/bin/bash

countNum=$(ps -ef|grep -v grep|grep /data/htdocs/huogou/shell/queue.sh\ message1|wc -l)
if [ $countNum -lt 1 ]
then
    nohup /data/htdocs/huogou/shell/queue.sh message1 &
fi

countNum=$(ps -ef|grep -v grep|grep /data/htdocs/huogou/shell/queue.sh\ message2|wc -l)
if [ $countNum -lt 1 ]
then
    nohup /data/htdocs/huogou/shell/queue.sh message2 &
fi

countNum=$(ps -ef|grep -v grep|grep /data/htdocs/huogou/shell/queue.sh\ message3|wc -l)
if [ $countNum -lt 1 ]
then
    nohup /data/htdocs/huogou/shell/queue.sh message3 &
fi

countNum=$(ps -ef|grep -v grep|grep /data/htdocs/huogou/shell/queue.sh\ message4|wc -l)
if [ $countNum -lt 1 ]
then
    nohup /data/htdocs/huogou/shell/queue.sh message4 &
fi