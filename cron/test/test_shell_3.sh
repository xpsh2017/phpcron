#! /bin/sh
# 每10分钟执行一次

echo 'start at: ' +`date`
cd /tmp
mkdir shell_test
cd shell_test

for ((i=0; i<10; i++)); do
    touch test_$i.txt
done
echo 'end at: ' +`date`
