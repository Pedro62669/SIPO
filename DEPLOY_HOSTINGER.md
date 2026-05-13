# Implantação na Hostinger

Guia para publicar o SIPO em `https://sipo.pmsgra.net` usando hospedagem compartilhada/cPanel.

## Requisitos

- PHP 8.3 ou superior no domínio/subdomínio.
- Extensões PHP comuns do Laravel, Excel e PDF: `ctype`, `curl`, `dom`, `fileinfo`, `filter`, `gd`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `zip`.
- Banco MySQL criado na Hostinger.
- Permissão de escrita em `storage/` e `bootstrap/cache/`.

## Arquivos para enviar

Envie o projeto sem os itens listados em `.deployignore`.

Obrigatórios no servidor:

- `app/`, `bootstrap/`, `config/`, `database/`, `lang/`, `public/`, `resources/`, `routes/`, `storage/`
- `artisan`, `composer.json`, `composer.lock`, `index.php`, `.htaccess`
- `vendor/`, caso não vá rodar Composer pelo SSH da Hostinger
- `public/build/`, gerado por `npm run build`

Não envie:

- `.env` local, `.git/`, `.cursor/`, `node_modules/`, `tests/`
- `Documentos/`, `Documentos 2025/`
- logs, caches, sessões locais e banco SQLite local

## `.env` de produção

Crie o `.env` diretamente no servidor com valores reais:

```dotenv
APP_NAME=SIPO
APP_ENV=production
APP_KEY=base64:GERAR_NO_SERVIDOR
APP_DEBUG=false
APP_URL=https://sipo.pmsgra.net
APP_URL_PATH_PREFIX=
APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=pt_BR
APP_FAKER_LOCALE=pt_BR

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=NOME_DO_BANCO
DB_USERNAME=USUARIO_DO_BANCO
DB_PASSWORD=SENHA_DO_BANCO

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_DOMAIN=null

CACHE_STORE=database
QUEUE_CONNECTION=sync

MAIL_MAILER=log
MAIL_FROM_ADDRESS="no-reply@sipo.pmsgra.net"
MAIL_FROM_NAME="${APP_NAME}"
```

Use `QUEUE_CONNECTION=database` somente se houver worker ou cron configurado para processar filas.

## Comandos de preparação

Na máquina local:

```bash
npm run build
composer install --no-dev --optimize-autoloader
```

No servidor, se houver SSH:

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate --force
php artisan migrate --force
php artisan config:cache
php artisan view:cache
```

Não use `php artisan route:cache` neste projeto enquanto houver rotas definidas com closures em `routes/web.php`.
Se ele tiver sido executado no servidor, limpe com:

```bash
php artisan route:clear
php artisan optimize:clear
```

Se não houver SSH, gere `vendor/` e `public/build/` localmente e envie esses diretórios prontos.

## Raiz de documentos

O ideal é apontar o subdomínio para a pasta `public/`. Se a Hostinger não permitir e o subdomínio apontar para a raiz do projeto, o arquivo `.htaccess` da raiz bloqueia acesso direto a diretórios internos e encaminha as rotas para `public/index.php`.

## Pós-implantação

- Acesse `https://sipo.pmsgra.net/up` para verificar saúde básica do Laravel.
- Acesse a tela de login.
- Verifique se `storage/logs/laravel.log` não tem erro de permissão.
- Teste importação de Excel e geração de PDF com arquivos pequenos antes de usar arquivos grandes.
