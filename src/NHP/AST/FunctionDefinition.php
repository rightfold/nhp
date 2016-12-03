<?php
namespace NHP\AST;

final class FunctionDefinition extends Definition {
    private $name;
    private $body;

    public function __construct(string $name, Expression $body) {
        $this->name = $name;
        $this->body = $body;
    }

    public function name(): string {
        return $this->name;
    }

    public function body(): Expression {
        return $this->body;
    }
}
