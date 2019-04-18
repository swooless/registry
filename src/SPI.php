<?php declare(strict_types=1);

namespace Swooless\Registry;

/** Service Provider Interface */
interface SPI
{
    public function addProvider(ServerNode $node): bool;

    public function removeProvider(ServerNode $node): bool;

    public function getProviderList(string $serverName): array;

    public function getProvider(string $serverName): ServerNode;
}
