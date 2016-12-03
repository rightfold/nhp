<?php
namespace NHP\AST;
use NHP\Thing;

final class VariableDefinition extends Definition {
    private $name;
    private $value;
    private $thing;

    public function __construct(string $name, Expression $value) {
        $this->name = $name;
        $this->value = $value;
        $this->thing = null;
    }

    public function name(): string {
        return $this->name;
    }

    public function value(): Expression {
        return $this->value;
    }

    public function thing(): ?Thing {
        return $this->thing;
    }

    public function setThing(?Thing $thing): void {
        $this->thing = $thing;
    }
}
