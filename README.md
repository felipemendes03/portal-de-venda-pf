# Portal de Vendas Pioneiros da Fé
Sistema de vendas para sociais do clube pioneiros da Fé


![php](https://img.shields.io/static/v1?label=php&message=8.2&color=blue)
![guzzlehttp](https://img.shields.io/static/v1?label=guzzlehttp&message=7.8&color=purple)
![laravel](https://img.shields.io/static/v1?label=laravel&message=11.0&color=red)
![composer](https://img.shields.io/static/v1?label=composer&message=2.7.2&color=orange)
![mysql](https://img.shields.io/static/v1?label=mysql&message=8.0&color=green)
![nodejs](https://img.shields.io/badge/nodejs-v20.13-238812)



## Preparando ambiente de desenvolvimento

1. Suba a infra estrutura em docker
    ``` shell
    docker-compose -f ./docker/docker-composer.yaml up -d
    ```

## Instalando o projeto

1. Copie o .env.example e remomeie ele para .env
    ``` shell
    cp .env.example .env
    ```
2. Ajustes as variáveis de ambiente no .env
3. Rode o composer
    ``` shell
    composer install
    ```
4. Gere a Key do da aplicação
    ``` shell
    php artisan key:generate
    ```
5. Prepare a base de dados
    ``` shell
    php artisan migrate
    ```
6. Suba o projeto com o Artisan
    ``` shell
    php artisan serve
    ```
7. Suba o front-end
    ``` shell
    npm i
    npm run dev
    ```
7. Acesse a aplicação  
    http://localhost:8000
    