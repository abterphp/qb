<?php

declare(strict_types=1);

namespace QB\Generic;

interface IQueryPart
{
    public function __toString(): string;

    public function getParams(): array;
}
