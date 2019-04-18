<?php declare(strict_types=1);

namespace Swooless\Registry\Tests;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Swooless\Registry\RedisRegistry;
use Swooless\Registry\ServerNode;

class RedisRegistryTest extends TestCase
{
    private static $node;
    private static $name = 'test';

    public static function setUpBeforeClass()
    {
        self::$node = new ServerNode();
        self::$node->setHost('127.0.0.1');
        self::$node->setName(self::$name);
        self::$node->setPort(90909);
    }

    public function testAdd()
    {
        $registry = new RedisRegistry();
        $result = $registry->addProvider(self::$node);
        Assert::assertTrue($result);
    }

    public function testGetList()
    {
        $registry = new RedisRegistry();
        $result = $registry->getProviderList(self::$name);
        Assert::assertIsArray($result);
    }

    public function testDel()
    {
        $registry = new RedisRegistry();
        $result = $registry->removeProvider(self::$node);
        Assert::assertTrue($result);
    }
}