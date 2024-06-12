#!/bin/bash

if grep -q "APP_ENV=production" .env; then
    npm install
    npm run build
    zip -r build.zip .
    mkdir -p $PW$PWD/temp
    sshpass -p $sshpass scp -P 2222 $server:$pathOnServer/.env $PWD/temp/.env
    sshpass -p $sshpass scp -P 2222 $server:$pathOnServer/.htaccess $PWD/temp/.htaccess
    sshpass -p $sshpass ssh -P 2222 $server "rm -rf $pathOnServer/*"
    sshpass -p $sshpass scp -P 2222 build.zip $server:$pathOnServer
    sshpass -p $sshpass ssh -P 2222 $server "cd $pathOnServer && unzip -o build.zip && rm build.zip"
    sshpass -p $sshpass scp -P 2222 $PWD/temp/.env $server:$pathOnServer
    sshpass -p $sshpass scp -P 2222 $PWD/temp/.htaccess $server:$pathOnServer
    sshpass -p $sshpass ssh -P 2222 $server "cd $pathOnServer && php artisan migrate --force"
    rm -rf $PWD/temp
    rm build.zip
else
    echo "Erro: APP_ENV=production não encontrado no arquivo .env"
fi