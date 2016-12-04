<?php
namespace NHP\AST;
use NHP\Scope;

final class FunctionDefinition extends Definition {
    private $name;
    private $body;
    private $scope;

    public function __construct(string $name, Expression $body) {
        $this->name = $name;
        $this->body = $body;
        $this->scope = null;
    }

    public function name(): string {
        return $this->name;
    }

    public function body(): Expression {
        return $this->body;
    }

    public function scope(): ?Scope {
        return $this->scope;
    }

    public function setScope(?Scope $scope): void {
        $this->scope = $scope;
    }
}
