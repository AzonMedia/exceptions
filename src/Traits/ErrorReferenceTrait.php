<?php
declare(strict_types=1);

namespace Azonmedia\Exceptions\Traits;

use Azonmedia\Exceptions\Interfaces\BaseExceptionInterface;
use Azonmedia\Exceptions\InvalidArgumentException;
use Azonmedia\Packages\Packages;
use Azonmedia\Translator\Translator as t;
use Azonmedia\Utilities\SourceUtil;

trait ErrorReferenceTrait
{

    /**
     * Returns the error reference URL for the given exception.
     * Returns NULL if no UUID is provided to the exception or the base error reference URL is not defined.
     * @return string|null
     * @throws InvalidArgumentException
     * @see self::getErrorReferenceBaseUrl()
     */
    public function getErrorReferenceUrl() : ?string
    {
        $ret = NULL;
        $base_url = $this->getErrorReferenceBaseUrl();
        $uuid = $this->getUUID();

        if ($uuid) {
            if ($base_url) {
                $ret = $base_url.$uuid;
            } else {
                print sprintf(t::_('The exception has a valid UUID %s provided but no base URL / component could be detected in the stack backtrace.'), $uuid);
            }
        }
        return $ret;
    }

    /**
     * Returns the base URL for the error reference of the component where the exception occurred.
     * Returns NULL if the package is not found of the Component class in this package does not exist
     * @see self::getErrorComponentClass()
     * @return string|null
     */
    public function getErrorReferenceBaseUrl() : ?string
    {
        $ret = NULL;
        $component_class = $this->getErrorComponentClass();
        if ($component_class && class_exists($component_class)) {
            $ret = $component_class::get_error_reference_url();
        }
        return $ret;
    }

    /**
     * Returns the Component class if the package if it exists.
     * @return string|null
     */
    public function getErrorComponentClass() : ?string
    {
        $ret = NULL;
        $Packages = new Packages(Packages::get_application_composer_file_path());

        foreach ($this->getTrace() as $frame) {
            if (!empty($frame['class'])) {
                $Package = $Packages->get_package_by_class($frame['class']);
                if ($Package) {
                    $package_ns = Packages::get_package_namespace($Package);
                    if ($package_ns) {
                        if ($package_ns[-1] !== '\\') {
                            $package_ns .= '\\';
                        }
                        $component_class = $package_ns.'Component';
                        if (class_exists($component_class)) {
                            $ret = $component_class;
                            break;
                        }
                    } else {
                        //look for the component.php file by using the installed package path
                        $component_install_path = trim($Packages->get_package_installation_path($Package).PHP_EOL);
                        //the look in the files
                        $autoload_rules = $Package->getAutoload();
                        $component_file_path = '';
                        if (isset($autoload_rules['files'])) {
                            foreach ($autoload_rules['files'] as $autoload_file) {
                                if (file_exists($component_install_path.'/Component.php')) {
                                    $component_file_path = $component_install_path.'/Component.php';
                                }
                                if (!$component_file_path) {
                                    $path_components = explode('/', $autoload_file);
                                    $composed_path = '/';
                                    foreach ($path_components as $path_component) {
                                        $composed_path .= $path_component.'/';
                                        if (file_exists($component_install_path.$composed_path.'Component.php')) {
                                            $component_file_path = $component_install_path.$composed_path.'Component.php';
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                        if ($component_file_path) {
                            $package_ns = SourceUtil::get_file_namespace($component_file_path);
                            if ($package_ns) {
                                if ($package_ns[-1] !== '\\') {
                                    $package_ns .= '\\';
                                }
                                $component_class = $package_ns.'Component';
                                if (class_exists($component_class)) {
                                    $ret = $component_class;
                                    break;
                                }
                            }
                        }
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

    /**
     * Returns a message suitable for showing to end user.
     * @return string
     * @throws InvalidArgumentException
     */
    public function getPrettyMessage() : string
    {
        $message = $this->getMessage();
        $component = $this->getErrorComponentName() ?? t::_('Unknown');
        $ref_url = $this->getErrorReferenceUrl();
        if ($ref_url) {
            $ref_message = sprintf(t::_('More details: %s'), $ref_url);
        } else {
            $ref_message = '';
        }

        //return sprintf(t::_('%s "%s" in module %s. %s'), get_class($this), $message, $component, $ref_url);
        $ex_message = sprintf(t::_('%s "%s" in module %s.'), get_class($this), $message, $component, $ref_url);
        return $ex_message.' '.$ref_message;
    }

    /**
     * Returns a message suitable for the developer.
     * @return string
     * @throws InvalidArgumentException
     */
    public function getCompleteMessage() : string
    {
        $ret = '';
        $Exception = $this;
        do {
            $ret .= sprintf(t::_('%s %s in %s#%s.'), get_class($Exception), $Exception->getMessage(), $Exception->getFile(), $Exception->getLine() ).PHP_EOL;
            if ($Exception instanceof BaseExceptionInterface) {
                $component_name = $Exception->getErrorComponentName();
                if ($component_name) {
                    $ret .= sprintf(t::_('Component: %s'), $component_name) . PHP_EOL;
                }
                $error_url = $Exception->getErrorReferenceUrl();
                if ($error_url) {
                    $ret .= sprintf(t::_('ERROR REFERENCE: %s'), $error_url) . PHP_EOL;
                }
            }
            $ret .= t::_('Stack Trace:') . PHP_EOL;
            $ret .= $Exception->getTraceAsString() . PHP_EOL;
            $Exception = $Exception->getPrevious();
            if ($Exception) {
                $ret .= t::_('Previous Exception:') . PHP_EOL;
            }
        } while ($Exception);

        return $ret;
    }
}