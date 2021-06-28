<?php

declare(strict_types=1);

namespace QB\Generic\Params;

use InvalidArgumentException;
use PDO;

class Params
{
    public const ALL_STRING = 1;
    public const ALL_AUTO   = 2;
    public const ALL_MANUAL = 4;

    protected int $paramHandle;

    protected bool $useNamedParams;

    /** @var array<int,array<int,mixed>> */
    protected array $params = [];

    /** @var int Helps tracking the extensions done on the SQL originally received */
    protected int $extendedBy = 0;

    /**
     * Params constructor.
     *
     * @param array $params
     * @param int   $paramHandle
     */
    public function __construct(array $params = [], int $paramHandle = self::ALL_AUTO)
    {
        $this->useNamedParams = !array_key_exists(0, $params);

        $this->validateParamHandle($paramHandle);
        $this->validateParamKeys(array_keys($params));

        $this->paramHandle = $paramHandle;

        $this->bind($params, $paramHandle);
    }

    /**
     * @param int $paramHandle
     */
    protected function validateParamHandle(int $paramHandle): void
    {
        if (!in_array($paramHandle, [self::ALL_STRING, self::ALL_AUTO, self::ALL_MANUAL])) {
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
    public function bind(array $params, int $paramHandle = self::ALL_AUTO)
    {
        foreach ($params as $origKey => $var) {
            if ($this->useNamedParams) {
                $this->params[$origKey] = $this->getFinalParam($var, $paramHandle);
            } else {
                $this->params[] = $this->getFinalParam($var, $paramHandle);
            }
        }
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
            case self::ALL_MANUAL:
                return [$var[0], $var[1]];
            case self::ALL_AUTO:
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
     * @return bool
     */
    public function usesNamedParams(): bool
    {
        return $this->useNamedParams;
    }

    /**
     * @return bool
     */
    public function usesUnnamedParams(): bool
    {
        return !$this->useNamedParams;
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        return $this->params;
    }
}
