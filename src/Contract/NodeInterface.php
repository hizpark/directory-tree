<?php

declare(strict_types=1);

namespace Hizpark\DirectoryTree\Contract;

interface NodeInterface
{
    public function getPath(): string;

    public function getLocation(): string;

    public function getParent(): ?NodeInterface;

    public function addChild(NodeInterface $child): void;

    /**
     * @return ?NodeInterface[]
     */
    public function getChildren(): ?array;
}
