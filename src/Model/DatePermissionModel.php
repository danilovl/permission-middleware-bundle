<?php declare(strict_types=1);

namespace Danilovl\PermissionMiddlewareBundle\Model;

use Danilovl\PermissionMiddlewareBundle\Attribute\RequireModelOption;
use Danilovl\PermissionMiddlewareBundle\Exception\LogicException;
use Danilovl\PermissionMiddlewareBundle\Interfaces\CheckInterface;
use Danilovl\PermissionMiddlewareBundle\Traits\OptionsCheckTrait;
use DateTimeImmutable;

#[RequireModelOption(optionNames: ['from', 'to'])]
class DatePermissionModel implements CheckInterface
{
    use OptionsCheckTrait;

    public readonly ?DateTimeImmutable $from;
    public readonly ?DateTimeImmutable $to;
    public readonly ?TransPermissionModel $exceptionMessage;
    public readonly ?RedirectPermissionModel $redirect;

    public function __construct(array $options)
    {
        $this->checkOptions($options);
        $this->initializeDate($options);

        $exceptionMessage = $options['exceptionMessage'] ?? null;
        $redirect = $options['redirect'] ?? null;

        $this->exceptionMessage = $exceptionMessage !== null ? new TransPermissionModel($exceptionMessage) : null;
        $this->redirect = $redirect !== null ? new RedirectPermissionModel($redirect) : null;
    }

    private function initializeDate(array $options): void
    {
        $from = $options['from'] ?? null;
        $to = $options['to'] ?? null;

        if ($from !== null) {
            if (is_string($from)) {
                $this->from = new DateTimeImmutable($from);
            } elseif ($from instanceof DateTimeImmutable) {
                $this->from = $from;
            } else {
                throw new LogicException(sprintf('Try to initialize "%s" with unknown value "from" parameter.', self::class));
            }
        } else {
            $this->from = null;
        }

        if ($to !== null) {
            if (is_string($to)) {
                $this->to = new DateTimeImmutable($to);
            } elseif ($to instanceof DateTimeImmutable) {
                $this->to = $to;
            } else {
                throw new LogicException(sprintf('Try to initialize "%s" with unknown value "to" parameter.', self::class));
            }
        } else {
            $this->to = null;
        }

        if ($this->from !== null && $this->to !== null && $this->from > $this->to) {
            throw new LogicException(sprintf('Try to initialize "%s" with invalid date range.', self::class));
        }
    }

    public function checkOptions(array $options): void
    {
        $this->checkOptionsNames($options);
        $this->checkRequiredOptions($options);
    }
}
