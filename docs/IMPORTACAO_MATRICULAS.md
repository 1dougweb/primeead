# Sistema de Importação de Matrículas

## Visão Geral

O sistema de importação de matrículas permite importar múltiplas matrículas de uma vez através de arquivos CSV, com validação automática e tratamento inteligente de duplicatas.

## Funcionalidades

### ✅ Características Principais
- **Importação em Lote**: Processa múltiplas matrículas simultaneamente
- **Validação Automática**: Verifica dados obrigatórios e formato
- **Tratamento de Duplicatas**: Ignora ou atualiza matrículas existentes
- **Modo de Teste**: Simula importação sem salvar no banco
- **Processamento em Lotes**: Configurável para melhor performance
- **Logs Detalhados**: Registra todas as operações para auditoria

### 🔧 Opções de Importação
- **Ignorar Duplicatas**: Padrão - não cria registros duplicados
- **Atualizar Existentes**: Atualiza matrículas já existentes
- **Tamanho do Lote**: Configurável (50, 100, 200, 500 registros)
- **Modo de Teste**: Simula importação para validação

## Como Usar

### 1. Acesso à Interface
```
Dashboard → Matrículas → Botão "Importar"
URL: /dashboard/matriculas/importar
```

### 2. Preparação do Arquivo CSV

#### Campos Obrigatórios
- `nome_completo` - Nome completo do aluno
- `cpf` - CPF do aluno (formato livre)
- `email` - E-mail válido do aluno
- `data_nascimento` - Data no formato YYYY-MM-DD
- `modalidade` - Tipo de ensino (ensino-fundamental, ensino-medio)
- `curso` - Nome do curso

#### Campos Opcionais
- `rg`, `orgao_emissor`, `sexo`, `estado_civil`
- `nacionalidade`, `naturalidade`
- `cep`, `logradouro`, `numero`, `complemento`, `bairro`, `cidade`, `estado`
- `telefone_fixo`, `telefone_celular`
- `nome_pai`, `nome_mae`
- `ultima_serie`, `ano_conclusao`, `escola_origem`
- `forma_pagamento`, `valor_total_curso`, `valor_matricula`, `valor_mensalidade`
- `numero_parcelas`, `dia_vencimento`, `observacoes`
- `parceiro_id`

### 3. Processo de Importação

#### Passo a Passo
1. **Baixar Template**: Clique em "Baixar template" para obter o arquivo modelo
2. **Preencher Dados**: Adicione as informações das matrículas no CSV
3. **Configurar Opções**: Escolha as opções de importação
4. **Upload**: Selecione o arquivo CSV (máximo 10MB)
5. **Teste**: Execute primeiro em modo de teste
6. **Importação Real**: Confirme a importação real

#### Configurações Recomendadas
- **Primeira Execução**: Use modo de teste
- **Tamanho do Lote**: 100 registros (padrão)
- **Duplicatas**: Ignorar (padrão) ou Atualizar se necessário

### 4. Validação e Tratamento

#### Validações Automáticas
- Formato de e-mail válido
- Data de nascimento válida
- Campos obrigatórios preenchidos
- Formato de CPF (aceita com ou sem pontuação)

#### Tratamento de Duplicatas
- **Busca por CPF**: Método mais confiável
- **Busca por E-mail**: Método secundário
- **Busca por Nome**: Método menos confiável

## Comando Artisan

### Uso Básico
```bash
php artisan matriculas:import arquivo.csv
```

### Opções Disponíveis
```bash
# Simulação (não salva no banco)
php artisan matriculas:import arquivo.csv --dry-run

# Atualizar matrículas existentes
php artisan matriculas:import arquivo.csv --update-existing

# Ignorar duplicatas (padrão)
php artisan matriculas:import arquivo.csv --ignore-duplicates

# Tamanho do lote personalizado
php artisan matriculas:import arquivo.csv --batch-size=200
```

### Exemplos de Uso
```bash
# Importação básica
php artisan matriculas:import storage/app/imports/exemplo.csv

# Simulação com lote de 500
php artisan matriculas:import arquivo.csv --dry-run --batch-size=500

# Atualizar existentes
php artisan matriculas:import arquivo.csv --update-existing
```

## Estrutura do Arquivo CSV

### Exemplo de Cabeçalho
```csv
nome_completo,cpf,email,data_nascimento,modalidade,curso,telefone_celular,sexo,estado_civil,nacionalidade,cep,logradouro,numero,bairro,cidade,estado,nome_mae,forma_pagamento,valor_total_curso,numero_parcelas,dia_vencimento
```

### Exemplo de Dados
```csv
João Silva Santos,123.456.789-00,joao.silva@email.com,1990-05-15,ensino-medio,Supletivo Ensino Médio,(11) 99999-8888,M,solteiro,Brasileira,01234-567,Rua das Flores,123,Centro,São Paulo,SP,Maria Silva Santos,boleto,1200.00,10,15
```

## Tratamento de Erros

### Tipos de Erro
1. **Validação**: Campos obrigatórios ausentes ou inválidos
2. **Formato**: Estrutura do arquivo incorreta
3. **Duplicatas**: Conflitos com registros existentes
4. **Sistema**: Problemas de permissão ou banco de dados

### Logs e Auditoria
- Todos os erros são registrados em `storage/logs/laravel.log`
- Relatório detalhado após cada importação
- Contador de registros processados, criados, atualizados e ignorados

## Performance e Limitações

### Limitações
- **Tamanho do arquivo**: Máximo 10MB
- **Timeout**: 5 minutos por importação
- **Memória**: Processamento em lotes para otimizar uso

### Recomendações
- Use arquivos CSV com menos de 10.000 registros
- Execute importações grandes em horários de baixo tráfego
- Monitore logs para identificar gargalos

## Segurança

### Permissões
- Requer permissão `matriculas.create`
- Apenas usuários autenticados
- Logs de todas as operações

### Validação
- Sanitização de dados de entrada
- Validação de tipos e formatos
- Prevenção de injeção SQL

## Troubleshooting

### Problemas Comuns

#### Arquivo não é reconhecido
- Verifique se é um arquivo CSV válido
- Confirme a codificação (UTF-8 recomendado)
- Verifique se não há caracteres especiais no cabeçalho

#### Erro de validação
- Verifique campos obrigatórios
- Confirme formato de data (YYYY-MM-DD)
- Valide formato de e-mail

#### Timeout na importação
- Reduza o tamanho do lote
- Verifique performance do servidor
- Execute em horários de baixo tráfego

#### Duplicatas não detectadas
- Verifique se o CPF está correto
- Confirme se o e-mail está válido
- Use modo de teste para verificar

### Comandos de Diagnóstico
```bash
# Verificar logs
tail -f storage/logs/laravel.log

# Verificar permissões
php artisan route:list --name=matriculas.importar

# Testar conexão com banco
php artisan tinker
DB::connection()->getPdo();
```

## Suporte e Manutenção

### Arquivos do Sistema
- **Controller**: `app/Http/Controllers/Admin/MatriculaImportController.php`
- **Comando**: `app/Console/Commands/ImportMatriculas.php`
- **View**: `resources/views/admin/matriculas/importar.blade.php`
- **Rotas**: `routes/web.php` (grupo matriculas)

### Atualizações
- Sistema é compatível com Laravel 8+
- Mantenha backups antes de atualizações
- Teste em ambiente de desenvolvimento

### Contato
Para suporte técnico ou dúvidas sobre o sistema de importação, consulte a documentação do projeto ou entre em contato com a equipe de desenvolvimento.
