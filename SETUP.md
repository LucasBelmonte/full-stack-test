# Guia de Instalação do Projeto Laravel

Este guia detalha os passos necessários para configurar e executar o projeto Laravel localmente, com foco em usuários do VS Code.

## Pré-requisitos

Certifique-se de ter os seguintes softwares instalados em sua máquina:

*   **VS Code**: Editor de código.
*   **Git**: Sistema de controle de versão.
*   **PHP 8.4+**: Linguagem de programação. (Já instalado no ambiente de sandbox)
*   **Composer**: Gerenciador de dependências para PHP. (Já instalado no ambiente de sandbox)
*   **Node.js (LTS)**: Ambiente de execução JavaScript.
*   **npm** ou **Yarn**: Gerenciador de pacotes para Node.js.

## Passos de Instalação

1.  **Clone o Repositório**

    Se você ainda não clonou o projeto, faça-o usando o Git:

    ```bash
    git clone <URL_DO_REPOSITORIO>
    cd full-stack-test-master
    ```

2.  **Instalar Dependências PHP**

    Navegue até o diretório do projeto e instale as dependências do Composer:

    ```bash
    composer install
    ```

3.  **Configurar o Ambiente**

    Crie o arquivo de ambiente a partir do exemplo e gere a chave da aplicação:

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

    Abra o arquivo `.env` no VS Code e configure as variáveis de ambiente, especialmente as relacionadas ao banco de dados. Para um setup rápido, você pode usar SQLite. Altere as seguintes linhas:

    ```dotenv
    DB_CONNECTION=sqlite
    DB_DATABASE=/path/to/your/project/database/database.sqlite
    ```

    Certifique-se de que o caminho para `database.sqlite` seja absoluto e que o arquivo `database.sqlite` exista. Você pode criá-lo com `touch database/database.sqlite`.

4.  **Executar Migrações do Banco de Dados**

    Execute as migrações para criar as tabelas no banco de dados:

    ```bash
    php artisan migrate
    ```

5.  **Instalar Dependências Node.js**

    Instale as dependências JavaScript usando npm ou Yarn:

    ```bash
    npm install
    # ou
    yarn install
    ```

6.  **Compilar Assets Frontend**

    Compile os assets frontend (Vue.js, CSS, etc.):

    ```bash
    npm run dev
    # ou
    yarn dev
    ```

    Para produção:

    ```bash
    npm run build
    # ou
    yarn build
    ```

7.  **Iniciar o Servidor de Desenvolvimento**

    Inicie o servidor local do Laravel:

    ```bash
    php artisan serve
    ```

    A aplicação estará disponível em `http://127.0.0.1:8000` (ou outra porta, se especificado).

8.  **Acessar a Aplicação**

    Abra seu navegador e acesse a URL fornecida. Você pode registrar um novo usuário ou usar as credenciais de um seeder (se houver).

## Configuração do VS Code

Para uma melhor experiência de desenvolvimento no VS Code, considere instalar as seguintes extensões:

*   **PHP Intelephense**: Suporte avançado a PHP.
*   **Laravel Blade Snippets**: Snippets para Blade.
*   **Vue.js Extension Pack**: Suporte a Vue.js.
*   **Tailwind CSS IntelliSense**: Autocompletar e linting para Tailwind CSS.
*   **ESLint**: Para linting de JavaScript/TypeScript.
*   **Prettier**: Para formatação de código.

## Executando Testes

Para executar os testes, use o comando:

```bash
php artisan test
```

Certifique-se de que seu arquivo `phpunit.xml` esteja configurado para usar um banco de dados de teste em memória ou um banco de dados de teste separado para evitar conflitos com seus dados de desenvolvimento. (Já corrigido no ambiente de sandbox para usar `:memory:`).

## Próximos Passos (Correções e Refatorações)

Com o projeto funcionando, você pode começar a abordar os problemas identificados:

*   **Vulnerabilidade de Multi-tenancy**: Implementar um middleware para validar o `team_id` na requisição e garantir que o usuário autenticado pertence ao time solicitado.
*   **Bugs de Faturamento**: Refatorar a lógica de cálculo de faturas para ser mais robusta e centralizada. Melhorar o tratamento de erros com o `BankService`.
*   **Segurança em Rotas de Admin**: Revisar o `FeatureAuthorizationMiddleware` e garantir que a autorização para rotas de admin seja rigorosa.
*   **Testes**: Corrigir os testes falhos e adicionar novos testes para cobrir as funcionalidades críticas e as correções implementadas.

Este documento será atualizado com as decisões e implementações à medida que o projeto for corrigido e refatorado. 
