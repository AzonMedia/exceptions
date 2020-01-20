<?php
declare(strict_types=1);

namespace Azonmedia\Exceptions\Traits;


trait BasicMethodsTrait
{
    /**
     * Returns an array of messages from this exception and all previous exceptions (if there are such)
     * The first message is from this exception, the second is from its previous exception, and so on...
     * @return array Array of strings (error messages)
     */
    public function getAllMessages() : array
    {
        $messages = [];
        $exception = $this;
        do {
            $messages[] = $exception->getMessage().' ';
            $exception = $exception->getPrevious();
        } while ($exception);
        return $messages;
    }

    /**
     * Returns an array of messages from this exception and all previous exceptions (if there are such)
     * The first message is from this exception, the second is from its previous exception, and so on...
     * @return string A concatenated string of all error messages;.
     */
    public function getAllMessagesAsString() : string
    {
        // $message = implode(' ', $this->getAllMessagesAsArray());
        $message = ' ';
        return $message;
    }

    /**
     * Returns the caller using the internal debug_backtrace() function.
     * @param int $level $level=1 means the parent caller, 2 means the parent of the parent call and so on
     * @return array
     */
    protected function _get_caller($level=1)
    {
        $trace_arr = debug_backtrace();
        return $trace_arr[$level+1];
    }

    /**
     * Returns the caller class using the internal debug_backtrace() function.
     * @param int $level $level=1 means the parent caller, 2 means the parent of the parent call and so on
     * @return string
     */
    protected function _get_caller_class($level=1)
    {
        //$caller_arr = $this->_get_caller($level);
        $trace_arr = debug_backtrace(0);
        $caller_arr = $trace_arr[$level+1];
        if (!isset($caller_arr['class'])) {
            $trace = debug_backtrace();
        }
        return $caller_arr['class'];
    }

    /**
     * Returns the caller method using the internal debug_backtrace() function.
     * @param int $level $level=1 means the parent caller, 2 means the parent of the parent call and so on
     * @return string
     */
    protected function _get_caller_method($level=1)
    {
        //$caller_arr = $this->_get_caller($level);
        $trace_arr = debug_backtrace(0);
        $caller_arr = $trace_arr[$level+1];
        if (!isset($caller_arr['function'])) {
            $trace = debug_backtrace();
        }
        return $caller_arr['function'];
    }




    //public static function setPreviousExceptionStatic(\Throwable $exception, \Guzaba2\Base\Interfaces\TraceInfoInterface $previous) : void
    public static function setPreviousExceptionStatic(\Throwable $exception, \Throwable $previous) : void
    {
        // if ($previous instanceof framework\base\interfaces\traceInfo) {
        //     $previous = $previous->getAsException();
        // }


        self::setPropertyStatic($exception, 'previous', $previous);
    }

    /**
     * This allows a previous exception to be set on an existing exception (this can be set only during the construction)
     * This is to be used to set traceException as a previous exception to an existing one.
     * But we will not be limiting the signature only to the traceException
     *
     * If the provided argument is traceInfoObject not a traceException (these two classes implement traceInfo) then the traceInfoObject will be converted to traceException
     *
     * @param framework\base\interfaces\traceInfo $exception
     */
    //public function setPreviousException(\Throwable $previous) : void
    //public function setPreviousException(\Guzaba2\Base\Interfaces\TraceInfoInterface $previous) : void
    public function setPreviousException(\Throwable $previous) : void
    {
        /*
        $reflection = new \ReflectionClass($this);
        while( ! $reflection->hasProperty('previous') ) {
            $reflection = $reflection->getParentClass();
        }
        $prop = $reflection->getProperty('previous');
        $prop->setAccessible(true);
        $prop->setValue($this, $previous);
        $prop->setAccessible(false);
        */
        // if ($previous instanceof framework\base\interfaces\traceInfo) {
        //     $previous = $previous->getAsException();
        // }


        $this->setProperty('previous', $previous);
    }

    public static function prependAsFirstExceptionStatic(\Throwable $exception, \Throwable $FirstException) : void
    {
        $CurrentFirstException = self::getFirstExceptionStatic($exception);
        self::setPreviousExceptionStatic($CurrentFirstException, $FirstException);
    }

    public function prependAsFirstException(\Throwable $FirstException) : void
    {
        self::prependAsFirstExceptionStatic($this, $FirstException);
    }

    /**
     * The first exception of a chain of previous exceptions.
     *
     */
    public static function getFirstExceptionStatic(\Throwable $exception) : \Throwable
    {
        do {
            $previous_exception = $exception->getPrevious();
            if ($previous_exception) {
                $exception = $previous_exception;
            }
        } while ($previous_exception);

        return $exception;
    }

    public function getFirstException() : \Throwable
    {
        return self::getFirstExceptionStatic($this);
    }
}