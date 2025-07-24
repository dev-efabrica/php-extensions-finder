<?php

namespace ExtensionsFinder;

use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use Symfony\Component\Finder\Finder;

class ExtensionsFinder
{
    public function find(array $dirs): array
    {
        $parser = (new ParserFactory())->createForHostVersion();
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());
        $requiredExtensions = [];
        foreach (Finder::create()->files()->name('*.php')->in($dirs) as $file) {
            $fileName = (string) $file;
            $nodes = $parser->parse(file_get_contents($fileName));

            $finderVisitor = new ExtensionsFinderVisitor();
            $traverser->addVisitor($finderVisitor);

            $traverser->traverse($nodes);

            foreach($finderVisitor->getExtensions() as $extension => $usage) {
                $requiredExtensions[$extension][$fileName] = $usage;
            }
        }
        return $requiredExtensions;
    }
}
