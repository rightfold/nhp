<?php
namespace NHP;
use NHP\AST;
use PhpParser\Node\{Expr, Name, Scalar, Stmt};

final class CodeGen {
    private function __construct() { }

    public static function codeGenDefinitionToStmts(AST\Definition $definition): array {
        if ($definition instanceof AST\VariableDefinition) {
            return self::codeGenExpressionToStmts($definition->value(), function($result) use($definition) {
                return [new Expr\Assign(new Expr\Variable($definition->name()), $result)];
            });
        } elseif ($definition instanceof AST\FunctionDefinition) {
            return [new Stmt\Function_(
                $definition->name(),
                ['stmts' => self::codeGenExpressionToStmts($definition->body(), function($result) {
                    return [new Stmt\Return_($result)];
                })]
            )];
        } else {
            assert(false);
        }
    }

    public static function codeGenExpressionToStmts(AST\Expression $expression, callable $withResult): array {
        if ($expression instanceof AST\VariableExpression) {
            return $withResult(new Expr\Variable($expression->name()));
        } elseif ($expression instanceof AST\FloatLiteralExpression) {
            return $withResult(new Scalar\DNumber($expression->value()));
        } elseif ($expression instanceof AST\BlockExpression) {
            $statements = $expression->statements();
            $count = count($statements);
            if ($count === 0) {
                return $withResult(new Expr\ConstFetch(new Name('null')));
            } else {
                $stmts = [];
                foreach ($statements as $i => $statement) {
                    if ($statement instanceof AST\Definition) {
                        $stmts = array_merge($stmts, self::codeGenDefinitionToStmts($statement));
                        if ($i === $count - 1) {
                            $stmts = array_merge($stmts, $withResult(new Expr\ConstFetch(new Name('null'))));
                        }
                    } elseif ($statement instanceof AST\Expression) {
                        $localWithResult = $i === $count - 1 ? $withResult : function($result) { return [$result]; };
                        $stmts = array_merge($stmts, self::codeGenExpressionToStmts($statement, $localWithResult));
                    }
                }
                return $stmts;
            }
        } else {
            assert(false);
        }
    }
}
