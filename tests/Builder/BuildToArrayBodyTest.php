<?php
/**
 * This file is part of prolic/fpp.
 * (c) 2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FppTest\Builder;

use Fpp\Argument;
use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;
use Fpp\DefinitionType;
use Fpp\Deriving;
use PHPUnit\Framework\TestCase;
use function Fpp\Builder\buildToArrayBody;

class BuildToArrayBodyTest extends TestCase
{
    /**
     * @test
     */
    public function it_builds_to_array_body()
    {
        $userId = new Definition(
            DefinitionType::data(),
            'My',
            'UserId',
            [
                new Constructor('My\UserId'),
            ],
            [
                new Deriving\Uuid(),
            ]
        );

        $email = new Definition(
            DefinitionType::data(),
            'Some',
            'Email',
            [
                new Constructor('String'),
            ],
            [
                new Deriving\FromString(),
                new Deriving\ToString(),
            ]
        );

        $constructor = new Constructor('My\Person', [
            new Argument('id', 'My\UserId'),
            new Argument('name', 'string', true),
            new Argument('email', 'Some\Email'),
            new Argument('secondaryEmails', 'Some\Email', false, true),
            new Argument('nickNames', 'string', false, true),
        ]);

        $definition = new Definition(
            DefinitionType::data(),
            'My',
            'Person',
            [$constructor],
            [
                new Deriving\ToArray(),
            ]
        );

        $expected = <<<CODE
\$secondaryEmails = [];

        foreach (\$this->secondaryEmails as \$__value) {
            \$secondaryEmails[] = \$__value->toString();
        }

        return [
            'id' => \$this->id->toString(),
            'name' => \$this->name,
            'email' => \$this->email->toString(),
            'secondaryEmails' => \$secondaryEmails,
            'nickNames' => \$this->nickNames,
        ];

CODE;

        $this->assertSame($expected, buildToArrayBody($definition, $constructor, new DefinitionCollection($definition, $userId, $email), ''));
    }
}
