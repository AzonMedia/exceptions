<?php
declare(strict_types=1);

namespace Azonmedia\Exceptions;

use Azonmedia\Exceptions\Interfaces\BaseExceptionInterface;
use Azonmedia\Exceptions\Interfaces\ErrorReferenceInterface;
use Azonmedia\Exceptions\Interfaces\PropertyModificationInterface;
use Azonmedia\Exceptions\Traits\BasicMethodsTrait;
use Azonmedia\Exceptions\Traits\BasicTrait;
use Azonmedia\Exceptions\Traits\ErrorReferenceTrait;
use Azonmedia\Exceptions\Traits\PropertyModificationTrait;

abstract class BaseException extends \Exception implements BaseExceptionInterface, ErrorReferenceInterface, PropertyModificationInterface
{
    use BasicTrait;
    use BasicMethodsTrait;
    use PropertyModificationTrait;
    use ErrorReferenceTrait;
}