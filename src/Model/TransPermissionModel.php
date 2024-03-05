<?php declare(strict_types=1);

namespace Danilovl\PermissionMiddlewareBundle\Model;

use Danilovl\PermissionMiddlewareBundle\Attribute\RequireModelOption;
use Danilovl\PermissionMiddlewareBundle\Interfaces\CheckInterface;
use Danilovl\PermissionMiddlewareBundle\Traits\OptionsCheckTrait;

#[RequireModelOption(['message'])]
class TransPermissionModel implements CheckInterface
{
    use OptionsCheckTrait;

    public readonly string $message;
    public readonly array $messageParameters;
    public readonly ?string $domain;
    public readonly ?string $locale;

    public function __construct(array $options)
    {
        $this->checkOptions($options);

        $this->message = $options['message'];
        $this->messageParameters = $options['messageParameters'] ?? [];
        $this->domain = !empty($options['domain']) ? $options['domain'] : null;
        $this->locale = !empty($options['locale']) ? $options['locale'] : null;
    }

    public function getArguments(): array
    {
        $data = [$this->message, $this->messageParameters];
        if ($this->domain !== null && $this->locale !== null) {
            $data = array_merge($data, [$this->domain, $this->locale]);
        }

        return $data;
    }

    public function checkOptions(array $options): void
    {
        $this->checkOptionsNames($options);
        $this->checkRequiredOptions($options);
    }
}
