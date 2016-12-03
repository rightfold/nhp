<?php
namespace NHP;

final class Parse {
    private function __construct() { }

    public static function parseDefinition(Lexer $lexer): AST\Definition {
        switch ($lexer->peek()[0]) {
        case Lexer::VAL_TYPE: return self::parseVariableDefinition($lexer);
        case Lexer::DEF_TYPE: return self::parseFunctionDefinition($lexer);
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

    public static function parseFunctionDefinition(Lexer $lexer): AST\FunctionDefinition {
        self::expect($lexer, Lexer::DEF_TYPE);
        $name = self::expect($lexer, Lexer::IDENTIFIER_TYPE);
        self::expect($lexer, Lexer::LEFT_PARENTHESIS_TYPE);
        self::expect($lexer, Lexer::RIGHT_PARENTHESIS_TYPE);
        self::expect($lexer, Lexer::EQUALS_SIGN_TYPE);
        $value = self::parseExpression($lexer);
        self::expect($lexer, Lexer::SEMICOLON_TYPE);
        return new AST\FunctionDefinition($name, $value);
    }

    public static function parseExpression(Lexer $lexer): AST\Expression {
        switch ($lexer->peek()[0]) {
        case Lexer::FLOAT_LITERAL_TYPE: return self::parseFloatLiteralExpression($lexer);
        case Lexer::LEFT_BRACE_TYPE: return self::parseBlockExpression($lexer);
        default: throw new \Exception('parse error');
        }
    }

    public static function parseFloatLiteralExpression(Lexer $lexer): AST\FloatLiteralExpression {
        $value = self::expect($lexer, Lexer::FLOAT_LITERAL_TYPE);
        return new AST\FloatLiteralExpression($value);
    }

    public static function parseBlockExpression(Lexer $lexer): AST\BlockExpression {
        self::expect($lexer, Lexer::LEFT_BRACE_TYPE);
        $statements = [];
        while (($token = $lexer->peek())[0] !== Lexer::RIGHT_BRACE_TYPE) {
            switch ($token[0]) {
            case Lexer::VAL_TYPE:
            case Lexer::DEF_TYPE:
                $statements[] = self::parseDefinition($lexer);
                break;
            default:
                $statements[] = self::parseExpression($lexer);
                self::expect($lexer, Lexer::SEMICOLON_TYPE);
                break;
            }
        }
        $lexer->read();
        return new AST\BlockExpression($statements);
    }

    private static function expect(Lexer $lexer, int $type) {
        $token = $lexer->read();
        if ($token[0] !== $type) {
            throw new \Exception('parse error');
        }
        return $token[1];
    }
}
