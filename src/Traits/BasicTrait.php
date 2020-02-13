<?php
declare(strict_types=1);

namespace Azonmedia\Exceptions\Traits;

use Azonmedia\Utilities\GeneralUtil;

trait BasicTrait
{
    private ?string $uuid = NULL;

    private int $microtime_created;

    public function __construct(string $message = '', int $code = 0, \Throwable $previous = NULL, ?string $uuid = NULL)
    {

        if ($uuid && !GeneralUtil::is_uuid($uuid)) {
            print sprintf('The provided UUID %s to the exception is not a valid UUID. The provided UUID will be ignored.', $uuid);
        } else {
            $this->uuid = $uuid;
        }

        $this->microtime_created = (int) microtime(TRUE) * 1_000_000;

        parent::__construct($message, $code, $previous);
    }

    public function __toString() : string
    {
        //return $this->getPrettyMessage();
        return $this->getCompleteMessage();
    }

    public function get_microtime_created() : int
    {
        return $this->getMicrotimeCreated();
    }

    public function getMicrotimeCreated() : int
    {
        return $this->microtime_created;
    }

    public function getUUID() : ?string
    {
        return $this->uuid;
    }

    public function getDebugData() : string
    {
        $ret =
            time().' '.date('Y-m-d H:i:s').PHP_EOL.
            $this->getMessage().':'.$this->getCode().PHP_EOL.
            $this->getFile().':'.$this->getLine().PHP_EOL;
        return $ret;
    }

}