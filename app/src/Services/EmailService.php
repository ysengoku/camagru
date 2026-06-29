<?php

final class EmailService {
    private static ?EmailService $instance = null;

    private string $smtpHost;
    private int    $smtpPort;
    private string $smtpUser;
    private string $smtpPass;
    private string $mailFrom;

    /** @var resource $socket */
    private $socket = null;

    private function __construct() {
        // Private constructor to prevent direct instantiation
        $this->smtpHost = $_ENV['SMTP_HOST'] ?? '';
        $this->smtpPort = (int)($_ENV['SMTP_PORT'] ?? 0);
        $this->smtpUser = $_ENV['SMTP_USER'] ?? '';
        $this->smtpPass = $_ENV['SMTP_PASS'] ?? '';
        $this->mailFrom = $_ENV['MAIL_FROM'] ?? '';

        if (
            empty($this->smtpHost)
            || $this->smtpPort === 0
            || empty($this->smtpUser)
            || empty($this->smtpPass)
            || empty($this->mailFrom)
        ) {
            throw new RuntimeException('SMTP configuration is incomplete.');
        }
    }

    public static function getInstance(): EmailService {
        if (self::$instance === null) {
            self::$instance = new EmailService();
        }
        return self::$instance;
    }

    public function send(string $to, string $subject, string $body): void {
        try {
            $this->socket = @fsockopen($this->smtpHost, $this->smtpPort, $errno, $errstr, 5);
            if ($this->socket === false) {
                throw new RuntimeException("Failed to connect to SMTP server: $errstr ($errno)");
            }
            $this->expect(220); // Server welcome

            $this->startTls();
            $this->authenticate();

            $this->sendMail($to, $subject, $body);

            $this->write("QUIT");
            $this->expect(221);
        } finally {
            if (isset($this->socket) && is_resource($this->socket)) {
                fclose($this->socket);
                $this->socket = null;
            }
        }
    }

    private function write(string $data): void {
        fwrite($this->socket, $data . "\r\n");
    }

    private function expect(int $code): void {
        $response = '';
        while ($line = fgets($this->socket, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) == ' ') {
                break;
            }
        }
        if (substr($response, 0, 3) !== (string)$code) {
            throw new Exception("SMTP Error: Expected $code, got: " . $response);
        }
    }

    private function startTls(): void {
        $host = gethostname() ?: 'localhost';
        $this->write("EHLO {$host}");
        $this->expect(250);

        $this->write("STARTTLS");
        $this->expect(220);
        if (!stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            throw new RuntimeException("Failed to enable TLS encryption for SMTP connection.");
        }

        $this->write("EHLO {$host}");
        $this->expect(250);
    }

    private function authenticate(): void {
        $this->write("AUTH LOGIN");
        $this->expect(334); // Server ready for username
        $this->write(base64_encode($this->smtpUser));
        $this->expect(334); // Server ready for password
        $this->write(base64_encode($this->smtpPass));
        $this->expect(235); // Authentication successful
    }

    private function sendMail(string $to, string $subject, string $body): void {
        $this->write("MAIL FROM:<{$this->mailFrom}>");
        $this->expect(250);
        $this->write("RCPT TO:<{$to}>");
        $this->expect(250);
        $this->write("DATA");
        $this->expect(354);

        $headers = [
            "From: <$this->mailFrom>",
            "To: <$to>",
            "Subject: " . str_replace(["\r", "\n"], '', $subject),
            "MIME-Version: 1.0",
            "Content-Type: text/html; charset=UTF-8",
            "Content-Transfer-Encoding: 8bit",
            "" // Empty line separating headers and body
        ];
        $emailData = implode("\r\n", $headers) . "\r\n" . preg_replace('/^\./m', '..', $body);
        $this->write($emailData);
        $this->write(".");
        $this->expect(250);
    }
}
