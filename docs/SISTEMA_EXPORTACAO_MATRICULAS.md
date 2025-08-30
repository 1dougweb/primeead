# Sistema de Exportação de Matrículas

## Visão Geral

O sistema de exportação de matrículas foi desenvolvido para permitir a extração completa de dados das matrículas em múltiplos formatos, incluindo todas as colunas disponíveis no sistema, inclusive as relacionadas ao Google Drive.

## Funcionalidades Principais

### ✅ **Exportação Completa**
- **TODAS as colunas** disponíveis no sistema
- **Colunas do Google Drive** incluídas
- **Múltiplos formatos**: CSV, Excel, JSON, PDF
- **Filtros avançados** para exportações específicas
- **Ordenação personalizada** por qualquer coluna
- **Limite configurável** de registros (até 10.000)

### ✅ **Interface Web**
- Formulário intuitivo com todas as opções
- Seleção de colunas organizadas por categoria
- Filtros visuais para facilitar a busca
- Preview em tempo real das configurações
- Download direto dos arquivos gerados

### ✅ **Processamento Assíncrono**
- Jobs em fila para exportações grandes
- Notificações por e-mail ao concluir
- Monitoramento de status em tempo real
- Limpeza automática de arquivos antigos

### ✅ **Comando CLI**
- Exportação via terminal
- Filtros via JSON
- Configuração completa via parâmetros
- Ideal para automações e scripts

## Estrutura de Arquivos

```
app/
├── Http/
│   ├── Controllers/Admin/
│   │   └── MatriculaExportController.php    # Controller principal
│   └── Requests/
│       └── MatriculaExportRequest.php       # Validação de dados
├── Services/
│   └── MatriculaExportService.php           # Lógica de negócio
├── Jobs/
│   └── ProcessMatriculaExport.php           # Processamento assíncrono
├── DTOs/
│   └── MatriculaExportData.php              # Transferência de dados
├── Mail/
│   └── MatriculaExportCompleted.php         # E-mail de notificação
└── Console/Commands/
    └── ExportMatriculasCommand.php          # Comando CLI

resources/views/
└── admin/matriculas/
    └── exportar.blade.php                   # Interface web

resources/views/emails/
└── matricula-export-completed.blade.php     # Template do e-mail
```

## Colunas Disponíveis para Exportação

### 🔍 **Identificação**
- `id` - ID único da matrícula
- `inscricao_id` - ID da inscrição relacionada
- `numero_matricula` - Número da matrícula

### 👤 **Dados Pessoais**
- `nome_completo` - Nome completo do aluno
- `data_nascimento` - Data de nascimento
- `cpf` - CPF do aluno
- `rg` - RG do aluno
- `orgao_emissor` - Órgão emissor do RG
- `sexo` - Sexo (M/F)
- `estado_civil` - Estado civil
- `nacionalidade` - Nacionalidade
- `naturalidade` - Naturalidade

### 📞 **Contato**
- `cep` - CEP do endereço
- `logradouro` - Logradouro
- `numero` - Número do endereço
- `complemento` - Complemento do endereço
- `bairro` - Bairro
- `cidade` - Cidade
- `estado` - Estado
- `telefone_fixo` - Telefone fixo
- `telefone_celular` - Telefone celular
- `email` - E-mail

### 👨‍👩‍👧‍👦 **Familiares**
- `nome_pai` - Nome do pai
- `nome_mae` - Nome da mãe

### 🎓 **Dados Acadêmicos**
- `modalidade` - Modalidade de ensino
- `curso` - Nome do curso
- `ultima_serie` - Última série cursada
- `ano_conclusao` - Ano de conclusão
- `escola_origem` - Escola de origem

### 📊 **Status e Configuração**
- `status` - Status da matrícula
- `escola_parceira` - Se é escola parceira
- `parceiro_id` - ID do parceiro

### 💰 **Pagamento**
- `forma_pagamento` - Forma de pagamento
- `tipo_boleto` - Tipo de boleto
- `valor_total_curso` - Valor total do curso
- `valor_matricula` - Valor da matrícula
- `valor_mensalidade` - Valor da mensalidade
- `numero_parcelas` - Número de parcelas
- `dia_vencimento` - Dia de vencimento
- `forma_pagamento_mensalidade` - Forma de pagamento da mensalidade
- `parcelas_ativas` - Parcelas ativas
- `parcelas_geradas` - Parcelas geradas
- `parcelas_pagas` - Parcelas pagas
- `percentual_juros` - Percentual de juros
- `desconto` - Desconto aplicado

### 📄 **Documentos**
- `doc_rg_cpf` - Documentos RG/CPF
- `doc_comprovante` - Comprovante
- `doc_historico` - Histórico escolar
- `doc_certificado` - Certificado
- `doc_outros` - Outros documentos

### ☁️ **Google Drive**
- `google_drive_folder_id` - ID da pasta no Google Drive

### 📝 **Observações e Metadados**
- `observacoes` - Observações gerais
- `created_at` - Data de criação
- `updated_at` - Data de atualização
- `deleted_at` - Data de exclusão (soft delete)
- `created_by` - Usuário que criou
- `updated_by` - Usuário que atualizou

## Filtros Disponíveis

### 🔍 **Filtros Básicos**
- `status` - Filtrar por status da matrícula
- `parceiro_id` - Filtrar por parceiro específico
- `modalidade` - Filtrar por modalidade de ensino
- `curso` - Buscar por nome do curso (busca parcial)

### 📅 **Filtros de Data**
- `data_inicio` - Data de início para filtro
- `data_fim` - Data de fim para filtro

### 💰 **Filtros de Valor**
- `valor_min` - Valor mínimo do curso
- `valor_max` - Valor máximo do curso

## Formatos de Exportação

### 📊 **CSV**
- Formato padrão para planilhas
- Cabeçalhos configuráveis
- Codificação UTF-8
- Separador de campos configurável

### 📈 **Excel**
- Formato .xlsx compatível com Excel
- Múltiplas abas se necessário
- Formatação automática de dados

### 🔧 **JSON**
- Formato estruturado para APIs
- Dados aninhados preservados
- Ideal para integrações

### 📄 **PDF**
- Relatório formatado
- Cabeçalhos e rodapés
- Paginação automática

## Uso da Interface Web

### 1. **Acesso**
- Navegue para `/dashboard/matriculas/exportar`
- Ou clique no botão "Exportar" na listagem de matrículas

### 2. **Configuração**
- Selecione o formato desejado
- Escolha as colunas para exportar
- Configure filtros se necessário
- Defina ordenação e limite

### 3. **Execução**
- Clique em "Iniciar Exportação"
- Aguarde o processamento
- Faça download do arquivo gerado

## Uso via Comando CLI

### 📋 **Comando Básico**
```bash
php artisan matriculas:export csv
```

### 🔍 **Com Filtros**
```bash
php artisan matriculas:export csv --filters='{"status":"ativo","parceiro_id":1}'
```

### 📊 **Com Colunas Específicas**
```bash
php artisan matriculas:export excel --columns="nome_completo,cpf,email,curso"
```

### 🔄 **Com Ordenação**
```bash
php artisan matriculas:export json --sort-by="nome_completo" --sort-direction="asc"
```

### 📏 **Com Limite**
```bash
php artisan matriculas:export pdf --limit=500
```

### 🚀 **Exportação Completa**
```bash
php artisan matriculas:export csv \
  --filters='{"status":"ativo"}' \
  --columns="nome_completo,cpf,email,curso,google_drive_folder_id" \
  --sort-by="created_at" \
  --sort-direction="desc" \
  --limit=2000
```

## Configuração

### ⚙️ **Variáveis de Ambiente**
```env
# Configuração de filas
QUEUE_CONNECTION=database

# Configuração de armazenamento
FILESYSTEM_DISK=local
```

### 🔧 **Configuração de Filas**
```bash
# Criar tabelas de fila
php artisan queue:table
php artisan queue:failed-table

# Executar worker de fila
php artisan queue:work --queue=exports
```

## Monitoramento e Manutenção

### 📊 **Status das Exportações**
- Verificar status: `/dashboard/matriculas/exportar/status`
- Limpeza automática: `/dashboard/matriculas/exportar/cleanup`

### 🧹 **Limpeza Automática**
- Arquivos antigos são removidos automaticamente após 7 dias
- Comando manual: `php artisan matriculas:export:cleanup`

### 📧 **Notificações**
- E-mail enviado ao concluir exportação
- E-mail de erro se houver falha
- Configurável por exportação

## Performance e Limites

### ⚡ **Processamento**
- **Exportações pequenas** (≤1000 registros): Processamento imediato
- **Exportações grandes** (>1000 registros): Processamento assíncrono
- **Timeout**: 30 minutos por job
- **Retry**: 3 tentativas em caso de falha

### 📏 **Limites**
- **Máximo de registros**: 10.000 por exportação
- **Tamanho de arquivo**: Sem limite (depende do servidor)
- **Tempo de processamento**: Até 30 minutos

## Segurança e Permissões

### 🔐 **Autenticação**
- Usuário deve estar logado
- Permissão `matriculas.index` obrigatória

### 🛡️ **Validação**
- Validação de formato de arquivo
- Validação de filtros e parâmetros
- Sanitização de dados de entrada

### 📁 **Armazenamento**
- Arquivos salvos em `storage/app/exports/`
- Acesso restrito via middleware de autenticação
- Download seguro via controller

## Troubleshooting

### ❌ **Problemas Comuns**

#### Exportação não inicia
- Verificar permissões do usuário
- Verificar configuração de filas
- Verificar logs de erro

#### Arquivo não é gerado
- Verificar espaço em disco
- Verificar permissões de escrita
- Verificar logs de erro

#### Filtros não funcionam
- Verificar sintaxe dos filtros
- Verificar nomes das colunas
- Verificar valores dos filtros

### 🔍 **Logs e Debug**
```bash
# Ver logs de exportação
tail -f storage/logs/laravel.log | grep "export"

# Ver jobs em fila
php artisan queue:monitor

# Ver jobs falhados
php artisan queue:failed
```

## Exemplos de Uso

### 📊 **Relatório de Matrículas por Parceiro**
```bash
php artisan matriculas:export csv \
  --filters='{"parceiro_id":5}' \
  --columns="nome_completo,cpf,curso,status,valor_total_curso,created_at" \
  --sort-by="created_at" \
  --sort-direction="desc"
```

### 📈 **Relatório Financeiro**
```bash
php artisan matriculas:export excel \
  --filters='{"status":"ativo"}' \
  --columns="nome_completo,cpf,valor_total_curso,valor_matricula,valor_mensalidade,numero_parcelas,parcelas_pagas" \
  --sort-by="valor_total_curso" \
  --sort-direction="desc"
```

### 🔍 **Relatório de Documentos**
```bash
php artisan matriculas:export json \
  --columns="nome_completo,cpf,doc_rg_cpf,doc_comprovante,doc_historico,doc_certificado,google_drive_folder_id" \
  --filters='{"status":"ativo"}'
```

## Contribuição

### 🚀 **Melhorias Futuras**
- Suporte a mais formatos (XML, YAML)
- Exportação para Google Sheets
- Relatórios agendados
- Templates personalizáveis
- Integração com sistemas externos

### 🐛 **Reportar Bugs**
- Criar issue no repositório
- Incluir logs de erro
- Descrever passos para reproduzir

### 💡 **Sugestões**
- Abrir discussão no repositório
- Descrever caso de uso
- Propor solução se possível

---

**Desenvolvido para o projeto EC - Sistema Educacional Completo**

Este sistema é parte do projeto EC e segue as mesmas políticas de licenciamento.
