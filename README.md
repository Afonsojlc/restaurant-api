# 🍽️ Restaurante API

Uma API RESTful desenvolvida em **Laravel 11** para a gestão completa de um restaurante.

Este projeto fornece um sistema robusto para consulta de menu, gestão de pratos por categorias e tratamento do ciclo de vida de reservas de mesa.

A arquitetura foi desenhada com três níveis de acesso que refletem um caso de uso real num ambiente de restauração.

---

# ✨ Funcionalidades Principais

O sistema utiliza autorização granular por papel em cada rota, garantindo a segurança e privacidade dos dados através da divisão em 3 níveis de acesso:

## 👨‍🍳 Proprietário (Admin)

- Tem acesso total ao sistema.
- Pode criar, editar e apagar categorias e pratos.
- Pode visualizar todas as reservas de todos os clientes.
- É o único responsável por confirmar ou rejeitar as reservas pendentes.

## 👤 Cliente (Autenticado)

- Pode gerir o seu próprio perfil (nome, email, password, telefone).
- Pode criar novas reservas associando múltiplos pratos e respetivas quantidades.
- Pode visualizar, editar e cancelar exclusivamente as suas próprias reservas.
- A edição só é permitida enquanto a reserva estiver pendente.

## 🌐 Visitante (Não Autenticado)

- Pode consultar as categorias ativas e o menu completo.
- Pode aplicar filtros de pesquisa avançados nos pratos por disponibilidade, preço máximo e categoria.

---

# 🏗️ Arquitetura e Base de Dados

O projeto explora conceitos avançados de bases de dados relacionais:

- **Base de Dados:** SQLite (configuração local sem servidor).
- **Autenticação:** Laravel Sanctum para gestão segura baseada em tokens (JWT).

## Relações (Eloquent ORM)

| Tipo | Descrição |
|---|---|
| 1:N | Um utilizador pode ter várias reservas |
| 1:N | Uma categoria pode ter vários pratos |
| N:N | Relação muitos-para-muitos através da tabela pivot `reservation_dish`, permitindo que uma reserva inclua múltiplos pratos com quantidades específicas |

## Ciclo de Vida das Reservas

```text
pending -> confirmed -> cancelled
```

---

# 🛠️ Como Instalar e Executar

Siga estes passos para correr o projeto localmente na sua máquina.

## 1. Clonar o Repositório

```bash
git clone https://github.com/Afonsojlc/Restaurante-API
cd Restaurante-API
```

## 2. Instalar as Dependências do PHP

```bash
composer install
```

## 3. Configurar as Variáveis de Ambiente

Duplique o ficheiro de exemplo e crie o ficheiro `.env`:

```bash
cp .env.example .env
```

No ficheiro `.env`, configure a ligação SQLite:

```env
DB_CONNECTION=sqlite
```

## 4. Gerar a Chave da Aplicação

```bash
php artisan key:generate
```

## 5. Preparar a Base de Dados

Crie o ficheiro SQLite:

```bash
touch database/database.sqlite
```

Execute as migrações e os seeders:

```bash
php artisan migrate:fresh --seed
```

## 6. Iniciar o Servidor

```bash
php artisan serve
```

A API ficará disponível em:

```text
http://127.0.0.1:8000/api
```

---

# 🧪 Testes com Postman

O repositório está preparado para testes "plug & play".

## Passos

1. Importar o ficheiro `Restaurante API.postman_collection.json`.
2. Importar o ficheiro `Restaurante API.postman_environment.json`.
3. Selecionar o ambiente **Restaurante API** no Postman.

Os pedidos estão organizados em pastas:

- Utilizador
- Menu
- Reservas

A variável `{{token}}` é preenchida automaticamente após um login com sucesso.

---

# 🔑 Credenciais de Teste (Seeders)

## Conta de Proprietário (Admin)

| Campo | Valor |
|---|---|
| Email | admin@restaurante.pt |
| Password | password123 |

## Conta de Cliente

| Campo | Valor |
|---|---|
| Email | maria@email.com |
| Password | password123 |

---

# 📖 Documentação da API (Endpoints)

## 👤 Autenticação e Perfil

| Método | Endpoint | Acesso | Descrição |
|---|---|---|---|
| POST | `/api/register` | Público | Criar conta de cliente |
| POST | `/api/login` | Público | Login devolve token Sanctum |
| GET | `/api/me` | Cliente | Ver o meu perfil |
| PUT | `/api/me` | Cliente | Atualizar dados pessoais e password |
| POST | `/api/logout` | Cliente | Invalidar token atual |

---

## 🍔 Menu (Categorias e Pratos)

| Método | Endpoint | Acesso | Descrição |
|---|---|---|---|
| GET | `/api/categories` | Público | Listar categorias ativas |
| GET | `/api/categories/{id}` | Público | Detalhe da categoria + pratos disponíveis |
| POST | `/api/categories` | Proprietário | Criar categoria |
| PUT | `/api/categories/{id}` | Proprietário | Editar categoria |
| DELETE | `/api/categories/{id}` | Proprietário | Apagar categoria |
| GET | `/api/dishes` | Público | Listar pratos (filtros: `category_id`, `available`, `max_price`) |
| GET | `/api/dishes/{id}` | Público | Detalhes de um prato |
| POST | `/api/dishes` | Proprietário | Criar prato |
| PUT | `/api/dishes/{id}` | Proprietário | Editar prato |
| DELETE | `/api/dishes/{id}` | Proprietário | Apagar prato |

---

## 📅 Reservas

| Método | Endpoint | Acesso | Descrição |
|---|---|---|---|
| GET | `/api/reservations` | Cliente | Listar reservas (cliente: só as suas; admin: todas + filtro status) |
| POST | `/api/reservations` | Cliente | Criar reserva com pratos opcionais |
| GET | `/api/reservations/{id}` | Cliente | Ver detalhes de uma reserva |
| PUT | `/api/reservations/{id}` | Cliente | Editar reserva (só se `pending`) |
| PATCH | `/api/reservations/{id}/cancel` | Cliente | Cancelar a própria reserva |
| PATCH | `/api/reservations/{id}/status` | Proprietário | Confirmar ou rejeitar reserva |
| DELETE | `/api/reservations/{id}` | Proprietário | Eliminar reserva |
