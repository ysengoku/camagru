<?php

final class EmailService {
    use SingletonTrait;

    private string $smtpHost;
    private int    $smtpPort;
    private string $smtpUser;
    private string $smtpPass;
    private string $mailFrom;

    /** @var resource|null $socket */
    private $socket = null;

    private static function envString(string $key, string $fallback = ''): string {
        $value = getenv($key);

        return $value !== false ? $value : $fallback;
    }

    private function __construct() {
        // Private constructor to prevent direct instantiation
        $this->smtpHost = self::envString('SMTP_HOST');
        $this->smtpPort = (int) self::envString('SMTP_PORT', '0');
        $this->smtpUser = self::envString('SMTP_USER');
        $this->smtpPass = self::envString('SMTP_PASS');
        $this->mailFrom = self::envString('MAIL_FROM');

        if (getenv('MAIL_DISABLED') === 'true') {
            return;
        }

        if (
            empty($this->smtpHost) === true
            || $this->smtpPort === 0
            || empty($this->smtpUser) === true
            || empty($this->smtpPass) === true
            || empty($this->mailFrom) === true
        ) {
            throw new RuntimeException('SMTP configuration is incomplete.');
        }
    }

    public function send(string $to, string $subject, string $body): void {
        if (getenv('MAIL_DISABLED') === 'true') {
            return;
        }

        try {
            $socket = @fsockopen($this->smtpHost, $this->smtpPort, $errno, $errstr, 5);
            if ($socket === false) {
                throw new RuntimeException("Failed to connect to SMTP server: $errstr ($errno)");
            }
            $this->socket = $socket;
            $this->expect(220); // Server welcome

            $this->startTls();
            $this->authenticate();

            $this->sendMail($to, $subject, $body);

            $this->write("QUIT");
            $this->expect(221);
        } finally {
            // Socket may still be null here if fsockopen() failed before line 40 ran.
            /** @psalm-suppress RedundantConditionGivenDocblockType */
            if (isset($this->socket) && is_resource($this->socket)) {
                $socket = $this->socket;
                $this->socket = null;
                fclose($socket);
            }
        }
    }

    /**
     * @return resource
     */
    private function getSocket() {
        if ($this->socket === null) {
            throw new \LogicException('SMTP socket is not open; send() must establish a connection first.');
        }

        return $this->socket;
    }

    private function write(string $data): void {
        fwrite($this->getSocket(), $data . "\r\n");
    }

    private function expect(int $code): void {
        $response = '';
        while ($line = fgets($this->getSocket(), 515)) {
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
        $hostname = gethostname();
        $host = $hostname !== false ? $hostname : 'localhost';
        $this->write("EHLO {$host}");
        $this->expect(250);

        $this->write("STARTTLS");
        $this->expect(220);
        if (stream_socket_enable_crypto($this->getSocket(), true, STREAM_CRYPTO_METHOD_TLS_CLIENT) !== true) {
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
