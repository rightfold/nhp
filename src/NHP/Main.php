<?php
namespace NHP;
use NHP\AST;
use PhpParser\PrettyPrinter;

final class Main {
    private function __construct() { }

    public static function main(): void {
        $text = <<<'EOF'
val goldenRatio = {
    val pi = 3.14159265359f;
    1.61803398875f;
};

def getGoldenRatio() =
    goldenRatio;
EOF;
        $lexer = new Lexer($text);
        $definitions = [];
        while (($token = $lexer->peek())[0] !== Lexer::EOF_TYPE) {
            $definitions[] = Parse::parseDefinition($lexer);
        }

        $analyzer = new Analyzer(Scope::globalScope(), []);
        foreach ($definitions as $definition) {
            $analyzer->analyzeDefinition($definition);
        }

        $stmts = [];
        foreach ($definitions as $definition) {
            $stmts = array_merge($stmts, CodeGen::codeGenDefinitionToStmts($definition));
        }
        $prettyPrinter = new PrettyPrinter\Standard();
        echo $prettyPrinter->prettyPrintFile($stmts);
    }
}
