# Sistema de Importação de Matrículas - Refatorado

## Visão Geral

Este sistema foi completamente refatorado seguindo as melhores práticas do Laravel, implementando:

- **Service Layer** para lógica de negócio
- **Repository Pattern** para acesso a dados
- **DTOs** para transferência de dados
- **Form Requests** para validação
- **Job Queues** para processamento assíncrono
- **Melhor tratamento de erros** e logging
- **Validação robusta** de dados

## Estrutura dos Arquivos

```
app/
├── Console/Commands/
│   └── ImportMatriculasCommand.php          # Comando CLI
├── DTOs/
│   └── MatriculaImportData.php              # DTO para dados de importação
├── Http/
│   ├── Controllers/Admin/
│   │   └── MatriculaImportController.php    # Controller refatorado
│   └── Requests/
│       └── MatriculaImportRequest.php       # Validação de formulário
├── Jobs/
│   └── ProcessMatriculaImport.php           # Job assíncrono
├── Mail/
│   └── MatriculaImportCompleted.php         # E-mail de notificação
├── Models/
│   └── Matricula.php                        # Modelo existente
├── Providers/
│   └── MatriculaImportServiceProvider.php   # Service Provider
├── Repositories/
│   └── MatriculaRepository.php              # Repository para matrículas
└── Services/
    ├── CsvParserService.php                 # Parser de CSV
    └── MatriculaImportService.php           # Service principal
```

## Funcionalidades

### 1. Importação via Interface Web
- Upload de arquivo CSV
- Validação em tempo real
- Simulação (dry run)
- Processamento assíncrono
- Notificações por e-mail

### 2. Importação via CLI
- Comando Artisan `matriculas:import`
- Opções configuráveis
- Relatórios detalhados
- Tratamento de erros

### 3. Validações Implementadas
- Campos obrigatórios
- Validação de CPF
- Validação de e-mail
- Validação de valores numéricos
- Verificação de duplicatas

## Uso

### Interface Web

1. Acesse `/dashboard/matriculas/importar`
2. Selecione o arquivo CSV
3. Configure as opções:
   - **Atualizar existentes**: Atualiza matrículas com mesmo CPF
   - **Ignorar duplicatas**: Pula registros duplicados
   - **Tamanho do lote**: Número de registros processados por vez
   - **Simulação**: Executa sem salvar no banco
   - **Parceiro**: Associa todas as matrículas a um parceiro específico
   - **E-mail de notificação**: Recebe relatório por e-mail

### Comando CLI

```bash
# Importação básica
php artisan matriculas:import arquivo.csv

# Simulação (dry run)
php artisan matriculas:import arquivo.csv --dry-run

# Atualizar existentes
php artisan matriculas:import arquivo.csv --update-existing

# Configurar tamanho do lote
php artisan matriculas:import arquivo.csv --batch-size=200

# Associar a um parceiro
php artisan matriculas:import arquivo.csv --parceiro-id=1

# Executar como usuário específico
php artisan matriculas:import arquivo.csv --user-id=2

# Combinação de opções
php artisan matriculas:import arquivo.csv --dry-run --batch-size=500 --update-existing
```

## Estrutura do CSV

### Cabeçalhos Obrigatórios
- `nome_completo` - Nome completo do aluno
- `cpf` - CPF do aluno
- `email` - E-mail do aluno
- `telefone_celular` - Telefone celular

### Cabeçalhos Opcionais
- `rg`, `orgao_emissor`, `sexo`, `estado_civil`
- `nacionalidade`, `naturalidade`, `cep`, `logradouro`
- `numero`, `complemento`, `bairro`, `cidade`, `estado`
- `telefone_fixo`, `nome_pai`, `nome_mae`
- `modalidade`, `curso`, `ultima_serie`, `ano_conclusao`
- `escola_origem`, `forma_pagamento`
- `valor_total_curso`, `valor_matricula`, `valor_mensalidade`
- `numero_parcelas`, `dia_vencimento`, `observacoes`
- `parceiro_id`

## Configuração

### 1. Service Provider
O `MatriculaImportServiceProvider` é registrado automaticamente e configura:
- Repository de matrículas
- Service de parsing CSV
- Service de importação

### 2. Filas
Para processamento assíncrono, configure no `.env`:
```env
QUEUE_CONNECTION=database
```

Execute as migrações de filas:
```bash
php artisan queue:table
php artisan migrate
```

### 3. Permissões
O usuário deve ter a permissão `matriculas.create` para acessar a funcionalidade.

## Processamento Assíncrono

### 1. Job Queue
- **Fila**: `imports`
- **Timeout**: 30 minutos
- **Tentativas**: 3
- **Retry**: Automático em caso de falha

### 2. Monitoramento
- Logs detalhados em `storage/logs/laravel.log`
- Status das importações via endpoint `/importar/status`
- Limpeza automática de arquivos antigos

### 3. Notificações
- E-mail de conclusão com estatísticas
- E-mail de erro em caso de falha
- Relatórios detalhados de processamento

## Tratamento de Erros

### 1. Validação
- Validação de estrutura do CSV
- Validação de campos obrigatórios
- Validação de formatos (CPF, e-mail, valores)

### 2. Processamento
- Tratamento de erros por linha
- Continuação do processamento em caso de erro
- Log detalhado de todas as operações

### 3. Recuperação
- Retry automático de jobs falhados
- Limpeza de arquivos temporários
- Notificações de falhas

## Performance

### 1. Processamento em Lotes
- Configurável via `batch_size`
- Padrão: 100 registros por lote
- Máximo: 1000 registros por lote

### 2. Otimizações
- Uso de transações de banco
- Limpeza automática de arquivos
- Processamento assíncrono para arquivos grandes

### 3. Monitoramento
- Métricas de tempo de processamento
- Contadores de registros processados
- Estatísticas de sucesso/erro

## Manutenção

### 1. Limpeza Automática
- Arquivos de importação são removidos após 7 dias
- Comando `cleanup` disponível via API
- Logs de limpeza para auditoria

### 2. Logs
- Todos os eventos são logados
- Rastreamento de usuários
- Histórico de importações

### 3. Backup
- Arquivos temporários são limpos automaticamente
- Dados de importação são preservados no banco
- Logs mantêm histórico completo

## Troubleshooting

### Problemas Comuns

1. **Arquivo não encontrado**
   - Verificar permissões de diretório
   - Verificar configuração de storage

2. **Timeout de job**
   - Aumentar `QUEUE_TIMEOUT` no `.env`
   - Reduzir `batch_size`

3. **Erros de validação**
   - Verificar estrutura do CSV
   - Validar campos obrigatórios
   - Verificar formatos de dados

4. **Falhas de fila**
   - Verificar conexão de banco
   - Executar `php artisan queue:work`
   - Verificar logs de erro

### Comandos Úteis

```bash
# Verificar status das filas
php artisan queue:work --queue=imports

# Limpar filas
php artisan queue:clear --queue=imports

# Verificar jobs falhados
php artisan queue:failed

# Retry de jobs falhados
php artisan queue:retry all

# Limpar arquivos de importação
php artisan matriculas:cleanup
```

## Contribuição

Para contribuir com melhorias:

1. Siga os padrões PSR-12
2. Use strict typing
3. Implemente testes unitários
4. Documente mudanças
5. Mantenha compatibilidade com versões anteriores

## Licença

Este sistema é parte do projeto EC e segue as mesmas políticas de licenciamento.
