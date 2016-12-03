<?php
namespace NHP\AST;

final class BlockExpression extends Expression {
    private $statements;

    public function __construct(array $statements) {
        $this->statements = $statements;
    }

    public function statements(): array {
        return $this->statements;
    }
}
