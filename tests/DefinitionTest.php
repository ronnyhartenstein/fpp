<?php
/**
 * This file is part of prolic/fpp.
 * (c) 2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FppTest;

use Fpp\Argument;
use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionType;
use Fpp\Deriving;
use Fpp\InvalidDeriving;
use PHPUnit\Framework\TestCase;

class DefinitionTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_simple_data_defintion()
    {
        $definition = new Definition(DefinitionType::data(), 'Foo', 'Person', [new Constructor('Foo\Person')]);

        $this->assertSame('Foo', $definition->namespace());
        $this->assertSame('Person', $definition->name());
        $this->assertNull($definition->messageName());
    }

    /**
     * @test
     */
    public function it_requires_defintion_namespace()
    {
        $this->expectException(\InvalidArgumentException::class);

        new Definition(DefinitionType::data(), '', 'Foo');
    }

    /**
     * @test
     */
    public function it_requires_defintion_name()
    {
        $this->expectException(\InvalidArgumentException::class);

        new Definition(DefinitionType::data(), 'Foo', '');
    }

    /**
     * @test
     */
    public function it_creates_data_defintion_with_namespace()
    {
        $definition = new Definition(DefinitionType::data(), 'Foo', 'Person', [new Constructor('Foo\Person')]);

        $this->assertSame('Foo', $definition->namespace());
        $this->assertSame('Person', $definition->name());
    }

    /**
     * @test
     */
    public function it_creates_data_defintion_with_constructor_arguments()
    {
        $constructor = new Constructor('Foo\Person', [new Argument('name', 'string', false)]);
        $definition = new Definition(DefinitionType::data(), 'Foo', 'Person', [$constructor]);

        $this->assertSame('Foo', $definition->namespace());
        $this->assertSame('Person', $definition->name());

        $this->assertCount(1, $definition->constructors());

        $constructor = $definition->constructors()[0];

        $this->assertCount(1, $constructor->arguments());

        $argument = $constructor->arguments()[0];

        $this->assertSame('name', $argument->name());
        $this->assertSame('string', $argument->type());
        $this->assertTrue($argument->isScalarTypeHint());
        $this->assertFalse($argument->nullable());
    }

    /**
     * @test
     */
    public function it_creates_data_defintion_with_derivings()
    {
        $constructor = new Constructor('Foo\Person', [new Argument('name', 'string', false)]);

        $definition = new Definition(
            DefinitionType::data(),
            'Foo',
            'Person',
            [$constructor],
            [new Deriving\ToScalar()]
        );

        $this->assertSame('Foo', $definition->namespace());
        $this->assertSame('Person', $definition->name());

        $this->assertCount(1, $definition->derivings());

        $deriving = $definition->derivings()[0];

        $this->assertSame(Deriving\ToScalar::VALUE, (string) $deriving);
    }

    /**
     * @test
     */
    public function it_forbids_message_name_for_non_prooph_message_deriving()
    {
        $this->expectException(\InvalidArgumentException::class);

        $constructor = new Constructor('Foo\Person', [new Argument('name', 'string', false)]);

        new Definition(DefinitionType::data(), 'Foo', 'Person', [$constructor], [], [], 'invalid');
    }

    /**
     * @test
     */
    public function it_forbids_empty_string_message_name_for_prooph_message_deriving()
    {
        $this->expectException(\InvalidArgumentException::class);

        $constructor = new Constructor('Foo\Person', [new Argument('name', 'string', false)]);

        new Definition(DefinitionType::data(), 'Foo', 'Person', [$constructor], [new Deriving\Command()], [], '');
    }

    /**
     * @test
     */
    public function it_requires_constructors_to_be_correct_instance()
    {
        $this->expectException(\InvalidArgumentException::class);

        new Definition(
            DefinitionType::data(),
            'Foo',
            'Person',
            ['invalid']
        );
    }

    /**
     * @test
     */
    public function it_creates_prooph_message_types()
    {
        $definition = new Definition(
            DefinitionType::data(),
            'Foo',
            'RegisterPerson',
            [new Constructor('Foo\RegisterPerson', [
                new Argument('id', 'string'),
            ])],
            [new Deriving\Command()],
            [],
            'register.person'
        );

        $this->assertSame(Deriving\Command::VALUE, (string) $definition->derivings()[0]);
        $this->assertSame('register.person', $definition->messageName());
    }

    /**
     * @test
     */
    public function it_forbids_duplicate_constructor_names()
    {
        $this->expectException(\InvalidArgumentException::class);

        $constructor = new Constructor('Foo\Person', [
            new Argument('name', 'string', false),
        ]);

        new Definition(
            DefinitionType::data(),
            'Foo',
            'Person',
            [$constructor, $constructor]
        );
    }

    /**
     * @test
     */
    public function it_forbids_invalid_derivings()
    {
        $this->expectException(\InvalidArgumentException::class);

        $constructor = new Constructor('Foo\Person', [
            new Argument('name', 'string', false),
        ]);

        new Definition(
            DefinitionType::data(),
            'Foo',
            'Person',
            [$constructor],
            ['invalid']
        );
    }

    /**
     * @test
     */
    public function it_forbids_duplicate_derivings()
    {
        $this->expectException(\InvalidArgumentException::class);

        $constructor = new Constructor('Foo\Person', [
            new Argument('name', 'string', false),
        ]);

        new Definition(
            DefinitionType::data(),
            'Foo',
            'Person',
            [$constructor],
            [new Deriving\ToString(), new Deriving\ToString()]
        );
    }

    /**
     * @test
     */
    public function it_forbids_invalid_conditions()
    {
        $this->expectException(\InvalidArgumentException::class);

        $constructor = new Constructor('Foo\Person', [
            new Argument('name', 'string', false),
        ]);

        new Definition(
            DefinitionType::data(),
            'Foo',
            'Person',
            [$constructor],
            [],
            ['invalid']
        );
    }

    /**
     * @test
     */
    public function it_requires_at_least_one_constructor()
    {
        $this->expectException(\InvalidArgumentException::class);

        new Definition(DefinitionType::data(), 'Foo', 'Person');
    }

    /**
     * @test
     */
    public function it_checks_deriving_requirements()
    {
        $this->expectException(InvalidDeriving::class);

        $deriving1 = new Deriving\Command();
        $deriving2 = new Deriving\ToArray();

        $constructor = new Constructor('Foo\Person', [
            new Argument('name', 'string', false),
        ]);

        new Definition(
            DefinitionType::data(),
            'Foo',
            'Person',
            [$constructor],
            [$deriving1, $deriving2],
            []
        );
    }

    /**
     * @test
     */
    public function it_checks_deriving_requirements_2()
    {
        $deriving1 = new Deriving\FromString();
        $deriving2 = new Deriving\ToString();

        $constructor = new Constructor('Foo\Person', [
            new Argument('name', 'string', false),
        ]);

        $definition = new Definition(
            DefinitionType::data(),
            'Foo',
            'Person',
            [$constructor],
            [$deriving1, $deriving2],
            []
        );

        $this->assertCount(2, $definition->derivings());
    }

    /**
     * @test
     */
    public function it_checks_constructor_requirements()
    {
        $this->expectException(InvalidDeriving::class);

        $constructor = new Constructor('Foo\Person', [
            new Argument('firstName', 'string', false),
            new Argument('lastName', 'string', false),
        ]);

        new Definition(
            DefinitionType::data(),
            'Foo',
            'Person',
            [$constructor],
            [new Deriving\ToString()],
            []
        );
    }
}
