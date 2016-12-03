<?php
namespace NHP\AST;
use NHP\Thing;

final class VariableExpression extends Expression {
    private $name;
    private $thing;

    public function __construct(string $name) {
        $this->name = $name;
        $this->scope = null;
    }

    public function name(): string {
        return $this->name;
    }

    public function thing(): ?Thing {
        return $this->thing;
    }

    public function setThing(?Thing $thing): void {
        $this->thing = $thing;
    }
}
