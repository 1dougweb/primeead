# Sistema de Importação de Inscrições

## Visão Geral

O sistema de importação de inscrições permite importar leads em lote através de arquivos CSV, mantendo a compatibilidade com o formato de exportação existente e ignorando automaticamente leads duplicados.

## Funcionalidades

### ✅ Características Principais
- **Importação em lote** via arquivo CSV
- **Compatibilidade total** com formato de exportação
- **Detecção automática de duplicatas** (por email)
- **Validação de dados** em tempo real
- **Tratamento de encoding** automático
- **Template de exemplo** disponível para download
- **Relatório detalhado** de resultados

### 🔒 Segurança
- **Validação de permissões** (requer `inscricoes.index`)
- **Validação de arquivo** (tamanho máximo: 10MB)
- **Sanitização de dados** antes da inserção
- **Log de erros** para auditoria

## Como Usar

### 1. Acessar a Funcionalidade
- Navegue para `/dashboard/inscricoes`
- Clique no botão **"Importar CSV"** (azul)
- Ou acesse diretamente `/dashboard/inscricoes/importar`

### 2. Preparar o Arquivo
- **Formato**: CSV com separador ponto e vírgula (;)
- **Encoding**: UTF-8 (recomendado)
- **Tamanho**: Máximo 10MB
- **Cabeçalhos obrigatórios**:
  - ID
  - Nome
  - Email
  - Telefone
  - Curso
  - Modalidade
  - Aceita Termos
  - IP
  - Data/Hora
  - Etiqueta
  - Posição Kanban
  - Prioridade
  - Notas

### 3. Processar Importação
1. Clique em **"Selecionar Arquivo"**
2. Escolha o arquivo CSV
3. Marque a confirmação de formato
4. Clique em **"Importar"**

### 4. Verificar Resultados
- **Sucesso**: Redirecionamento para lista com mensagem
- **Erros**: Exibição detalhada de problemas encontrados
- **Estatísticas**: Contagem de importados vs. ignorados

## Formato do Arquivo

### Exemplo de Cabeçalho
```csv
ID;Nome;Email;Telefone;Curso;Modalidade;Aceita Termos;IP;Data/Hora;Etiqueta;Posição Kanban;Prioridade;Notas
```

### Exemplo de Dados
```csv
1;João Silva;joao@email.com;(11) 99999-9999;eja;online;Sim;192.168.1.1;01/01/2024 10:00:00;pendente;1;media;Lead interessado no curso EJA
2;Maria Santos;maria@email.com;(11) 99999-8888;eja;presencial;Sim;192.168.1.2;01/01/2024 11:00:00;contatado;2;alta;Já foi contatada, aguardando retorno
```

## Validações

### Campos Obrigatórios
- **Nome**: Não pode estar vazio
- **Email**: Deve ser um email válido e único
- **Telefone**: Não pode estar vazio

### Campos Opcionais
- **Curso**: Se vazio, usa padrão do sistema
- **Modalidade**: Se vazio, usa padrão do sistema
- **Data/Hora**: Se vazio, usa data/hora atual
- **IP**: Se vazio, usa IP da requisição
- **Etiqueta**: Se vazio, usa 'pendente' (padrão)
- **Posição Kanban**: Se vazio, calcula automaticamente
- **Prioridade**: Se vazio, usa 'media' (padrão)
- **Notas**: Se vazio, fica em branco

### Validações Especiais
- **Email único**: Não permite duplicatas
- **Formato de data**: Aceita múltiplos formatos
- **Termos**: Converte "Sim", "Yes", "1", "true" para true
- **Etiqueta**: Valida contra valores permitidos do sistema
- **Prioridade**: Valida contra níveis permitidos
- **Posição Kanban**: Calcula automaticamente se não fornecida

## Tratamento de Erros

### Tipos de Erro
1. **Arquivo inválido**: Formato ou encoding incorreto
2. **Cabeçalhos faltando**: Colunas obrigatórias ausentes
3. **Dados inválidos**: Campos obrigatórios vazios
4. **Email inválido**: Formato incorreto
5. **Curso/Modalidade inválidos**: Valores não reconhecidos
6. **Erros de banco**: Problemas na inserção

### Resolução
- **Verificar formato**: Usar template como referência
- **Corrigir dados**: Preencher campos obrigatórios
- **Validar valores**: Usar apenas valores permitidos
- **Reimportar**: Corrigir arquivo e tentar novamente

## Performance

### Otimizações
- **Processamento em lote**: Uma transação por arquivo
- **Validação eficiente**: Verificação de duplicatas por email
- **Tratamento de encoding**: Detecção automática
- **Log de erros**: Coleta em memória para relatório

### Limitações
- **Tamanho máximo**: 10MB por arquivo
- **Tempo de processamento**: Depende do número de registros
- **Memória**: Consumo proporcional ao tamanho do arquivo

## Monitoramento

### Logs Disponíveis
- **Arquivos processados**: Contagem de sucessos
- **Registros ignorados**: Duplicatas detectadas
- **Erros encontrados**: Detalhes de validação
- **Tempo de processamento**: Performance da importação

### Métricas
- **Taxa de sucesso**: Importados / Total
- **Taxa de duplicatas**: Ignorados / Total
- **Taxa de erro**: Erros / Total

## Troubleshooting

### Problemas Comuns

#### 1. "Formato de arquivo inválido"
- **Causa**: Cabeçalhos incorretos ou separador errado
- **Solução**: Usar template como referência

#### 2. "Email inválido"
- **Causa**: Formato incorreto ou campo vazio
- **Solução**: Verificar formato e preenchimento

#### 3. "Curso inválido"
- **Causa**: Valor não reconhecido pelo sistema
- **Solução**: Usar valores do template ou deixar vazio

#### 4. "Arquivo vazio"
- **Causa**: Arquivo sem dados ou encoding incorreto
- **Solução**: Verificar conteúdo e encoding

### Dicas de Uso
- **Sempre testar** com arquivo pequeno primeiro
- **Verificar encoding** antes da importação
- **Usar template** como base para formatação
- **Validar dados** antes da importação
- **Fazer backup** antes de importações grandes

## Suporte

### Recursos Disponíveis
- **Template de exemplo**: Download automático
- **Validação em tempo real**: Feedback imediato
- **Relatório detalhado**: Análise completa de resultados
- **Log de erros**: Rastreamento de problemas

### Contato
Para suporte técnico ou dúvidas sobre a funcionalidade, entre em contato com a equipe de desenvolvimento.

---

**Versão**: 1.0  
**Última atualização**: {{ date('d/m/Y H:i:s') }}  
**Desenvolvido por**: Sistema de Inscrições
