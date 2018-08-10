<?php
/**
 * This file is part of prolic/fpp.
 * (c) 2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fpp;

const replace = '\Fpp\replace';

function replace(
    string $template,
    Definition $definition,
    $constructor,
    DefinitionCollection $collection,
    array $builders = null
): string {
    if (null === $builders) {
        $builders = defaultBuilders();
    }

    foreach ($builders as $placeHolder => $builder) {
        $template = \str_replace('{{' . $placeHolder . '}}', $builder($definition, $constructor, $collection, '{{' . $placeHolder . '}}'), $template);
    }

    foreach ($builders as $placeHolder => $builder) {
        $template = \str_replace("\n        {{{$placeHolder}}}\n", "\n", $template);
        $template = \str_replace("\n    {{{$placeHolder}}}\n\n", "\n", $template);
        $template = \str_replace("\n    {{{$placeHolder}}}\n", "\n", $template);
        $template = \str_replace("\n{{{$placeHolder}}}\n", "\n", $template);
    }

    // clean up
    $template = \str_replace("\n    }\n    ", "\n    }\n\n    ", $template);
    $template = \str_replace("\n\n\n", "\n\n", $template);
    $template = \str_replace("\n    \n", "\n", $template);
    $template = \str_replace("\n\n}\n}", "\n}\n}", $template);
    $template = \str_replace("\n\n    }\n", "\n    }\n", $template);
    $template = \str_replace("    }\n\n}\n", "    }\n}\n", $template);

    return $template . "\n";
}
