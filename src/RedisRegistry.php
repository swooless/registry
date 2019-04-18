<?php declare(strict_types=1);

namespace Swooless\Registry;

use Exception;
use Redis;

class RedisRegistry implements SPI
{
    /** @var Redis */
    private $redis;

    private const HOST = 0;
    private const PORT = 1;

    public function __construct()
    {
        $this->redis = new Redis();

        $host = getenv('REDIS_SERVER_HOST') ?: '127.0.0.1';
        $port = (int)getenv('REDIS_SERVER_PORT') ?: 6379;
        $pwd = getenv('REDIS_SERVER_PWD') ?: '';

        $this->redis->connect($host, $port);
        $this->redis->auth($pwd);
    }

    public function addProvider(ServerNode $node): bool
    {
        $host = sprintf("%s:%s", $node->getHost(), $node->getPort());
        $key = $this->toProviderName($node->getName());
        return $this->redis->sAdd($key, $host) > 0;
    }

    public function removeProvider(ServerNode $node): bool
    {
        $host = sprintf("%s:%s", $node->getHost(), $node->getPort());
        $key = $this->toProviderName($node->getName());
        return $this->redis->sRemove($key, $host) > 0;
    }

    public function getProviderList(string $serverName): array
    {
        $key = $this->toProviderName($serverName);
        return $this->redis->sDiff($key, null);
    }

    /**
     * @param string $serverName
     * @return mixed
     * @throws Exception
     */
    public function getProvider(string $serverName): ServerNode
    {
        $hosts = $this->getProviderList($serverName);

        $serverList = [];
        foreach ($hosts as $host) {
            if ($this->healthExamination($host)) {
                $serverList[] = $host;
            } else {
                error_log("服务：{$host} 不可用");
                $this->redis->srem($this->toProviderName($serverName), $host);
            }
        }

        $count = count($serverList);

        if ($count > 0) {
            $index = (int)rand(0, $count - 1);
            $server = $serverList[$index];
            $serverInfo = explode(':', $server);

            $node = new ServerNode();
            $node->setName($serverName);
            $node->setHost($serverInfo[self::HOST]);
            $node->setPort(intval($serverInfo[self::PORT]));
            return $node;
        }

        error_log("未发现 [{$serverName}] 服务提供者");
        throw new NotFoundProvidersException("未发现 [{$serverName}] 服务提供者");
    }

    /**
     * @param string $host
     * @return bool
     * @throws Exception
     */
    private function healthExamination(string $host): bool
    {
        $arr = explode(':', $host);
        $ip = $arr[0];
        $port = $arr[1];

        $socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket === false) {
            throw new Exception('socket error!');
        }

        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array("sec" => 1, "usec" => 0));
        socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, array("sec" => 1, "usec" => 0));
        $result = @socket_connect($socket, $ip, (int)$port);

        @socket_close($socket);
        return boolval($result);
    }

    private function toProviderName($serverName)
    {
        $path = str_replace('\\', '.', $serverName);
        $path = "thrift:{$path}:providers";
        return strtolower($path);
    }
}
