<?php declare(strict_types=1);

namespace Danilovl\PermissionMiddlewareBundle\Model;

use Danilovl\PermissionMiddlewareBundle\Interfaces\CheckInterface;
use DateTime;

class DatePermissionModel implements CheckInterface
{
    public ?DateTime $from = null;
    public ?DateTime $to = null;
    public TransPermissionModel $exceptionMessage;
    public RedirectPermissionModel $redirect;
    public bool $accessDeniedHttpException = true;

    public function __construct(?array $options)
    {
        if (empty($options)) {
            return;
        }

        $this->from = !empty($options['from']) ? new DateTime($options['from']) : null;
        $this->to = !empty($options['to']) ? new DateTime($options['to']) : null;
        $this->exceptionMessage = new TransPermissionModel($options['exceptionMessage'] ?? null);
        $this->redirect = new RedirectPermissionModel($options['redirect'] ?? null);
        $this->accessDeniedHttpException = (bool) ($options['accessDeniedHttpException'] ?? true);
    }

    public function canCheck(): bool
    {
        return $this->from !== null || $this->to !== null;
    }
}
