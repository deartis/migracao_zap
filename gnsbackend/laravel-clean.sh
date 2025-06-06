#!/bin/bash

# Script de limpeza para Laravel 12
# Autor: [Seu Nome]
# Descrição: Limpa cache, rotas, views e otimiza o projeto Laravel

# Cores para melhor visualização
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Verifica se o usuário está no diretório raiz do Laravel
if [ ! -f "artisan" ]; then
    echo -e "${RED}Erro: Você não está no diretório raiz do projeto Laravel.${NC}"
    echo -e "Execute este script a partir do diretório onde está o arquivo artisan."
    exit 1
fi

# Função para executar comandos com feedback
run_command() {
    echo -e "${YELLOW}Executando: $1${NC}"
    eval $1
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✔ Sucesso${NC}"
    else
        echo -e "${RED}✖ Erro ao executar: $1${NC}"
    fi
    echo ""
}

# Limpeza básica do Laravel
echo -e "${YELLOW}Iniciando limpeza do projeto Laravel...${NC}"

run_command "php artisan cache:clear"
run_command "php artisan config:clear"
run_command "php artisan route:clear"
run_command "php artisan view:clear"
run_command "php artisan event:clear"

# Limpar cache de compilação
run_command "php artisan clear-compiled"

# Limpar cache do composer
run_command "composer dump-autoload"

# Otimização do Laravel
echo -e "${YELLOW}Otimizando o projeto...${NC}"

run_command "php artisan optimize"
run_command "php artisan config:cache"
run_command "php artisan route:cache"
run_command "php artisan view:cache"

# Limpar arquivos temporários (opcional)
echo -e "${YELLOW}Limpando arquivos temporários...${NC}"

run_command "rm -rf storage/framework/cache/*"
run_command "rm -rf storage/framework/sessions/*"
run_command "rm -rf storage/framework/testing/*"
run_command "rm -rf storage/framework/views/*"
run_command "rm -rf bootstrap/cache/*"

# Limpar logs (opcional)
echo -e "${YELLOW}Limpando arquivos de log...${NC}"
run_command "rm -rf storage/logs/*.log"

# Reinstalar node_modules (opcional)
echo -e "${YELLOW}Reinstalando dependências do Node.js...${NC}"
if [ -d "node_modules" ]; then
    run_command "rm -rf node_modules"
fi
run_command "npm install"

# Finalização
echo -e "${GREEN}✔ Limpeza e otimização concluídas com sucesso!${NC}"
