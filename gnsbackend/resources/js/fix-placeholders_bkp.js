// Script completo para corrigir todos os problemas (com limpeza de quebras de linha)
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM carregado. Inicializando correções para os placeholders.');

    // 1. Substituir a função geradora de botões
    window.gerarBotoesPlaceholders = function(colunasSelecionadas) {
        console.log('Nova função gerarBotoesPlaceholders executada');
        const container = document.getElementById('botoesPlaceholders');
        container.innerHTML = '';

        colunasSelecionadas.forEach(function(coluna) {
            console.log('Criando botão para coluna:', coluna);

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-outline-primary btn-sm me-2 mb-2';
            btn.textContent = coluna;

            const colunaCapturada = coluna;
            btn.addEventListener('click', function() {
                const placeholder = '{{' + colunaCapturada + '}}';
                console.log('Inserindo placeholder:', placeholder);

                const textarea = document.getElementById('messageTextarea');
                const start = textarea.selectionStart;
                const end = textarea.selectionEnd;
                const value = textarea.value;

                textarea.value = value.substring(0, start) + placeholder + value.substring(end);
                textarea.selectionStart = textarea.selectionEnd = start + placeholder.length;
                textarea.focus();

                const inputEvent = new Event('input', { bubbles: true });
                textarea.dispatchEvent(inputEvent);
            });

            container.appendChild(btn);
        });
    };

    // 2. Substituir a função de prévia
    window.gerarPrevia = function(colunasSelecionadas) {
        console.log('Nova função gerarPrevia executada (sem selector)');

        // Oculta o seletor (se existir no HTML)
        const selector = document.getElementById('previewSelector');
        if (selector) {
            selector.style.display = 'none';
        }

        // Função para atualizar a prévia usando SEMPRE o primeiro registro
        function atualizarPrevia() {
            const row = previewData[0]; // Sempre o primeiro registro
            if (!row) {
                console.warn('Nenhum dado disponível para a prévia');
                return;
            }

            let msg = document.getElementById('messageTextarea').value;
            console.log('Atualizando prévia com mensagem:', msg);

            colunasSelecionadas.forEach(coluna => {
                const colunaIndex = colunasDisponiveis.indexOf(coluna);
                let valorReal = '';
                if (colunaIndex >= 0 && row[colunaIndex] !== undefined) {
                    valorReal = row[colunaIndex]
                        .toString()
                        .replace(/[\r\n]+/g, '')
                        .trim();
                }

                console.log(`Substituindo {{${coluna}}} por "${valorReal}"`);

                const regex = new RegExp('\\{\\{' + coluna + '\\}\\}', 'g');
                msg = msg.replace(regex, valorReal);
            });

            console.log('Mensagem processada:', msg);
            document.getElementById('messagePreview').innerHTML = '<p>' + msg + '</p>';
        }

        // Atualiza a prévia sempre que o textarea for alterado
        document.getElementById('messageTextarea').addEventListener('input', atualizarPrevia);

        // Executa a atualização uma vez ao carregar
        atualizarPrevia();
    };


    // 3. Modificar o comportamento do botão Confirmar Seleção
    const btnConfirmarSelecao = document.getElementById('btnConfirmarSelecao');
    if (btnConfirmarSelecao) {
        const originalOnClick = btnConfirmarSelecao.onclick;

        btnConfirmarSelecao.onclick = function(event) {
            console.log('Botão Confirmar Seleção clicado - interceptado para garantir funções corretas');

            if (typeof originalOnClick === 'function') {
                originalOnClick.call(this, event);
            }

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
