<?php
namespace NHP;

final class Thing {
    private $scope;
    private $type;

    public const VARIABLE_TYPE = 1;
    public const FUNCTION_TYPE = 2;

    public function __construct(Scope $scope, int $type) {
        $this->scope = $scope;
        $this->type = $type;
    }

    public function scope(): Scope {
        return $this->scope;
    }

    public function type(): int {
        return $this->type;
    }
}
