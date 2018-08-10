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
use function Fpp\Builder\buildFromStringBody;

class BuildFromStringBodyTest extends TestCase
{
    /**
     * @test
     */
    public function it_builds_from_string_body()
    {
        $userId = new Definition(
            DefinitionType::data(),
            'My',
            'UserId',
            [
                $constructor = new Constructor('My\UserId', [
                    new Argument('id', 'string'),
                ]),
            ],
            [
                new Deriving\FromString(),
            ]
        );

        $expected = <<<CODE
return new self(\$id);

CODE;

        $this->assertSame($expected, buildFromStringBody($userId, $constructor, new DefinitionCollection($userId), ''));
    }

    /**
     * @test
     */
    public function it_builds_from_scalar_body_from_scalar_constructor()
    {
        $userId = new Definition(
            DefinitionType::data(),
            'My',
            'UserId',
            [
                $constructor = new Constructor('String'),
            ],
            [
                new Deriving\FromString(),
            ]
        );

        $expected = <<<CODE
return new self(\$userId);

CODE;

        $this->assertSame($expected, buildFromStringBody($userId, $constructor, new DefinitionCollection($userId), ''));
    }

    /**
     * @test
     */
    public function it_builds_from_string_body_from_string_scalar()
    {
        $userId = new Definition(
            DefinitionType::data(),
            'My',
            'UserId',
            [
                $constructor = new Constructor('My\UserId', [
                    new Argument('id', 'string'),
                ]),
            ],
            [
                new Deriving\FromScalar(),
            ]
        );

        $expected = <<<CODE
return new self(\$id);

CODE;

        $this->assertSame($expected, buildFromStringBody($userId, $constructor, new DefinitionCollection($userId), ''));
    }

    /**
     * @test
     */
    public function it_builds_from_string_body_from_object_of_object()
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

        $user = new Definition(
            DefinitionType::data(),
            'My',
            'User',
            [
                $constructor = new Constructor('My\User', [
                    new Argument('userid', 'My\UserId'),
                ]),
            ],
            [
                new Deriving\FromString(),
            ]
        );

        $expected = <<<CODE
return new self(UserId::fromString(\$userid));

CODE;

        $this->assertSame($expected, buildFromStringBody($user, $constructor, new DefinitionCollection($userId, $user), ''));
    }

    /**
     * @test
     */
    public function it_builds_from_string_body_from_object_of_object_from_another_namespace()
    {
        $userId = new Definition(
            DefinitionType::data(),
            'Your',
            'UserId',
            [
                new Constructor('Your\UserId'),
            ],
            [
                new Deriving\Uuid(),
            ]
        );

        $user = new Definition(
            DefinitionType::data(),
            'My',
            'User',
            [
                $constructor = new Constructor('My\User', [
                    new Argument('userid', 'Your\UserId'),
                ]),
            ],
            [
                new Deriving\FromString(),
            ]
        );

        $expected = <<<CODE
return new self(\Your\UserId::fromString(\$userid));

CODE;

        $this->assertSame($expected, buildFromStringBody($user, $constructor, new DefinitionCollection($userId, $user), ''));
    }

    /**
     * @test
     */
    public function it_builds_from_string_body_from_object_of_object_with_scalar_deriving()
    {
        $userId = new Definition(
            DefinitionType::data(),
            'My',
            'UserId',
            [
                new Constructor('My\UserId', [
                    new Argument('id', 'string'),
                ]),
            ],
            [
                new Deriving\FromScalar(),
            ]
        );

        $user = new Definition(
            DefinitionType::data(),
            'My',
            'User',
            [
                $constructor = new Constructor('My\User', [
                    new Argument('userid', 'My\UserId'),
                ]),
            ],
            [
                new Deriving\FromString(),
            ]
        );

        $expected = <<<CODE
return new self(UserId::fromScalar(\$userid));

CODE;

        $this->assertSame($expected, buildFromStringBody($user, $constructor, new DefinitionCollection($userId, $user), ''));
    }

    /**
     * @test
     */
    public function it_throws_when_no_deriving_given()
    {
        $userId = new Definition(
            DefinitionType::data(),
            'My',
            'UserId',
            [
                new Constructor('My\UserId'),
            ]
        );

        $user = new Definition(
            DefinitionType::data(),
            'My',
            'User',
            [
                $constructor = new Constructor('My\User', [
                    new Argument('userid', 'My\UserId'),
                ]),
            ],
            [
                new Deriving\FromString(),
            ]
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot build fromString for My\User, no needed deriving for My\UserId given');

        buildFromStringBody($user, $constructor, new DefinitionCollection($userId, $user), '');
    }

    /**
     * @test
     */
    public function it_throws_when_unknown_argument_given()
    {
        $user = new Definition(
            DefinitionType::data(),
            'My',
            'User',
            [
                $constructor = new Constructor('My\User', [
                    new Argument('userid', 'My\UserId'),
                ]),
            ],
            [
                new Deriving\FromString(),
            ]
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot build fromString for My\User, unknown argument My\UserId given');

        buildFromStringBody($user, $constructor, new DefinitionCollection($user), '');
    }

    /**
     * @test
     */
    public function it_returns_placeholder_if_no_constructor_given()
    {
        $userId = new Definition(
            DefinitionType::data(),
            'My',
            'UserId',
            [
                new Constructor('My\UserId', [
                    new Argument('id', 'string'),
                ]),
            ],
            [
                new Deriving\FromString(),
            ]
        );

        $expected = 'placeholder';

        $this->assertSame($expected, buildFromStringBody($userId, null, new DefinitionCollection($userId), 'placeholder'));
    }
}
