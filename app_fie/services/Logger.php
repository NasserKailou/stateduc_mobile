<?php
/**
 * app_fie/services/Logger.php
 * Logger minimal PSR-3-like (sans dépendance externe).
 */

class Logger
{
    private string $channel;
    private string $logDir;

    public function __construct(string $channel = 'app')
    {
        $this->channel = $channel;
        $this->logDir  = FIE_LOGS_PATH;
        if (!is_dir($this->logDir)) {
            @mkdir($this->logDir, 0750, true);
        }
    }

    public function info(string $msg): void    { $this->write('INFO',    $msg); }
    public function warning(string $msg): void { $this->write('WARNING', $msg); }
    public function error(string $msg): void   { $this->write('ERROR',   $msg); }
    public function debug(string $msg): void   { if (FIE_DEBUG) $this->write('DEBUG', $msg); }

    private function write(string $level, string $msg): void
    {
        $line = sprintf(
            "[%s] [%s] [%s] %s\n",
            date('Y-m-d H:i:s'),
            $level,
            $this->channel,
            $msg
        );
        $file = $this->logDir . $this->channel . '_' . date('Y-m-d') . '.log';
        @file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }
}
