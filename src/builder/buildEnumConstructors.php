<?php
/**
 * This file is part of prolic/fpp.
 * (c) 2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fpp\Builder;

use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;
use function Fpp\buildReferencedClass;

const buildEnumConstructors = '\Fpp\Builder\buildEnumConstructors';

function buildEnumConstructors(Definition $definition, $constructor, DefinitionCollection $collection, string $placeHolder): string
{
    if ($definition->isMarker()) {
        return $placeHolder;
    }

    $replace = '';
    foreach ($definition->constructors() as $constructor2) {
        $class = buildReferencedClass($definition->namespace(), $constructor2->name());
        $method = \lcfirst($class);
        $replace .= "    public static function $method(): self\n";
        $replace .= "    {\n";
        $replace .= "        return new self('$class');\n";
        $replace .= "    }\n\n";
    }

    return \substr($replace, 4, -1);
}
