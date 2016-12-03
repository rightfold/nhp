<?php
namespace NHP\AST;

final class VariableDefinition extends Definition {
    private $name;
    private $value;

    public function __construct(string $name, Expression $value) {
        $this->name = $name;
        $this->value = $value;
    }

    public function name(): string {
        return $this->name;
    }

    public function value(): Expression {
        return $this->value;
    }
}
