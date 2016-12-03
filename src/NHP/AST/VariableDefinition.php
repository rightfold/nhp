<?php
namespace NHP\AST;
use NHP\Scope;

final class VariableDefinition extends Definition {
    private $name;
    private $value;
    private $scope;

    public function __construct(string $name, Expression $value) {
        $this->name = $name;
        $this->value = $value;
        $this->scope = null;
    }

    public function name(): string {
        return $this->name;
    }

    public function value(): Expression {
        return $this->value;
    }

    public function scope(): ?Scope {
        return $this->scope;
    }

    public function setScope(?Scope $scope): void {
        $this->scope = $scope;
    }
}
