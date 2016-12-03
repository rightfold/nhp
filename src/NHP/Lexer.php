<?php
namespace NHP;

final class Lexer {
    private $text;

    public const EOF_TYPE = -1;

    public const IDENTIFIER_TYPE = 1;

    public const VAL_TYPE = 101;

    public const SEMICOLON_TYPE = 201;
    public const EQUALS_SIGN_TYPE = 202;

    public const FLOAT_LITERAL_TYPE = 301;

    public function __construct(string $text) {
        $this->text = $text;
    }

    public function read() {
        $this->text = preg_replace('/^[ \t\r\n]+/', '', $this->text);

        if ($this->text === '') {
            return [self::EOF_TYPE, null];
        }

        if (preg_match('/^[a-zA-Z_][a-zA-Z_0-9]*/', $this->text, $matches)) {
            $this->text = substr($this->text, strlen($matches[0]));
            switch ($matches[0]) {
            case 'val': return [self::VAL_TYPE, null];
            default: return [self::IDENTIFIER_TYPE, $matches[0]];
            }
        }

        if (preg_match('/^([0-9](\.[0-9]+)?)f/', $this->text, $matches)) {
            $this->text = substr($this->text, strlen($matches[0]));
            return [self::FLOAT_LITERAL_TYPE, (float)$matches[1]];
        }

        if ($this->text[0] === ';') {
            $this->text = substr($this->text, 1);
            return [self::SEMICOLON_TYPE, null];
        }

        if ($this->text[0] === '=') {
            $this->text = substr($this->text, 1);
            return [self::EQUALS_SIGN_TYPE, null];
        }

        throw new \Exception('invalid token at ' . $this->text);
    }

    public function peek() {
        return (new self($this->text))->read();
    }
}
