<?php

$finder = (new PhpCsFixer\Finder())
    ->notName([
        '_ide_helper_actions.php',
        '_ide_helper_models.php',
        '_ide_helper.php',
        '.phpstorm.meta.php',
        '*.blade.php',
    ])
    ->exclude([
        'bootstrap/cache',
        'build',
        'node_modules',
        'storage',
    ])
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new PhpCsFixer\Config())
    ->setFinder($finder)
    ->setRules([
        '@PER' => true,
        'no_unused_imports' => true,
    ])
    ->setRiskyAllowed(true)
    ->setusingCache(true);