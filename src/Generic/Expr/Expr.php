<?php

declare(strict_types=1);

namespace QB\Generic\Expr;

use InvalidArgumentException;
use PDO;
use QB\Generic\IQueryPart;

class Expr implements IQueryPart
{
    public const PARAM_ALL_STRING = 1;
    public const PARAM_ALL_AUTO   = 2;
    public const PARAM_ALL_MANUAL = 4;

    protected string $sql;

    protected bool $useNamedParams;

    /** @var array<int,array<int,mixed>> */
    protected array $params = [];

    /** @var int Helps tracking the extensions done on the SQL originally received */
    protected int $extendedBy = 0;

    /**
     * Expr constructor.
     *
     * @param string|IQueryPart $sql
     * @param array             $params
     * @param int               $paramHandle
     */
    public function __construct(string|IQueryPart $sql, array $params = [], int $paramHandle = self::PARAM_ALL_AUTO)
    {
        $this->useNamedParams = !array_key_exists(0, $params);

        $this->validateParamHandle($paramHandle);
        $this->validateParamKeys(array_keys($params));

        $this->sql = (string)$sql;

        $this->bindParams($params, $paramHandle);
    }

    /**
     * @param int $paramHandle
     */
    protected function validateParamHandle(int $paramHandle): void
    {
        if (!in_array($paramHandle, [self::PARAM_ALL_STRING, self::PARAM_ALL_AUTO, self::PARAM_ALL_MANUAL])) {
            throw new InvalidArgumentException(
                sprintf('invalid param handle received: %d.', $paramHandle)
            );
        }
    }

    /**
     * @param array $paramKeys
     */
    protected function validateParamKeys(array $paramKeys): void
    {
        if ($this->useNamedParams) {
            foreach ($paramKeys as $paramKey) {
                if (is_int($paramKey)) {
                    throw new InvalidArgumentException(
                        sprintf('string param key was expected, int received: %d.', $paramKey)
                    );
                }
            }

            return;
        }

        $next = 0;
        foreach ($paramKeys as $paramKey) {
            if ($paramKey !== $next) {
                throw new InvalidArgumentException(
                    sprintf('key was expected to be %d, received: %s.', $next, $paramKey)
                );
            }
            $next++;
        }
    }

    /**
     * @param array $params
     * @param int   $paramHandle
     */
    protected function bindParams(array $params, int $paramHandle = self::PARAM_ALL_AUTO)
    {
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
                    $this->params[$fixedKey] = $this->getFinalParam($var, $paramHandle);
                } else {
                    $this->params[] = $this->getFinalParam($var, $paramHandle);
                }
            }
        }
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

        if (in_array($paramHandle, [self::PARAM_ALL_AUTO, self::PARAM_ALL_STRING], true)) {
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
     * @param     $var
     * @param int $paramHandle
     *
     * @return array
     */
    protected function getFinalParam($var, int $paramHandle): array
    {
        switch ($paramHandle) {
            case self::PARAM_ALL_MANUAL:
                return [$var[0], $var[1]];
            case self::PARAM_ALL_AUTO:
                if ($var === null) {
                    return [$var, PDO::PARAM_NULL];
                } elseif (is_bool($var)) {
                    return [$var, PDO::PARAM_BOOL];
                } elseif (is_int($var)) {
                    return [$var, PDO::PARAM_INT];
                }
        }

        return [$var, PDO::PARAM_STR];
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
        return $this->params;
    }
}
