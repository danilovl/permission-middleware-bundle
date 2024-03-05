<?php declare(strict_types=1);

namespace Danilovl\PermissionMiddlewareBundle\Traits;

use Danilovl\PermissionMiddlewareBundle\Attribute\RequireModelOption;
use Danilovl\PermissionMiddlewareBundle\Exception\LogicException;
use ReflectionClass;
use ReflectionProperty;

trait OptionsCheckTrait
{
    protected function checkOptionsNames(array $options): void
    {
        $reflection = new ReflectionClass($this);
        $properties = array_map(static function (ReflectionProperty $property): string {
            return $property->name;
        }, $reflection->getProperties());

        foreach ($options as $key => $value) {
            if (!in_array($key, $properties, true)) {
                throw new LogicException(sprintf('Try to initialize "%s" with unknown option "%s".', self::class, $key));
            }
        }
    }

    protected function checkRequiredOptions(array $options): void
    {
        $attributes = (new ReflectionClass($this))->getAttributes(RequireModelOption::class);
        if (count($attributes) === 0) {
            return;
        }

        /** @var RequireModelOption $attribute */
        $attribute = $attributes[0]->newInstance();

        $missingOptions = [];
        foreach ($attribute->requireNames as $optionName) {
            if (!array_key_exists($optionName, $options)) {
                $missingOptions[] = $optionName;
            }
        }

        if (count($missingOptions) >= 1) {
            $this->createMissingOptionsException($missingOptions);
        }

        $missingOptions = [];
        foreach ($attribute->optionNames as $optionName) {
            if (array_key_exists($optionName, $options)) {
                $missingOptions = [];

                break;
            } else {
                $missingOptions[] = $optionName;
            }
        }

        if (count($missingOptions) >= 1) {
            $this->createMissingOptionsException($missingOptions);
        }
    }

    protected function createMissingOptionsException(array $options): void
    {
        $options = implode(', ', $options);

        throw new LogicException(sprintf('Try to initialize %s without "%s" parameter.', self::class, $options));
    }
}
