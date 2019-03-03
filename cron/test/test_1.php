<?php
/**
 * 模拟循环处理
 * cron表达式: 0 * * * *
 */
$maxLoopCount = 1000;
echo basename(__FILE__). ' start at :'. date('Y-m-d H:i:s')."\n";
for ($i=0; $i < $maxLoopCount; $i++) {
    echo ($i + 1)." Loop"."\n";
    sleep(1);
}
echo basename(__FILE__). ' end at :'. date('Y-m-d H:i:s')."\n";