<?php
/**
 * app_fie/services/Logger.php
 * Logger minimal PSR-3-like (sans dépendance externe).
 *
 * CORRECTIONS PHASE 1 :
 *   - info/warning/error : acceptent maintenant un $context array optionnel
 *     (ancienne erreur : AuthController passait ['key'=>'val'] en 2e arg → PHP Fatal)
 *   - Sérialisation du contexte en JSON dans la ligne de log
 *   - Création du répertoire logs/ si inexistant (@ pour éviter warning en cas de race condition)
 */

class Logger
{
    private string $channel;
    private string $logDir;

    public function __construct(string $channel = 'app')
    {
        $this->channel = $channel;
        $this->logDir  = defined('FIE_LOGS_PATH') ? FIE_LOGS_PATH : (dirname(__DIR__, 2) . '/logs/');
        if (!is_dir($this->logDir)) {
            @mkdir($this->logDir, 0750, true);
        }
    }

    public function info(string $msg, array $context = []): void
    {
        $this->write('INFO', $msg, $context);
    }

    public function warning(string $msg, array $context = []): void
    {
        $this->write('WARNING', $msg, $context);
    }

    public function error(string $msg, array $context = []): void
    {
        $this->write('ERROR', $msg, $context);
    }

    public function debug(string $msg, array $context = []): void
    {
        if (defined('FIE_DEBUG') && FIE_DEBUG) {
            $this->write('DEBUG', $msg, $context);
        }
    }

    private function write(string $level, string $msg, array $context = []): void
    {
        $ctx  = $context ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '';
        $line = sprintf(
            "[%s] [%s] [%s] %s%s\n",
            date('Y-m-d H:i:s'),
            $level,
            $this->channel,
            $msg,
            $ctx
        );
        $file = $this->logDir . $this->channel . '_' . date('Y-m-d') . '.log';
        @file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }
}
