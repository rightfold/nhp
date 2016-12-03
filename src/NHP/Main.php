<?php
namespace NHP;
use NHP\AST;
use PhpParser\PrettyPrinter;

final class Main {
    private function __construct() { }

    public static function main(): void {
        $definitions = [
            new AST\VariableDefinition(
                'goldenRatio',
                new AST\FloatLiteralExpression(1.61803398875)
            ),
            new AST\FunctionDefinition(
                'getGoldenRatio',
                new AST\BlockExpression([
                    new AST\VariableExpression('goldenRatio'),
                ])
            ),
        ];
        $stmts = [];
        foreach ($definitions as $definition) {
            $stmts = array_merge($stmts, CodeGen::codeGenDefinitionToStmts($definition));
        }
        $prettyPrinter = new PrettyPrinter\Standard();
        echo $prettyPrinter->prettyPrintFile($stmts);
    }
}
