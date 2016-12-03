<?php
namespace NHP\AST;

final class FloatLiteralExpression extends Expression {
    private $value;

    public function __construct(float $value) {
        $this->value = $value;
    }

    public function value(): float {
        return $this->value;
    }
}
