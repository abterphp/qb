<?php

declare(strict_types=1);

namespace QB\Generic\Template;

use QB\Generic\IQueryPart;

class Template implements IQueryPart
{
    protected string $template;

    /** @var IQueryPart[] */
    protected array $queryParts;

    public function __construct(string $template, IQueryPart ...$queryParts)
    {
        $this->template   = $template;
        $this->queryParts = $queryParts;
    }

    public function __toString(): string
    {
        return sprintf($this->template, ...$this->queryParts);
    }

    public function getParams(): array
    {
        $params = [];
        foreach ($this->queryParts as $queryPart) {
            $params = array_merge($params, $queryPart->getParams());
        }

        return $params;
    }
}