<?php declare(strict_types=1);

namespace Danilovl\PermissionMiddlewareBundle\Tests\Fixtures;

use Symfony\Contracts\Translation\TranslatorInterface;

class SpyTranslator implements TranslatorInterface
{
    /** @var list<array{string, array, ?string, ?string}> */
    public array $calls = [];

    public function trans(string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string
    {
        $this->calls[] = [$id, $parameters, $domain, $locale];

        return 'translated:' . $id;
    }

    public function getLocale(): string
    {
        return 'en';
    }
}
