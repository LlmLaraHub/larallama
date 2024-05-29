<?php

namespace App\Domains\EmailParser;

use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Support\FolderCollection;

class EmailClientWrapper
{
    protected array $config = [];

    protected ClientManager $clientManager;

    public function setConfig(array $config): self
    {
        $this->config = $config;

        return $this;
    }

    public function connect(): self
    {
        $this->clientManager = new ClientManager($this->config);

        return $this;
    }

    public function getFolders(bool $hierarchical = false): FolderCollection
    {
        return $this->clientManager->getFolders(false);
    }
}
