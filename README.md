# 💇‍♀️ Cabeleila - Sistema de Agendamento para Salão

Um sistema completo de gerenciamento de agendamentos para salões de beleza, construído com **Laravel 13**, **Vue.js 3**, e **PrimeVue**.

![Laravel](https://img.shields.io/badge/Laravel-13.0-red?style=flat-square&logo=laravel)
![Vue](https://img.shields.io/badge/Vue-3.4-green?style=flat-square&logo=vue.js)
![PHP](https://img.shields.io/badge/PHP-8.3+-blue?style=flat-square&logo=php)
![License](https://img.shields.io/badge/License-MIT-yellow?style=flat-square)

## 📋 Sumário

- [Visão Geral](#visão-geral)
- [Tecnologias](#tecnologias)
- [Características](#características)
- [Estrutura do Projeto](#estrutura-do-projeto)
- [Instalação](#instalação)
- [Configuração](#configuração)
- [Uso](#uso)
- [API Routes](#api-routes)
- [Modelos de Dados](#modelos-de-dados)
- [Fluxos Principais](#fluxos-principais)
- [Troubleshooting](#troubleshooting)
- [Licença](#licença)

---

## 🎯 Visão Geral

**Cabeleila** é uma plataforma web de agendamento que permite:

✅ **Clientes** agendarem serviços online sem criar conta  
✅ **Admin (Leila)** gerenciar agendamentos, serviços, horários e clientes  
✅ **Notificações automáticas** via WhatsApp Web (sem API externa necessária)  
✅ **Dashboard analítico** com métricas e gráficos  
✅ **Gerador automático** de padrões de horários mensais  
✅ **Login seguro** para admin e clientes via OTP

O sistema é pensado especificamente para pequenos salões de beleza que desejam oferecer agendamento online simples, direto e intuitivo.

---

## 🚀 Tecnologias

### Backend

- **Laravel 13** - Framework PHP robusto e moderno
- **Laravel Sanctum** - Autenticação segura baseada em tokens
- **PostgreSQL** - Banco de dados relacional
- **Carbon** - Manipulação de datas e horas

### Frontend

- **Vue.js 3** - Framework JavaScript reativo e performático
- **Inertia.js** - Ponte perfeita entre Laravel e Vue (sem API REST)
- **TailwindCSS** - Utility-first CSS framework
- **PrimeVue** - Componentes UI profissionais (DataTable, Dialog, MultiSelect, etc)
- **Vite** - Empacotador rápido com HMR
- **Lucide Vue** - Ícones SVG modernos

### Ferramentas & Dependências

- **PHP 8.3+**
- **Node.js 18+**
- **Composer** - Gerenciador de pacotes PHP
- **npm** - Gerenciador de pacotes JavaScript

---

## ✨ Características

### 🔐 Autenticação & Segurança

- ✅ Login/Registro para administradores com email e senha
- ✅ Autenticação de clientes via **OTP (One-Time Password)** pelo WhatsApp
- ✅ Recuperação de senha segura com token temporário
- ✅ Proteção CSRF e validação de requisições
- ✅ Hashing de senhas com Bcrypt

### 📅 Gerenciamento de Agendamentos

**Para Clientes:**

- Visualizar serviços disponíveis com preços
- Agendar serviços escolhendo data, hora e serviços
- Ver histórico completo de agendamentos pessoais
- Editar agendamentos próprios
- Login sem senha via código OTP (6 dígitos, válido por 10 minutos)

**Para Admin (Leila):**

- Dashboard com estatísticas e gráficos
- Lista completa de agendamentos com filtros avançados
- Buscar por cliente, período ou status
- Criar, editar e deletar agendamentos
- Mudar status (pendente → confirmado → finalizado/cancelado)
- Ordenação por data, cliente, status ou preço total
- Paginação configurável (10, 20 ou 50 itens por página)

### 🕐 Gerenciamento de Horários

- Adicionar horários individualmente por data
- Criar padrão mensal automático (selecionar dias da semana + horários)
- Marcar como disponível/indisponível
- Filtrar por período e status
- Deltar horários não utilizados
- Gerador automático evita duplicatas

### 💇‍♀️ Gerenciamento de Serviços

- CRUD completo de serviços (Create, Read, Update, Delete)
- Campos: nome, descrição, preço e duração em minutos
- Associar múltiplos serviços por agendamento
- Cálculo automático do preço total do agendamento

### 👥 Gerenciamento de Clientes

- Visualizar lista completa de clientes cadastrados
- Editar informações pessoais (nome, telefone, CPF, endereço, etc)
- Ver histórico de agendamentos por cliente
- Cadastro automático ao agendar
- Validação de dados

### 📱 Notificações WhatsApp Web

**Implementação sem API externa** - Usa links `wa.me` nativos:

- ✅ Notificação ao criar agendamento (novo cliente)
- ✅ Notificação ao confirmar agendamento (admin confirma)
- ✅ Notificação ao cancelar agendamento
- ✅ Notificação quando admin remarcar horário
- ✅ Mensagens com formatação WhatsApp (negrito, emoji)
- ✅ Abre WhatsApp Web automaticamente com mensagem pronta

**Vantagem:** Sem necessidade de API (Z-API, Evolution, etc) ou servidor WhatsApp Business

### 📊 Dashboard Analítico

- Gráficos de agendamentos por período
- Receita total e por período
- Distribuição de status dos agendamentos
- Serviços mais populares
- Metas e performance

---

## 📁 Estrutura do Projeto

```
leila-salao/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   ├── AppointmentController.php      # Gerenciar agendamentos
│   │   │   │   ├── AvailabilityController.php     # Gerenciar horários
│   │   │   │   ├── ServiceController.php          # Gerenciar serviços
│   │   │   │   ├── ClientController.php           # Gerenciar clientes
│   │   │   │   └── DashboardController.php        # Dashboard e estatísticas
│   │   │   ├── ClientController.php               # Agendamentos do cliente
│   │   │   └── ProfileController.php              # Perfil do admin
│   │   ├── Middleware/
│   │   │   └── HandleInertiaRequests.php          # Config Inertia/Props globais
│   │   └── Requests/                              # Form Requests (validação)
│   ├── Models/
│   │   ├── Appointment.php                        # Agendamento
│   │   ├── Availability.php                       # Horário disponível
│   │   ├── Client.php                             # Cliente
│   │   ├── Service.php                            # Serviço
│   │   └── User.php                               # Admin/Leila
│   ├── Services/
│   │   └── WhatsAppService.php                    # (Legado - notificações agora no frontend)
│   └── Providers/
│       └── AppServiceProvider.php                 # Configurações da app
│
├── database/
│   ├── migrations/                                # Esquema do banco
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   ├── 0001_01_01_000002_create_jobs_table.php
│   │   ├── 2026_05_01_000001_add_role_to_users_table.php
│   │   ├── 2026_05_01_000002_create_clients_table.php
│   │   ├── 2026_05_01_000003_create_services_table.php
│   │   ├── 2026_05_01_000004_create_availabilities_table.php
│   │   ├── 2026_05_01_000005_create_appointments_table.php
│   │   └── 2026_05_01_000006_create_appointment_service_table.php
│   ├── seeders/
│   │   └── DatabaseSeeder.php                     # Dados iniciais
│   └── factories/                                 # Factories para testes
│       ├── AppointmentFactory.php
│       ├── AvailabilityFactory.php
│       ├── ClientFactory.php
│       ├── ServiceFactory.php
│       └── UserFactory.php
│
├── resources/
│   ├── css/
│   │   └── app.css                                # Estilos globais + Tailwind
│   ├── js/
│   │   ├── app.js                                 # Ponto de entrada Vite
│   │   ├── bootstrap.js                           # Configuração de headers HTTP
│   │   ├── theme.js                               # Tema PrimeVue
│   │   ├── Components/                            # Componentes Vue reutilizáveis
│   │   ├── Layouts/
│   │   │   ├── AuthenticatedLayout.vue            # Layout padrão com navbar
│   │   │   └── GuestLayout.vue                    # Layout sem autenticação
│   │   └── Pages/
│   │       ├── Admin/
│   │       │   ├── Appointments.vue               # Página CRUD de agendamentos
│   │       │   ├── Availabilities.vue             # Página CRUD de horários
│   │       │   ├── Services.vue                   # Página CRUD de serviços
│   │       │   ├── Clients.vue                    # Página CRUD de clientes
│   │       │   └── Dashboard.vue                  # Dashboard com gráficos
│   │       ├── Appointment/
│   │       │   ├── MyAppointments.vue             # Agendamentos do cliente
│   │       │   └── Create.vue                     # Formulário de agendamento
│   │       └── Auth/
│   │           ├── Login.vue
│   │           └── Register.vue
│   └── views/
│       └── app.blade.php                          # Template Blade principal
│
├── routes/
│   ├── web.php                                    # Rotas web principais
│   ├── auth.php                                   # Rotas de autenticação (Breeze)
│   └── console.php                                # Rotas de console/artisan
│
├── config/
│   ├── app.php                                    # Configuração geral da app
│   ├── database.php                               # Configuração do banco de dados
│   ├── auth.php                                   # Guards e providers de autenticação
│   ├── cache.php                                  # Drivers de cache
│   ├── session.php                                # Configuração de sessão
│   └── ...                                        # Outras configurações
│
├── storage/
│   ├── app/                                       # Uploads de arquivos
│   │   ├── public/
│   │   └── private/
│   ├── logs/                                      # Arquivos de log
│   └── framework/                                 # Cache, sessões, views compiladas
│
├── public/
│   ├── index.php                                  # Ponto de entrada da aplicação
│   ├── images/                                    # Imagens estáticas
│   └── build/                                     # Assets compilados (Vite)
│
├── tests/
│   ├── Feature/                                   # Testes de funcionalidade
│   └── Unit/                                      # Testes unitários
│
├── .env.example                                   # Variáveis de ambiente exemplo
├── .gitignore                                     # Arquivos ignorados pelo Git
├── composer.json                                  # Dependências PHP
├── composer.lock                                  # Lock file Composer
├── package.json                                   # Dependências Node
├── package-lock.json                              # Lock file npm
├── phpunit.xml                                    # Configuração de testes
├── tailwind.config.js                             # Configuração TailwindCSS
├── vite.config.js                                 # Configuração Vite
├── artisan                                        # CLI do Laravel
└── README.md                                      # Este arquivo
```

---

## 📦 Instalação

### Pré-requisitos

- **PHP 8.3** ou superior
- **Node.js 18+** e npm
- **Composer**
- **Git**
- **PostgreSQL 12+**

### Passos de Instalação

**1. Clone o repositório:**

```bash
git clone https://github.com/seu-usuario/leila-salao.git
cd leila-salao
```

**2. Instale dependências PHP:**

```bash
composer install
```

**3. Instale dependências Node:**

```bash
npm install
```

**4. Configure o arquivo `.env`:**

```bash
cp .env.example .env
php artisan key:generate
```

**5. Configure o banco de dados:**

Edite `.env` com suas credenciais PostgreSQL:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=leila_salao
DB_USERNAME=postgres
DB_PASSWORD=sua_senha
```

Certifique-se de que o banco de dados `leila_salao` foi criado:

```bash
psql -U postgres -c "CREATE DATABASE leila_salao;"
```

**6. Execute as migrações:**

```bash
php artisan migrate
```

**7. (Opcional) Popule dados de teste:**

```bash
php artisan db:seed
```

**8. Compile os assets:**

```bash
npm run build
```

**9. Inicie a aplicação:**

```bash
php artisan serve
# Em outro terminal:
npm run dev
```

Acesse **`http://localhost:8000`**

---

## ⚙️ Configuração

### Variáveis de Ambiente Importantes (.env)

```env
# Aplicação
APP_NAME=Cabeleila
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Banco de dados
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=leila_salao
DB_USERNAME=postgres
DB_PASSWORD=sua_senha

# WhatsApp
WHATSAPP_ADMIN_NUMBER=(75) 90000-0000

# Session (tempo em minutos)
SESSION_LIFETIME=120

# Cache
CACHE_STORE=database
```

### Primeiro Admin (Leila)

Para criar o primeiro usuário admin, use:

```bash
php artisan tinker
```

Dentro do tinker:

```php
App\Models\User::create([
    'name' => 'Leila',
    'email' => 'leila@salon.com',
    'password' => bcrypt('senha123'),
]);
```

Ou acesse `/register` e crie via formulário.

---

## 🚀 Uso

### Modo Desenvolvimento

**Terminal 1 - Servidor Laravel:**

```bash
php artisan serve
```

**Terminal 2 - Build de Assets (Vite):**

```bash
npm run dev
```

A aplicação estará em: **`http://localhost:8000`**

### Build para Produção

```bash
npm run build
```

Os assets compilados ficarão em `public/build/`

### Comando Automático (composer script)

```bash
composer dev
```

Executa tudo em um único comando (server, queue, logs, Vite, etc)

---

## 🛣️ API Routes

### Autenticação (Public)

```
GET    /                          Página inicial
GET    /login                     Formulário de login
POST   /login                     Fazer login
GET    /register                  Formulário de registro
POST   /register                  Criar novo admin
POST   /logout                    Fazer logout
GET    /forgot-password           Recuperar senha
POST   /forgot-password           Enviar link de recuperação
GET    /reset-password/{token}    Resetar senha
POST   /reset-password            Confirmar nova senha
```

### Agendamentos do Cliente (Public)

```
GET    /agendar                   Página de agendamento
POST   /agendar                   Criar novo agendamento
PUT    /agendar/{id}              Editar agendamento pessoal
GET    /meus-agendamentos         Ver histórico pessoal
POST   /cliente/enviar-otp        Enviar código OTP via WhatsApp
POST   /cliente/login-otp         Fazer login com código OTP
POST   /cliente/logout            Logout do cliente
```

### Admin - Agendamentos (Protegido)

```
GET    /admin/agendamentos                   Listar agendamentos
POST   /admin/agendamentos                   Criar agendamento
PUT    /admin/agendamentos/{id}              Editar agendamento
PATCH  /admin/agendamentos/{id}/status       Mudar status (pending/confirmed/canceled/completed)
```

### Admin - Horários (Protegido)

```
GET    /admin/horarios                       Listar horários
POST   /admin/horarios                       Adicionar horários individuais
POST   /admin/horarios/padrao                Gerar padrão mensal automático
PATCH  /admin/horarios/{id}/status           Marcar como disponível/indisponível
DELETE /admin/horarios/{id}                  Deletar horário
```

### Admin - Serviços (Protegido)

```
GET    /admin/servicos                       Listar serviços
POST   /admin/servicos                       Criar novo serviço
PATCH  /admin/servicos/{id}                  Atualizar serviço
DELETE /admin/servicos/{id}                  Deletar serviço
```

### Admin - Clientes (Protegido)

```
GET    /admin/clientes                       Listar clientes
POST   /admin/clientes                       Criar cliente manual
PATCH  /admin/clientes/{id}                  Editar informações do cliente
```

### Admin - Dashboard (Protegido)

```
GET    /admin/dashboard                      Página do dashboard
GET    /admin/dashboard/data                 Dados para gráficos (JSON)
```

### Perfil do Admin (Protegido)

```
GET    /profile                              Editar perfil pessoal
PATCH  /profile                              Salvar mudanças do perfil
PUT    /profile/password                     Mudar senha
DELETE /profile                              Deletar conta
```

---

## 🗄️ Modelos de Dados

### User (Admin/Leila)

```php
id              bigint
name            string
email           string (unique)
email_verified_at datetime (nullable)
password        string
remember_token  string (nullable)
created_at      timestamp
updated_at      timestamp
```

### Client

```php
id              bigint (primary)
full_name       string
phone           string (unique) - Identificador único
email           string (nullable, unique)
cpf             string (nullable, unique)
birth_date      date (nullable)
postal_code     string (nullable)
street          string (nullable)
number          string (nullable)
complement      string (nullable)
neighborhood    string (nullable)
city            string (nullable)
state           string (nullable)
notes           text (nullable)
otp_code        string (nullable) - Código de 6 dígitos para login
otp_expires_at  datetime (nullable) - Válidade do OTP (10 minutos)
email_verified_at datetime (nullable)
created_at      timestamp
updated_at      timestamp
```

### Service

```php
id              bigint
name            string - Nome do serviço (ex: Corte, Coloração)
description     text (nullable) - Descrição detalhada
price           decimal(10,2) - Preço em R$
duration_minutes integer (nullable) - Duração estimada em minutos
created_at      timestamp
updated_at      timestamp
```

### Availability

```php
id              bigint
date            date - Data do horário
hour            time - Hora do horário (ex: 09:00)
is_available    boolean (default: true) - Disponível ou não
created_at      timestamp
updated_at      timestamp

Índices compostos para performance:
- (date, hour, is_available)
```

### Appointment

```php
id              bigint
client_id       bigint (foreign → clients.id)
availability_id bigint (foreign → availabilities.id)
status          enum('pending', 'confirmed', 'canceled', 'completed')
                - pending: Aguardando confirmação
                - confirmed: Confirmado pelo admin
                - canceled: Cancelado (libera horário)
                - completed: Finalizado
notes           text (nullable) - Anotações internas
created_at      timestamp
updated_at      timestamp
```

### AppointmentService (Pivot Table - Many-to-Many)

```php
id              bigint
appointment_id  bigint (foreign → appointments.id)
service_id      bigint (foreign → services.id)
created_at      timestamp
updated_at      timestamp
```

---

## 🔄 Fluxos Principais

### 1️⃣ Cliente Agendando via Web

```
1. Cliente acessa /agendar
2. Preenche nome e telefone
3. Seleciona data, hora e serviço(s)
4. Clica em "Agendar"
5. POST /agendar cria o agendamento com status "pending"
6. Após sucesso, abre WhatsApp Web automaticamente
7. Mensagem pronta: "🎉 Novo Agendamento! Olá, [Nome]..."
8. Cliente envia manualmente a confirmação (opcional)
```

### 2️⃣ Admin Confirmando Agendamento

```
1. Admin acessa /admin/agendamentos
2. Vê agendamentos com status "pending"
3. Clica em "Confirmar" no agendamento desejado
4. Modal pede confirmação
5. PATCH /admin/agendamentos/{id}/status atualiza status para "confirmed"
6. Horário fica bloqueado (is_available = false)
7. Após sucesso, abre WhatsApp Web automaticamente
8. Mensagem pronta: "✅ Agendamento Confirmado! Seu horário..."
9. Admin envia confirmação ao cliente
```

### 3️⃣ Admin Cancelando Agendamento

```
1. Admin vê agendamento confirmado
2. Clica em "Cancelar"
3. Confirma a ação
4. PATCH /admin/agendamentos/{id}/status muda para "canceled"
5. Horário é liberado automaticamente (is_available = true)
6. Abre WhatsApp Web com mensagem de cancelamento
7. Admin avisa ao cliente sobre o cancelamento
```

### 4️⃣ Cliente Fazendo Login via OTP

```
1. Cliente acessa /meus-agendamentos
2. Preenche nome e telefone
3. Clica em "Enviar Código"
4. POST /cliente/enviar-otp gera código aleatório de 6 dígitos
5. Código é armazenado com validade de 10 minutos
6. (Em desenvolvimento) Log aparece no console
7. (Em produção) Integração com API WhatsApp para enviar SMS
8. Cliente recebe código e preenche no campo
9. POST /cliente/login-otp valida o código
10. Sessão é criada (login sem senha!)
11. Cliente vê seus agendamentos pessoais
```

### 5️⃣ Admin Gerando Padrão de Horários

```
1. Admin acessa /admin/horarios
2. Clica em "Padrão" (botão secundário)
3. Modal abre
4. Seleciona dias da semana (ex: seg-sex, 2-6)
5. Seleciona horários (ex: 09:00, 10:00, 11:00, 14:00, 15:00)
6. Clica em "Gerar padrão"
7. POST /admin/horarios/padrao cria slots para o mês atual
8. Sistema evita duplicatas automaticamente
9. Notificação: "X horários gerados para o mês atual!"
10. Admin vê novos horários na tabela
```

### 6️⃣ Admin Editando Agendamento Existente

```
1. Admin vê agendamento confirmado
2. Clica em "Editar"
3. Modal abre com data, hora e serviços preenchidos
4. Admin muda data, hora e/ou serviços
5. Clica em "Salvar"
6. PUT /admin/agendamentos/{id} atualiza o banco
7. Libera horário antigo (is_available = true)
8. Bloqueia novo horário (is_available = false)
9. Se houve mudanças, abre WhatsApp Web com mensagem de alteração
10. Mensagem: "🔄 Agendamento Alterado! Seu novo horário..."
11. Admin envia notificação ao cliente
```

---

## 🎨 Estilos & Componentes

### TailwindCSS + PrimeVue

- Cores principais: Verde (#547558) e tons neutros
- Componentes: DataTable, Dialog, Button, InputText, MultiSelect, Dropdown, Tag, etc
- Responsivo: Mobile-first com breakpoints sm, md, lg, xl
- Dark mode: Suportado (TailwindCSS)

### Fontes

- Sistema: Sans-serif padrão
- Ícones: Lucide Vue Icons (ícones SVG modernos)

---

## 🧪 Testes

Execute os testes:

```bash
php artisan test
```

Testes específicos:

```bash
php artisan test --filter=AppointmentTest
php artisan test tests/Feature/Auth/
```

---

## 📝 Notas Importantes

### 🌐 WhatsApp Web (Sem API)

**Decisão de Design:** O sistema usa links `wa.me` nativos do WhatsApp Web, não API externa.

**Vantagens:**

- ✅ Sem custo (sem Z-API, Evolution, etc)
- ✅ Sem dependência de servidor externo
- ✅ Funciona em produção instantaneamente
- ✅ Suporta emojis e formatação

**Fluxo:**

1. Admin/Cliente clica no botão de notificação
2. Navegador abre WhatsApp Web (ou app mobile)
3. Mensagem vem pré-prenchida
4. User envia manualmente

**URL Format:**

```
https://wa.me/55{numero_sem_formatacao}?text={mensagem_url_encoded}
```

### 🔐 OTP (One-Time Password)

- Código de **6 dígitos** gerado aleatoriamente
- Válido por **10 minutos**
- Armazenado em `clients.otp_code` e `clients.otp_expires_at`
- Atualmente logado no console (em desenvolvimento)
- **Integração opcional:** API WhatsApp Business para enviar SMS automaticamente

### 📊 Status de Agendamento

| Status      | Significado            | Ação                       |
| ----------- | ---------------------- | -------------------------- |
| `pending`   | Aguardando confirmação | Admin confirma             |
| `confirmed` | Confirmado             | Admin pode editar/cancelar |
| `canceled`  | Cancelado              | Horário é liberado         |
| `completed` | Finalizado             | Sem ações possíveis        |

### 🔐 Autenticação

| Tipo        | Método          | Segurança                  |
| ----------- | --------------- | -------------------------- |
| **Admin**   | Email + Senha   | Bcrypt + Sanctum           |
| **Cliente** | OTP (6 dígitos) | Código temporário (10 min) |

### 📱 Responsividade

- ✅ Desktop (1200px+)
- ✅ Tablet (768px - 1199px)
- ✅ Mobile (< 768px)

---

## 🐛 Troubleshooting

### Erro ao migrar banco

```bash
# Reseta e recria tudo
php artisan migrate:fresh

# Popula dados iniciais
php artisan db:seed
```

### Erro ao compilar assets

```bash
# Limpa node_modules e reinstala
rm -rf node_modules package-lock.json
npm install
npm run build
```

### Sessão expirando muito rápido

Aumente `SESSION_LIFETIME` em `.env`:

```env
SESSION_LIFETIME=43200  # 30 dias em minutos
```

### Erro "No application encryption key"

```bash
php artisan key:generate
```

### Erro de permissão em storage

```bash
chmod -R 775 storage bootstrap/cache
```

### Erro de conexão PostgreSQL

Verifique se o PostgreSQL está rodando e as credenciais estão corretas:

```bash
# Testar conexão
psql -U postgres -h 127.0.0.1 -d leila_salao

# Se o banco não existe, criar
psql -U postgres -c "CREATE DATABASE leila_salao;"
```

---

## 📚 Recursos Úteis

- [Laravel Documentation](https://laravel.com/docs)
- [Vue.js Guide](https://vuejs.org/guide/)
- [Inertia.js](https://inertiajs.com/)
- [PrimeVue Components](https://primevue.org/)
- [TailwindCSS](https://tailwindcss.com/)

---

## 📄 Licença

Este projeto é licenciado sob a **MIT License**.

---

## 👥 Autor

Desenvolvido para **Leila's Salon** ✂️

---

## 📧 Suporte & Contato

Para dúvidas, sugestões ou problemas, entre em contato:

- **WhatsApp:** (75) 90000-0000
- **Email:** leila@cabeleila.com.br

---

**Status do Projeto:** ✅ Em Desenvolvimento  
**Última Atualização:** 7 de maio de 2026  
**Versão:** 1.0.0
