<?php

declare(strict_types=1);

namespace QB\Generic\Expr;

use InvalidArgumentException;
use QB\Generic\IQueryPart;
use QB\Generic\Params\Params;

class Expr implements IQueryPart
{
    protected string $sql;

    protected bool $useNamedParams;

    protected Params $params;

    /** @var int Helps tracking the extensions done on the SQL originally received */
    protected int $extendedBy = 0;

    /**
     * Expr constructor.
     *
     * @param IQueryPart|string $sql
     * @param array             $params
     * @param int               $paramHandle
     */
    public function __construct(IQueryPart|string $sql, array $params = [], int $paramHandle = Params::ALL_AUTO)
    {
        $this->sql = (string)$sql;

        $this->useNamedParams = !array_key_exists(0, $params);

        $allParams = [];
        foreach ($params as $origKey => $origParam) {
            $fixedParam = $this->toParamArray($origKey, $origParam, $paramHandle);
            if (count($fixedParam) > 1) {
                if (!$this->useNamedParams) {
                    $this->expandSqlUnnamed($origKey, count($fixedParam));
                } else {
                    $this->expandSqlNamed($origKey, count($fixedParam));
                }
            }

            foreach ($fixedParam as $fixedKey => $var) {
                if ($this->useNamedParams) {
                    $allParams[$fixedKey] = $var;
                } else {
                    $allParams[] = $var;
                }
            }
        }

        $this->params = new Params($allParams, $paramHandle);
    }

    /**
     * @param string|int $origKey
     * @param            $param
     * @param int        $paramHandle
     *
     * @return array
     */
    protected function toParamArray(string|int $origKey, $param, int $paramHandle): array
    {
        if (!$this->isParamArray($param, $paramHandle)) {
            if ($this->useNamedParams) {
                return [$origKey => $param];
            }

            return [$param];
        }

        if (!$this->useNamedParams) {
            return $param;
        }

        $fixedParams = [];
        foreach ($param as $i => $p) {
            $fixedParams[$origKey . '_' . $i] = $p;
        }

        return $fixedParams;
    }

    /**
     * @param     $param
     * @param int $paramHandle
     *
     * @return bool
     */
    protected function isParamArray($param, int $paramHandle): bool
    {
        if (is_scalar($param) || is_null($param)) {
            return false;
        }

        if (!is_array($param)) {
            throw new InvalidArgumentException(sprintf('param must be scalar or array, %s received.', gettype($param)));
        }

        if (in_array($paramHandle, [Params::ALL_AUTO, Params::ALL_STRING], true)) {
            return true;
        }

        if (count($param) == 2 && is_scalar($param[0]) && is_int($param[1]) && $param[1] >= 0) {
            return false;
        }

        return true;
    }

    /**
     * @param int $origKey
     * @param int $fixedParamCount
     */
    protected function expandSqlUnnamed(int $origKey, int $fixedParamCount): void
    {
        $count = $fixedParamCount - 1;
        $pos   = $origKey + $this->extendedBy + $count;

        $parts = explode('?', $this->sql);
        $qs    = '?' . str_repeat(', ?', $count);
        $start = implode('?', array_slice($parts, 0, $pos));
        $end   = implode('?', array_slice($parts, $pos));

        $this->sql        = $start . $qs . $end;
        $this->extendedBy += $fixedParamCount - 1;
    }

    /**
     * @param string $origKey
     * @param int    $fixedParamCount
     */
    protected function expandSqlNamed(string $origKey, int $fixedParamCount): void
    {
        $searchKey = ':' . $origKey;
        $parts     = [];
        for ($i = 0; $i < $fixedParamCount; $i++) {
            $parts[] = $searchKey . '_' . $i;
        }
        $replace = implode(', ', $parts);

        $this->sql = str_replace($searchKey, $replace, $this->sql);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->sql;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params->getAll();
    }
}
