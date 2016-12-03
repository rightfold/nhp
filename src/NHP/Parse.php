<?php
namespace NHP;

final class Parse {
    private function __construct() { }

    public static function parseDefinition(Lexer $lexer): AST\Definition {
        switch ($lexer->peek()[0]) {
        case Lexer::VAL_TYPE: return self::parseVariableDefinition($lexer);
        default: throw new \Exception('parse error');
        }
    }

    public static function parseVariableDefinition(Lexer $lexer): AST\VariableDefinition {
        self::expect($lexer, Lexer::VAL_TYPE);
        $name = self::expect($lexer, Lexer::IDENTIFIER_TYPE);
        self::expect($lexer, Lexer::EQUALS_SIGN_TYPE);
        $value = self::parseExpression($lexer);
        self::expect($lexer, Lexer::SEMICOLON_TYPE);
        return new AST\VariableDefinition($name, $value);
    }

    public static function parseExpression(Lexer $lexer): AST\Expression {
        switch ($lexer->peek()[0]) {
        case Lexer::FLOAT_LITERAL_TYPE: return self::parseFloatLiteralExpression($lexer);
        default: throw new \Exception('parse error');
        }
    }

    public static function parseFloatLiteralExpression(Lexer $lexer): AST\FloatLiteralExpression {
        $value = self::expect($lexer, Lexer::FLOAT_LITERAL_TYPE);
        return new AST\FloatLiteralExpression($value);
    }

    private static function expect(Lexer $lexer, int $type) {
        $token = $lexer->read();
        if ($token[0] !== $type) {
            throw new \Exception('parse error');
        }
        return $token[1];
    }
}
