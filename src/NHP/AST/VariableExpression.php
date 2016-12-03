<?php
namespace NHP\AST;

final class VariableExpression extends Expression {
    private $name;

    public function __construct(string $name) {
        $this->name = $name;
    }

    public function name(): string {
        return $this->name;
    }
}
