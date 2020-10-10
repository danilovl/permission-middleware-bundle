<?php declare(strict_types=1);

namespace Danilovl\PermissionMiddlewareBundle\Model;

class TransPermissionModel
{
    public ?string $message = null;
    public array $messageParameters = [];
    public ?string $domain = null;
    public ?string $locale = null;

    public function __construct(?array $options)
    {
        if (empty($options)) {
            return;
        }

        $this->message = !empty($options['message']) ? $options['message'] : null;
        $this->messageParameters = !empty($options['messageParameters']) ? $options['messageParameters'] : [];
        $this->domain = !empty($options['domain']) ? $options['domain'] : null;
        $this->locale = !empty($options['locale']) ? $options['locale'] : null;
    }

    public function getArguments(): array
    {
        $data = [];
        if ($this->message === null) {
            return $data;
        }

        $data = [$this->message, $this->messageParameters];
        if ($this->domain !== null && $this->locale !== null) {
            $data = array_merge($data, [$this->domain, $this->locale]);
        }

        return $data;
    }
}
