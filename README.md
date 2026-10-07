# API de Sistema de Hotelaria - Desafio Técnico Foco Multimídia

**Desenvolvido por:** Tamara Lécia  
**GitHub:** [://github.com](https://github.com/TamaraLecia)

Este projeto consiste em uma solução de API RESTful robusta desenvolvida em Laravel para o gerenciamento de redes hoteleiras, cobrindo o ciclo completo de importação de dados, gestão de disponibilidade de quartos e gerenciamento de reservas.

A arquitetura foi desenhada seguindo rigorosos padrões de mercado, aplicando conceitos de *Clean Code*, desacoplamento em camadas de serviço e aplicação de testes.

## Tecnologias e Diferenciais Implementados

O projeto atende os requisitos obrigatórios e engloba os seguintes diferenciais avançados solicitados:

- **Service Layer Pattern:** Lógica de negócios isolada em camadas de serviço (`ReserveService`), mantendo os controladores enxutos.
- **Documentação Viva com Swagger (OpenAPI 3.0.0):** Mapeamento de endpoints interativos utilizando a sintaxe moderna de **PHP 8+ Attributes**.
- **Testes Automatizados (PHPUnit):** Suíte automatizada com 9 cenários de testes funcionais utilizando banco em memória temporária (`RefreshDatabase`).
- **Engenharia de Logs:** Monitoramento estruturado da aplicação em estágios cruciais de auditoria (`Log::info`, `Log::warning`, `Log::error`).
- **Regras de Negócio Hoteleiras:** 
  - Validação e aplicação automática de cupom de desconto (ex: `CUPOM20`).
  - Cálculo dinâmico de acréscimos, taxas de serviço e juros.
  - **Trava de Disponibilidade (Estoque):** Bloqueio inteligente que impede novas ocupações caso um quarto atinja o limite máximo de 10 reservas simultâneas.
- **Ambiente Isolado (Docker):** Execução simplificada e conteinerizada via Laravel Sail
- **Git Pattern:** Versionamento padronizado com a convenção internacional de *Conventional Commits* (`feat:`).

---

## Recursos e Ferramentas Utilizados

Para o desenvolvimento deste ecossistema hoteleiro, foram utilizadas as seguintes ferramentas e tecnologias de mercado:
- **PHP 8.2 + Laravel:** Framework base para construção da API RESTful.
- **Docker & Docker Compose (Laravel Sail):** Para conteinerização e isolamento de todo o ambiente de desenvolvimento.
- **MySQL:** Banco de dados relacional para persistência estável das tabelas de hotéis, quartos e reservas.
- **MySQL Workbench:** Ferramenta visual utilizada para a modelagem física do banco de dados relacional.
- **L5-Swagger (OpenAPI 3.0):** Biblioteca utilizada para expor e gerar a documentação interativa das rotas por meio de PHP Attributes.
- **PHPUnit:** Framework nativo para a escrita e execução de testes funcionais.
- **Postman:** Plataforma de API utilizada para realizar as validações manuais e simular as requisições HTTP da aplicação.
- **Git & GitHub:** Para controle de versão distribuído e armazenamento do código-fonte na nuvem.

---

## Como Executar o Projeto Localmente

### 1. Pré-requisitos
Certifique-se de ter instalado em sua máquina de desenvolvimento:
- [Docker](https://docker.com)
- [Docker Compose](https://docker.com)

### 2. Inicializar o Ambiente
Abra o seu terminal na pasta raiz do projeto e execute os comandos:

```bash
# Comando para ligar o docker, deve ser feito no terminal do Linux
    sudo systemctl start docker
# Comando para desligar o docker e o socket, deve ser feito no terminal do Linux
    sudo systemctl stop docker docker.socket
# Inicializar os contêineres em segundo plano via Laravel Sail
./vendor/bin/sail up -d

# Instalar as dependências do Composer dentro do contêiner
./vendor/bin/sail composer install

# Limpar caches de configuração do framework
./vendor/bin/sail artisan config:clear
```

### 3. Criar as Tabelas do Banco de Dados
Para rodar as migrações e estruturar o banco com os tipos de dados `INT UNSIGNED` idênticos aos modelados no Workbench, execute:

```bash
./vendor/bin/sail artisan migrate:fresh
```

---

## Processo do Desenvolvimento: Executando o Cron de Importação XML

O sistema conta com um comando Artisan responsável por fazer a varredura e ingestão dos arquivos complexos de XML (`hotels.xml`, `rooms.xml` e `reserves.xml`), efetuando a correta associação relacional de chaves estrangeiras no banco de dados.

### Execução Manual do Comando (Seeder/Command):
Para disparar a leitura dos arquivos XML imediatamente pela linha de comando, execute:
```bash
./vendor/bin/sail artisan db:seed
```

## Processo do Desenvolvimento: Executando o Cron de Importação XML

O sistema conta com um comando Artisan responsável por fazer a varredura e ingestão dos arquivos complexos de XML (`hotels.xml`, `rooms.xml` e `reserves.xml`), efetuando a correta associação relacional de chaves estrangeiras no banco de dados.

### 1. Preparação da Infraestrutura de Cache do Agendador
Para que o agendador de tarefas funcione sem conflitos, o Laravel exige uma tabela no banco de dados para controlar o status de execução. Prepare e execute essa tabela utilizando os comandos:

```bash
# Cria o blueprint/arquivo da tabela de controle de cache
./vendor/bin/sail artisan cache:table

# Aplica a migração no MySQL sem apagar nenhuma tabela existente
./vendor/bin/sail artisan migrate
```

### 2. Simulação e Execução do CRON no Terminal
Você pode interagir e disparar o agendador diretamente pela linha de comando para testar a automação:

- **Forçar Execução Única Imediata:** Dispara a rotina uma única vez para testar a importação instantaneamente.
  ```bash
  ./vendor/bin/sail artisan schedule:run
  ```

- **Simular em Tempo Real (Modo Work):** Deixa o terminal em modo de escuta ativa, disparando a leitura do XML automaticamente a cada 60 segundos (pressione `Ctrl + C` para sair).
  ```bash
  ./vendor/bin/sail artisan schedule:work
---

## 🧪 Como Executar os Testes Automatizados Isoladamente

A suíte possui **9 testes automatizados** independentes. Para garantir a flexibilidade do desenvolvimento, você pode rodar os testes da suíte completa ou filtrar para avaliar apenas uma API isolada.

### Executar TODOS os testes simultaneamente:
```bash
./vendor/bin/sail artisan test
```

### Executar apenas os testes isolados da API de Quartos (CRUD Room):
```bash
./vendor/bin/sail artisan test --filter=RoomApiTest
```

### Executar apenas os testes isolados da API de Reservas (Motor de Reservas):
```bash
./vendor/bin/sail artisan test --filter=ReserveApiTest
```

---

## Acessando a Documentação Interativa (Swagger)

A API possui uma interface gráfica onde é possível visualizar a modelagem e efetuar testes em tempo real.

1. Certifique-se de compilar as rotas rodando: `./vendor/bin/sail artisan l5-swagger:generate`
2. Abra o seu navegador e acesse:
**[http://localhost/api/documentation](http://localhost/api/documentation)**

---

## Como Testar a Inserção de Dados via Postman

Para realizar requisições manuais e validar as respostas JSON estruturadas, configure as chamadas no seu Postman utilizando os exemplos de payload abaixo:

### 1. Cadastrar um Quarto no Postman (`POST /api/rooms`)
- **Método:** `POST`
- **URL:** `http://localhost/api/rooms`
- **Headers:** `Content-Type: application/json` e `Accept: application/json`
- **Body (raw - JSON):**
```json
{
    "hotel_id": 1,
    "name": "Suíte Luxo Vista Mar"
}
```

### 2. Cadastrar uma Reserva no Postman (`POST /api/reserves`)
O payload abaixo dispara as regras de negócio complexas do `ReserveService`. Ele aplica o cupom promocional e realiza o cálculo de taxas associadas.
- **Método:** `POST`
- **URL:** `http://localhost/api/reserves`
- **Headers:** `Content-Type: application/json` e `Accept: application/json`
- **Body (raw - JSON):**
```json
{
    "hotel_id": 1,
    "room_id": 1,
    "check_in": "2026-12-01",
    "check_out": "2026-12-05",
    "total": 500.00,
    "cupom": "CUPOM20",
    "guest": {
        "name": "Tamara",
        "last_name": "Lecia",
        "phone": "5571999999999"
    },
    "daily": {
        "date": "2026-12-01",
        "value": 125.00
    },
    "payment": {
        "method": "Cartão de Crédito",
        "value": 510.00
    }
}
```
