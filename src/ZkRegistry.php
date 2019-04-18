<?php declare(strict_types=1);

namespace Swooless\Registry;

class ZkRegistry implements SPI
{
    public function addProvider(ServerNode $node): bool
    {
        // TODO: Implement addProvider() method.
    }

    public function removeProvider(ServerNode $node): bool
    {
        // TODO: Implement removeProvider() method.
    }

    public function getProviderList(string $serverName): array
    {
        // TODO: Implement getProviderList() method.
    }

    public function getProvider(string $serverName): ServerNode
    {
        // TODO: Implement getProvider() method.
    }
}
