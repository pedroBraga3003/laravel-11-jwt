# Laravel 11 + JWT + Docker

Este projeto eh um exemplo de API em Laravel 11 com autenticacao JWT, utilizando ambiente Docker baseado em Ubuntu 22.04, PHP 8.2, Apache e MySQL.

---

##  Pre-requisitos

- PHP 8.2+
- Composer
- Docker e Docker Compose
- Git

---

##  Instruções de Instalação

### 1. Clone o repositório

```bash
git clone https://github.com/pedroBraga3003/laravel-11-jwt.git
```

Exemplo de local para clonar:

- Windows: `C:\xampp\htdocs\laravel-11-jwt`
- Linux: `/var/www/html/laravel-11-jwt`

---

### 2. Instale as dependências

```bash
composer install
```

---

### 3. Configure o arquivo `.env`

Crie ou edite o arquivo `.env` com as configurações do banco de dados:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_onfly
DB_USERNAME=root
DB_PASSWORD=B@tera30
```

---

### 4. Inicie o container Docker

```bash
docker run -it -v /var/www/html/laravel-11-jwt:/var/www/html/laravel-11-jwt -d -p 8080:80 devpedro3003/ubuntu-22.04-laravel
```

---

### 5. Pegue o ID do container

```bash
docker container ps -a
```

Exemplo de saida:

```
CONTAINER ID   IMAGE                               COMMAND       CREATED          STATUS          PORTS                                   NAMES
8aed98564137   devpedro3003/ubuntu-22.04-laravel   "/bin/bash"   16 seconds ago   Up 10 seconds   0.0.0.0:8080->80/tcp, :::8080->80/tcp   charming_lalande
```

---

### 6. Acesse o container

```bash
docker exec -it 8aed98564137 bash
```

---

### 7. Inicie os servicos dentro do container

```bash
sh /home/start.sh
```

---

### 8. Navegue ate a pasta do projeto

```bash
cd /var/www/html/laravel-11-jwt
```

---

### 9. Prepare o ambiente Laravel

```bash
php artisan migrate
php artisan cache:clear
php artisan route:clear
php artisan config:clear
php artisan config:cache
php artisan view:clear
php artisan jwt:secret
```

---

### 10. Execute os testes automatizados

```bash
php artisan test
```

---

### 11. Saia do container

```bash
exit
```

---

### 12. Pare o container

```bash
docker stop 8aed98564137
```

---

### 13. Remova o container (opcional)

```bash
docker rm 8aed98564137
```

---

##  Observacoes

- A API estara acessivel em: [http://localhost:8080](http://localhost:8080)
- Lembre-se de atualizar o ID do container conforme a saída real do seu `docker container ps`.
- Os arquivos para importar os endpoints no postman estão na pasta 'docs\PostMan'.

---

##  Autor

**Pedro Braga**  
[https://github.com/pedroBraga3003](https://github.com/pedroBraga3003)
