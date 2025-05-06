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


    /*window.gerarPrevia = function(colunasSelecionadas) {
        console.log('Nova função gerarPrevia executada');
        const selector = document.getElementById('previewSelector');
        selector.innerHTML = '<option selected disabled>Selecione um registro para visualizar</option>';

        previewData.forEach((row, index) => {
            const opt = document.createElement('option');
            opt.value = index;
            opt.textContent = `Registro ${index + 1}`;
            selector.appendChild(opt);
        });

        function atualizarPrevia() {
            const idx = parseInt(selector.value);
            if (isNaN(idx)) return;

            const row = previewData[idx];
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

        selector.addEventListener('change', atualizarPrevia);
        document.getElementById('messageTextarea').addEventListener('input', atualizarPrevia);

        // Selecionar o primeiro registro automaticamente
        if (previewData.length > 0) {
            selector.value = 0; // Seleciona o primeiro registro
            atualizarPrevia();  // Atualiza a prévia imediatamente
        }
    };
*/

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




/*// Script completo para corrigir todos os problemas
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

            btn.addEventListener('click', function() {
                const placeholder = '{{' + coluna + '}}';
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
        console.log('Nova função gerarPrevia executada');
        const selector = document.getElementById('previewSelector');
        selector.innerHTML = '<option selected>Selecione um registro para visualizar</option>';

        previewData.forEach((row, index) => {
            const opt = document.createElement('option');
            opt.value = index;
            opt.textContent = `Registro ${index + 1}`;
            selector.appendChild(opt);
        });

        // Função wrapper para passar colunasSelecionadas ao atualizarPrevia
        const atualizar = () => atualizarPrevia(colunasSelecionadas);

        // Melhoria 2: remover listener antigo antes de adicionar novo (evita duplicação)
        const textarea = document.getElementById('messageTextarea');
        textarea.removeEventListener('input', atualizar);
        textarea.addEventListener('input', atualizar);

        selector.removeEventListener('change', atualizar);
        selector.addEventListener('change', atualizar);

        // Melhoria 3: selecionar o primeiro registro automaticamente (se existir)
        if (previewData.length > 0) {
            selector.selectedIndex = 1; // Seleciona o primeiro registro (índice 1)
            selector.dispatchEvent(new Event('change'));
        } else {
            atualizar(); // Se não houver registros, apenas atualiza
        }
    };

    // Função segura para atualizar a prévia (sanitiza o texto)
    function atualizarPrevia(colunasSelecionadas) {
        const selector = document.getElementById('previewSelector');
        const idx = parseInt(selector.value);
        if (isNaN(idx)) return;

        const row = previewData[idx];
        let msg = document.getElementById('messageTextarea').value;
        console.log('Atualizando prévia com mensagem:', msg);

        colunasSelecionadas.forEach(coluna => {
            const colunaIndex = colunasDisponiveis.indexOf(coluna);
            //const valorReal = colunaIndex >= 0 && row[colunaIndex] !== undefined ? row[colunaIndex] : '';
            let valorReal = '';
            if (colunaIndex >= 0 && row[colunaIndex] !== undefined) {
                valorReal = row[colunaIndex]
                    .toString()
                    .replace(/[\r\n]+/g, '') // Remove quebras de linha (\r, \n)
                    .trim();                 // Remove espaços extras
            }
            console.log(`Substituindo {{${coluna}}} por "${valorReal}"`);

            const regex = new RegExp('\\{\\{' + coluna + '\\}\\}', 'g');
            msg = msg.replace(regex, valorReal);
        });

        console.log('Mensagem processada:', msg);
        const previewElement = document.getElementById('messagePreview');

        // Escapar o texto e preservar quebras de linha
        const escaped = msg
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\n/g, '<br>');

        previewElement.innerHTML = escaped;
    }

    // 3. Modificar o comportamento do botão Confirmar Seleção
    const btnConfirmarSelecao = document.getElementById('btnConfirmarSelecao');
    if (btnConfirmarSelecao) {
        const originalOnClick = btnConfirmarSelecao.onclick;

        btnConfirmarSelecao.onclick = function(event) {
            console.log('Botão Confirmar Seleção clicado - interceptado para garantir funções corretas');

            if (typeof originalOnClick === 'function') {
                originalOnClick.call(this, event);
            }

            // Garantir que o textarea tenha o evento de input adicionado para atualização automática
            setTimeout(function() {
                const textarea = document.getElementById('messageTextarea');

                // Melhoria 2: evitar múltiplos listeners no textarea após várias confirmações
                const previewSelector = document.getElementById('previewSelector');
                const colunasSelecionadas = Array.from(document.querySelectorAll('input[name="colunas[]"]:checked')).map(el => el.value);
                const atualizar = () => atualizarPrevia(colunasSelecionadas);

                textarea.removeEventListener('input', atualizar);
                textarea.addEventListener('input', atualizar);

                previewSelector.removeEventListener('change', atualizar);
                previewSelector.addEventListener('change', atualizar);
            }, 1000);
        };
    }

    /!*const btnConfirmarSelecao = document.getElementById('btnConfirmarSelecao');
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
    }*!/
    console.log('Correções de placeholders inicializadas com sucesso.');
});*/


/*
// Script completo com ajustes finais — externo e sem conflito com Blade

// Garantir que as variáveis globais existem
window.previewData = window.previewData || [];
window.colunasDisponiveis = window.colunasDisponiveis || [];

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

            // Exibir o próprio placeholder no botão
            btn.textContent = '{{' + coluna + '}}';

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
                const valorReal = colunaIndex >= 0 && row[colunaIndex] !== undefined ? row[colunaIndex] : '';
                console.log(`Substituindo {{${coluna}}} por "${valorReal}"`);

                const regex = new RegExp('\\{\\{' + coluna + '\\}\}', 'g');
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
*/
