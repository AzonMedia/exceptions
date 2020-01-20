<?php
declare(strict_types=1);

namespace Azonmedia\Exceptions\Traits;

use Azonmedia\Packages\Packages;

trait ErrorReferenceTrait
{

    public function getUUID() : ?string
    {
        return $this->uuid;
    }

    public function getErrorReferenceUrl() : ?string
    {
        $ret = NULL;
        $base_url = $this->getErrorReferenceBaseUrl();
        $uuid = $this->getUUID();

        if ($uuid) {
            if ($base_url) {
                $ret = $base_url.$uuid;
            } else {
                print sprintf('The exception has a valid UUID %s provided but no base URL / component could be detected in the stack backtrace.', $uuid);
            }
        }
        return $ret;
    }

    public function getErrorReferenceBaseUrl() : ?string
    {
        $ret = NULL;
        $component_class = $this->getErrorComponentClass();
        if ($component_class && class_exists($component_class)) {
            $ret = $component_class::get_error_reference_url();
        }
        return $ret;
    }

    public function getErrorComponentClass() : ?string
    {
        $ret = NULL;
        foreach ($this->getTrace() as $frame) {
            if (!empty($frame['class'])) {
                $Package = (new Packages(Packages::get_application_composer_file_path()))->get_package_by_class($frame['class']);
                if ($Package) {
                    $package_ns = Packages::get_package_namespace($Package);
                    $component_class = $package_ns.'Component';
                    if (class_exists($component_class)) {
                        $ret = $component_class;
                    }
                }
            }
        }
        return $ret;
    }

    /**
     * Returns a string with the component name where the error occurred.
     * This is done by looking back in the stacktrace.
     * Returns NULL if no component is found.
     * @return string|null
     */
    public function getErrorComponentName() : ?string
    {
        $ret = NULL;
        $component_class = $this->getErrorComponentClass();
        if ($component_class) {
            $ret = $component_class::get_name().' ('.$component_class::get_composer_package_name().') '.$component_class::get_source_url();
        }
        return $ret;
    }
}