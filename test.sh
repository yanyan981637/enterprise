# if [ $# -ne 1 ]; then
###
 # @Date: 2023-12-29 14:04:18
 # @LastEditors: arvin
 # @LastEditTime: 2024-12-23 14:29:39
 # @FilePath: /test.sh
 # @Description: 
### 

#  echo "$0 自動化編譯使用方式： 請輸入<資料夾名稱>"
#  exit 0
# fi

# if [ -d "/var/html/$1" ]; then
#     echo "資料夾 /var/html/$1/ 存在."
#     echo "------------------------------------------------------------------"
# else
#     echo "資料夾 /var/html/$1/ 不存在"
#     exit 0
# fi

echo "編譯程序開始"
echo "------------------------------------------------------------------"

# sudo php bin/magento maintenance:enable

echo "開啟維護模式"
echo "------------------------------------------------------------------"

rm -rf /data/enterprise/generated/*
rm -rf /data/enterprise/pub/static/*
rm -rf /data/enterprise/var/view_preprocessed/*
rm -rf /data/enterprise/var/cache/*
rm -rf /data/enterprise/var/page_cache/*

# echo "暫存文件已經刪除"
# echo "------------------------------------------------------------------"
# echo "開始資料庫編譯"

php bin/magento setup:upgrade

echo "資料庫編譯完成"
echo ""------------------------------------------------------------------""
echo "開始模組編譯"

php bin/magento setup:di:compile

echo "模組編譯完成"
echo ""------------------------------------------------------------------""
echo "開始靜態檔部屬"

php bin/magento setup:static-content:deploy -f

echo "靜態檔部屬完成"
echo ""------------------------------------------------------------------""
# echo "開始刪除索引"

# sudo php bin/magento indexer:reset

# echo "刪除索引完成"
# echo ""------------------------------------------------------------------""
# echo "開始建立索引"

sudo php bin/magento indexer:reindex

# echo "建立索引完成"
echo ""------------------------------------------------------------------""
echo "開始清除快取"

php bin/magento cache:clean
php bin/magento cache:flush

echo "清除快取結束"
echo "------------------------------------------------------------------"

# sudo php bin/magento maintenance:disable
bin/magento deploy:mode:set developer

echo "關維護模式"
echo "------------------------------------------------------------------"

chown www-data:www-data -R  /data/enterprise
chmod -R 777 vendor/ pub/ var/ generated/
# chmod 777 -R /data/enterprise

echo "權限授權完成"
echo ""------------------------------------------------------------------""
exit 0