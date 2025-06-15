#!/bin/bash

# --- CONFIGURAÇÃO ---
# Opções para PROJECT_PATH:
# "." ou "./" - se executando o script no diretório do projeto
# "/caminho/completo/para/projeto" - caminho absoluto
# Deixe vazio "" para usar o diretório atual
PROJECT_PATH=""
# --------------------

# Se PROJECT_PATH estiver vazio, usar diretório atual
if [ -z "$PROJECT_PATH" ]; then
    PROJECT_PATH="$(pwd)"
    echo "Usando diretório atual: $PROJECT_PATH"
elif [ "$PROJECT_PATH" = "." ] || [ "$PROJECT_PATH" = "./" ]; then
    PROJECT_PATH="$(pwd)"
    echo "Usando diretório atual: $PROJECT_PATH"
fi

# Verificar se o diretório existe
if [ ! -d "$PROJECT_PATH" ]; then
    echo "ERRO: Diretório do projeto não encontrado: $PROJECT_PATH"
    echo "Por favor, altere a variável PROJECT_PATH no script."
    exit 1
fi

# Mudar para o diretório do projeto
cd "$PROJECT_PATH" || { echo "Erro ao acessar o diretório do projeto!"; exit 1; }

# Verificar se é um projeto Laravel
if [ ! -f "artisan" ]; then
    echo "ERRO: Este não parece ser um diretório de projeto Laravel (arquivo 'artisan' não encontrado)."
    exit 1
fi

# Verificar dependências
command -v php >/dev/null 2>&1 || { echo "ERRO: PHP não está instalado ou não está no PATH."; exit 1; }
command -v npm >/dev/null 2>&1 || { echo "ERRO: NPM não está instalado ou não está no PATH."; exit 1; }

echo "Iniciando serviços Laravel em novas abas..."
echo "Diretório do projeto: $PROJECT_PATH"

# Detectar o terminal disponível e executar comandos apropriados
if command -v konsole >/dev/null 2>&1; then
    echo "Usando Konsole..."
    konsole --new-tab -p "tabtitle=NPM Build" -e bash -c "cd '$PROJECT_PATH' && npm run build; exec bash" &
    sleep 1
    konsole --new-tab -p "tabtitle=Artisan Serve" -e bash -c "cd '$PROJECT_PATH' && php artisan serve; exec bash" &
    sleep 1
    konsole --new-tab -p "tabtitle=Queue Worker" -e bash -c "cd '$PROJECT_PATH' && php artisan queue:work; exec bash" &

elif command -v gnome-terminal >/dev/null 2>&1; then
    echo "Usando GNOME Terminal..."
    gnome-terminal --tab --title="NPM Build" -- bash -c "cd '$PROJECT_PATH' && npm run build; exec bash" &
    sleep 1
    gnome-terminal --tab --title="npm q" -- bash -c "cd '$PROJECT_PATH' && npm run build; exec bash" &
    sleep 1
    gnome-terminal --tab --title="Artisan Serve" -- bash -c "cd '$PROJECT_PATH' && php artisan serve; exec bash" &
    sleep 1
    gnome-terminal --tab --title="Queue Worker" -- bash -c "cd '$PROJECT_PATH' && php artisan queue:work; exec bash" &

elif command -v xfce4-terminal >/dev/null 2>&1; then
    echo "Usando XFCE Terminal..."
    xfce4-terminal --tab --title="NPM Build" --command="bash -c 'cd \"$PROJECT_PATH\" && npm run build; exec bash'" &
    sleep 1
    xfce4-terminal --tab --title="Artisan Serve" --command="bash -c 'cd \"$PROJECT_PATH\" && php artisan serve; exec bash'" &
    sleep 1
    xfce4-terminal --tab --title="Queue Worker" --command="bash -c 'cd \"$PROJECT_PATH\" && php artisan queue:work; exec bash'" &

else
    echo "ERRO: Nenhum terminal compatível encontrado (konsole, gnome-terminal, xfce4-terminal)."
    echo "Executando comandos em segundo plano no terminal atual..."

    # Fallback: executar em segundo plano no terminal atual
    echo "Iniciando NPM Watch..."
    npm run build &
    NPM_PID=$!

    echo "Iniciando Artisan Serve..."
    php artisan serve &
    SERVE_PID=$!

    echo "Iniciando Queue Worker..."
    php artisan queue:work &
    QUEUE_PID=$!

    echo "Processos iniciados:"
    echo "NPM Build PID: $NPM_PID"
    echo "Artisan Serve PID: $SERVE_PID"
    echo "Queue Worker PID: $QUEUE_PID"
    echo ""
    echo "Para parar os serviços, use:"
    echo "kill $NPM_PID $SERVE_PID $QUEUE_PID"

    # Não abrir Tinker automaticamente no fallback
    echo "Para abrir o Tinker, execute: php artisan tinker"
fi

# Aguardar um momento para que as abas sejam abertas
sleep 2

echo ""
echo "✅ Script finalizado!"
echo "Verifique as abas do seu terminal para os serviços Laravel."
echo ""
echo "Serviços iniciados:"
echo "  • NPM Watch (compilação de assets)"
echo "  • Artisan Serve (servidor de desenvolvimento)"
echo "  • Queue Worker (processamento de filas)"
echo "  • Artisan Tinker (REPL do Laravel)"
echo ""
echo "Pressione qualquer tecla para finalizar..."
read -n 1
