// Script completo para corrigir todos os problemas
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM carregado. Inicializando correções para os placeholders.');

    // 1. Substituir a função geradora de botões
    window.gerarBotoesPlaceholders = function(colunasSelecionadas) {
        console.log('Nova função gerarBotoesPlaceholders executada');
        const container = document.getElementById('botoesPlaceholders');
        container.innerHTML = '';

        colunasSelecionadas.forEach(function(coluna) {
            console.log('Criando botão para coluna:', coluna);

            // Criar o elemento do botão
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-outline-primary btn-sm me-2 mb-2';

            // Exibir apenas o nome da coluna (sem as chaves)
            btn.textContent = coluna;

            // Cria uma closure com o valor da coluna
            const colunaCapturada = coluna;

            // Adicionar evento de clique usando uma closure
            btn.addEventListener('click', function() {
                const placeholder = '{{' + colunaCapturada + '}}';
                console.log('Inserindo placeholder:', placeholder);

                // Inserir no textarea
                const textarea = document.getElementById('messageTextarea');
                const start = textarea.selectionStart;
                const end = textarea.selectionEnd;
                const value = textarea.value;

                textarea.value = value.substring(0, start) + placeholder + value.substring(end);
                textarea.selectionStart = textarea.selectionEnd = start + placeholder.length;
                textarea.focus();

                // Disparar evento de input para atualizar prévia
                const inputEvent = new Event('input', { bubbles: true });
                textarea.dispatchEvent(inputEvent);
            });

            container.appendChild(btn);
        });
    };

    // 2. Substituir a função de prévia
    window.gerarPrevia = function(colunasSelecionadas) {
        console.log('Nova função gerarPrevia executada');
        const selector = document.getElementById('previewSelector');
        selector.innerHTML = '<option selected>Selecione um registro para visualizar</option>';

        previewData.forEach((row, index) => {
            const opt = document.createElement('option');
            opt.value = index;
            opt.textContent = `Registro ${index + 1}`;
            selector.appendChild(opt);
        });

        // Função para atualizar a prévia
        function atualizarPrevia() {
            const idx = parseInt(selector.value);
            if (isNaN(idx)) return;

            const row = previewData[idx];
            let msg = document.getElementById('messageTextarea').value;
            console.log('Atualizando prévia com mensagem:', msg);

            // Para cada coluna selecionada, substituir placeholders
            colunasSelecionadas.forEach(coluna => {
                const colunaIndex = colunasDisponiveis.indexOf(coluna);
                // Obter o valor real da coluna para esta linha
                const valorReal = colunaIndex >= 0 && row[colunaIndex] !== undefined ? row[colunaIndex] : '';
                console.log(`Substituindo {{${coluna}}} por "${valorReal}"`);

                // Substituir o placeholder pelo valor real
                const regex = new RegExp('\\{\\{' + coluna + '\\}\\}', 'g');
                msg = msg.replace(regex, valorReal);
            });

            console.log('Mensagem processada:', msg);
            document.getElementById('messagePreview').innerHTML = '<p>' + msg + '</p>';
        }

        // Adicionar evento de change para o selector
        selector.addEventListener('change', atualizarPrevia);

        // Adicionar evento de input para o textarea para atualizar a prévia em tempo real
        document.getElementById('messageTextarea').addEventListener('input', atualizarPrevia);

        // Executar uma vez para inicializar
        setTimeout(atualizarPrevia, 500);
    };

    // 3. Modificar o comportamento do botão Confirmar Seleção
    const btnConfirmarSelecao = document.getElementById('btnConfirmarSelecao');
    if (btnConfirmarSelecao) {
        const originalOnClick = btnConfirmarSelecao.onclick;

        btnConfirmarSelecao.onclick = function(event) {
            console.log('Botão Confirmar Seleção clicado - interceptado para garantir funções corretas');

            // Executar comportamento original
            if (typeof originalOnClick === 'function') {
                originalOnClick.call(this, event);
            }

            // Garantir que o textarea tenha o evento de input adicionado para atualização automática
            setTimeout(function() {
                const textarea = document.getElementById('messageTextarea');
                if (textarea) {
                    textarea.addEventListener('input', function() {
                        console.log('Textarea alterado, atualizando prévia');
                        const event = new Event('change');
                        document.getElementById('previewSelector').dispatchEvent(event);
                    });
                }
            }, 1000);
        };
    }

    console.log('Correções de placeholders inicializadas com sucesso.');
});
