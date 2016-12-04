<?php
namespace NHP;

final class Analyzer {
    private $scope;
    private $values;

    public function __construct(Scope $scope, array $values) {
        $this->scope = $scope;
        $this->values = $values;
    }

    public function withScope(Scope $scope) {
        return new self($scope, $this->values);
    }

    public function analyzeDefinition(AST\Definition $definition): void {
        if ($definition instanceof AST\VariableDefinition) {
            $definition->setScope($this->scope);
            $this->withScope(Scope::localScope())->analyzeExpression($definition->value());
            $this->values[$definition->name()] = new Thing($this->scope, Thing::VARIABLE_TYPE);
        } elseif ($definition instanceof AST\FunctionDefinition) {
            $definition->setScope($this->scope);
            $thing = new Thing($this->scope, Thing::FUNCTION_TYPE);
            $this->values[$definition->name()] = $thing;
            $this->withScope(Scope::localScope())->analyzeExpression($definition->body());
        } else {
            assert(false);
        }
    }

    public function analyzeExpression(AST\Expression $expression): void {
        if ($expression instanceof AST\VariableExpression) {
            if (!array_key_exists($expression->name(), $this->values)) {
                throw new \Exception('no such variable');
            }
            $expression->setThing($this->values[$expression->name()]);
        } elseif ($expression instanceof AST\FloatLiteralExpression) {
        } elseif ($expression instanceof AST\BlockExpression) {
            foreach ($expression->statements() as $statement) {
                if ($statement instanceof AST\Definition) {
                    $this->analyzeDefinition($statement);
                } elseif ($statement instanceof AST\Expression) {
                    $this->analyzeExpression($statement);
                } else {
                    assert(false);
                }
            }
        } else {
            assert(false);
        }
    }
}
