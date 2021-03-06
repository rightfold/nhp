<?php
namespace NHP;
use NHP\AST;
use PhpParser\Node\{Expr, Name, Scalar, Stmt};
use Traversable;

final class CodeGen {
    private function __construct() { }

    public static function codeGenDefinitionToStmts(AST\Definition $definition): array {
        if ($definition instanceof AST\VariableDefinition) {
            if ($definition->scope() === Scope::globalScope()) {
                return [
                    new Stmt\Class_($definition->name(), [
                        'flags' => Stmt\Class_::MODIFIER_FINAL,
                        'stmts' => [
                            new Stmt\Property(Stmt\Class_::MODIFIER_PRIVATE | Stmt\Class_::MODIFIER_STATIC, [
                                new Stmt\PropertyProperty('initialized', new Expr\ConstFetch(new Name('false'))),
                                new Stmt\PropertyProperty('value'),
                            ]),
                            new Stmt\ClassMethod('__construct', ['flags' => Stmt\Class_::MODIFIER_PRIVATE]),
                            new Stmt\ClassMethod('initialize', [
                                'flags' => Stmt\Class_::MODIFIER_PUBLIC | Stmt\Class_::MODIFIER_STATIC,
                                'stmts' => [new Stmt\If_(
                                    new Expr\BooleanNot(new Expr\StaticPropertyFetch(new Name('self'), 'initialized')),
                                    [
                                        'stmts' => array_merge(
                                            [new Expr\Assign(
                                                new Expr\StaticPropertyFetch(new Name('self'), 'initialized'),
                                                new Expr\ConstFetch(new Name('true'))
                                            )],
                                            self::codeGenExpressionToStmts($definition->value(), function($result) {
                                                return [new Expr\Assign(new Expr\StaticPropertyFetch(new Name('self'), 'value'), $result)];
                                            })
                                        ),
                                    ]
                                )],
                            ]),
                            new Stmt\ClassMethod('value', [
                                'flags' => Stmt\Class_::MODIFIER_PUBLIC | Stmt\Class_::MODIFIER_STATIC,
                                'stmts' => [new Stmt\Return_(new Expr\StaticPropertyFetch(new Name('self'), 'value'))],
                            ])
                        ],
                    ]),
                    new Expr\StaticCall(new Name($definition->name()), 'initialize'),
                ];
            } elseif ($definition->scope() === Scope::localScope()) {
                return self::codeGenExpressionToStmts($definition->value(), function($result) use($definition) {
                    return [new Expr\Assign(new Expr\Variable($definition->name()), $result)];
                });
            } else {
                assert(false);
            }
        } elseif ($definition instanceof AST\FunctionDefinition) {
            $stmts = self::codeGenExpressionToStmts($definition->body(), function($result) {
                return [new Stmt\Return_($result)];
            });
            if ($definition->scope() === Scope::globalScope()) {
                return [new Stmt\Function_(
                    $definition->name(),
                    ['stmts' => $stmts]
                )];
            } elseif ($definition->scope() === Scope::localScope()) {
                return [new Expr\Assign(
                    new Expr\Variable($definition->name()),
                    new Expr\Closure([
                        'stmts' => $stmts,
                        'uses' => array_map(function($free) {
                            return new Expr\ClosureUse($free, true);
                        }, iterator_to_array(self::freeVariablesInExpression($definition->body()))),
                    ])
                )];
            } else {
                assert(false);
            }
        } else {
            assert(false);
        }
    }

    public static function codeGenExpressionToStmts(AST\Expression $expression, callable $withResult): array {
        if ($expression instanceof AST\VariableExpression) {
            if ($expression->thing()->type() === Thing::VARIABLE_TYPE) {
                if ($expression->thing()->scope() === Scope::globalScope()) {
                    return $withResult(new Expr\StaticCall(new Name($expression->name()), 'value'));
                } elseif ($expression->thing()->scope() === Scope::localScope()) {
                    return $withResult(new Expr\Variable($expression->name()));
                } else {
                    assert(false);
                }
            } elseif ($expression->thing()->type() === Thing::FUNCTION_TYPE) {
                if ($expression->thing()->scope() === Scope::globalScope()) {
                    return $withResult(new Scalar\String_($expression->name()));
                } elseif ($expression->thing()->scope() === Scope::localScope()) {
                    return $withResult(new Expr\Variable($expression->name()));
                } else {
                    assert(false);
                }
            } else {
                assert(false);
            }
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

    private static function freeVariablesInDefinition(AST\Definition $definition, array &$bound): Traversable {
        if ($definition instanceof AST\VariableDefinition) {
            foreach (self::freeVariablesInExpression($definition->value()) as $free) {
                if (!in_array($free, $bound)) {
                    yield $free;
                }
            }
            $bound[] = $definition->name();
        } elseif ($definition instanceof AST\FunctionDefinition) {
            $bound[] = $definition->name();
            foreach (self::freeVariablesInExpression($definition->body()) as $free) {
                if (!in_array($free, $bound)) {
                    yield $free;
                }
            }
        } else {
            assert(false);
        }
    }

    private static function freeVariablesInExpression(AST\Expression $expression): Traversable {
        if ($expression instanceof AST\VariableExpression) {
            yield $expression->name();
        } elseif ($expression instanceof AST\FloatLiteralExpression) {
        } elseif ($expression instanceof AST\BlockExpression) {
            $bound = [];
            foreach ($expression->statements() as $statement) {
                if ($statement instanceof AST\Definition) {
                    yield from self::freeVariablesInDefinition($statement, $bound);
                } elseif ($statement instanceof AST\Expression) {
                    foreach (self::freeVariablesInExpression($statement) as $free) {
                        if (!in_array($free, $bound)) {
                            yield $free;
                        }
                    }
                } else {
                    assert(false);
                }
            }
        } else {
            assert(false);
        }
    }
}
