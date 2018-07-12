#!/bin/bash

step=1 #间隔的秒数，不能大于60

for (( i = 0; i < 60; i=(i+step) )); do
    /data/htdocs/huogou/shell/pk_lottery_win.sh &
    sleep $step
done

exit 0