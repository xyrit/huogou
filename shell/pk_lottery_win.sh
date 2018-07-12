#!/bin/bash

countNum=$(ps -ef|grep -v grep|grep pk-lottery/win|wc -l)
if [ $countNum -lt 1 ]
then
    /data/htdocs/huogou/yii pk-lottery/win >> /tmp/pk.lottery.win.log &
fi