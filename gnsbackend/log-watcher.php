<?php

/**
 * Laravel Log Watcher - Ferramenta para monitorar logs com filtragem e coloração
 *
 * Use: php log-watcher.php [caminho-do-arquivo-de-log] [filtro-opcional]
 * Exemplo: php log-watcher.php storage/logs/laravel.log error
 */

// Cores para terminal
class Colors
{
    private array $foreground = [
        'black' => '0;30',
        'red' => '0;31',
        'green' => '0;32',
        'yellow' => '0;33',
        'blue' => '0;34',
        'magenta' => '0;35',
        'cyan' => '0;36',
        'white' => '0;37',
        'bright_black' => '1;30',
        'bright_red' => '1;31',
        'bright_green' => '1;32',
        'bright_yellow' => '1;33',
        'bright_blue' => '1;34',
        'bright_magenta' => '1;35',
        'bright_cyan' => '1;36',
        'bright_white' => '1;37',
    ];

    public function apply($color, $text)
    {
        if (!isset($this->foreground[$color])) {
            return $text;
        }

        return "\033[" . $this->foreground[$color] . "m" . $text . "\033[0m";
    }
}

// Configurações padrão
$logFile = $argv[1] ?? 'storage/logs/laravel.log';
$filter = isset($argv[2]) ? strtolower($argv[2]) : null;

// Verificar se o arquivo existe
if (!file_exists($logFile)) {
    echo "Arquivo de log não encontrado: $logFile\n";
    echo "Use: php " . $argv[0] . " [caminho-do-arquivo-de-log] [filtro-opcional]\n";
    exit(1);
}

// Iniciar colorador
$colors = new Colors();

echo $colors->apply('bright_cyan', "Monitorando logs em $logFile" . ($filter ? " (filtrando por '$filter')" : "") . "\n");
echo $colors->apply('bright_cyan', "Pressione Ctrl+C para sair\n\n");

// Lógica para parsear a data
function parseDate($line): string
{
    if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $line, $matches)) {
        return $matches[1];
    }
    return '';
}

// Lógica para determinar o nível do log
function getLogLevel($line): string
{
    if (stripos($line, 'error') !== false || stripos($line, 'exception') !== false) {
        return 'error';
    } elseif (stripos($line, 'warning') !== false) {
        return 'warning';
    } elseif (stripos($line, 'info') !== false) {
        return 'info';
    } elseif (stripos($line, 'debug') !== false) {
        return 'debug';
    } else {
        return 'unknown';
    }
}

// Função para colorir o log baseado no nível
function colorizeLog($line, $colors)
{
    $level = getLogLevel($line);
    $date = parseDate($line);

    // Substituir a data com uma versão colorida
    if ($date) {
        $line = str_replace("[$date]", $colors->apply('bright_blue', "[$date]"), $line);
    }

    return match ($level) {
        'error' => $colors->apply('bright_red', $line),
        'warning' => $colors->apply('bright_yellow', $line),
        'info' => $colors->apply('bright_green', $line),
        'debug' => $colors->apply('bright_magenta', $line),
        default => $colors->apply('white', $line),
    };
}

// Abrir o arquivo para leitura contínua
$file = fopen($logFile, 'r');
if ($file === false) {
    echo "Não foi possível abrir o arquivo: $logFile\n";
    exit(1);
}

// Ir para o final do arquivo
fseek($file, 0, SEEK_END);
$pos = ftell($file);

// Loop infinito para monitorar o arquivo
while (true) {
    clearstatcache(true, $logFile);
    $currentPos = ftell($file);
    $size = filesize($logFile);

    // Se o arquivo foi truncado ou recriado
    if ($size < $currentPos) {
        fclose($file);
        $file = fopen($logFile, 'r');
        fseek($file, 0, SEEK_END);
        $pos = ftell($file);
        continue;
    }

    // Se há novos dados
    if ($size > $pos) {
        fseek($file, $pos);
        while (($line = fgets($file)) !== false) {
            // Aplicar filtro se existir
            if ($filter && stripos(strtolower($line), $filter) === false) {
                continue;
            }

            echo colorizeLog($line, $colors);
        }
        $pos = ftell($file);
    }

    // Esperar um pouco para não sobrecarregar o CPU
    usleep(100000); // 100ms
}

fclose($file);
