<?php
namespace Splot\KnitModule\Knit;

use MD\Foundation\Debug\Debugger;
use MD\Foundation\Exceptions\InvalidArgumentException;
use MD\Foundation\Exceptions\NotFoundException;
use MD\Foundation\Utils\FilesystemUtils;

use Splot\Framework\Application\AbstractApplication;
use Splot\Framework\Modules\AbstractModule;

use Splot\KnitModule\Knit\EntityFinder;

class EntityFinder
{

    protected $application;

    public function __construct(AbstractApplication $application) {
        $this->application = $application;
    }

    public function all() {
        $entityClasses = array();
        foreach($this->application->getModules() as $module) {
            $entityClasses = array_merge($entityClasses, $this->findInModule($module));
        }

        return $entityClasses;
    }

    public function expand($entityName) {
        $entityNameArray = explode(':', $entityName);
        if (count($entityNameArray) !== 2 || empty($entityNameArray[0]) || empty($entityNameArray[1])) {
            throw new InvalidArgumentException('valid entity name', $entityName);
        }

        list($moduleName, $name) = $entityNameArray;
        if (!$this->application->hasModule($moduleName)) {
            throw new NotFoundException('Could not find module '. $moduleName .' for entity '. $entityName);
        }

        $module = $this->application->getModule($moduleName);

        return $module->getNamespace() . NS .'Entity'. NS . str_replace(DS, NS, $name);
    }

    public function findInModule(AbstractModule $module) {
        $entityDir = $module->getModuleDir() .'Entity'. DS;
        $moduleName = $module->getName();
        $entityClasses = array();
        
        foreach(FilesystemUtils::glob($entityDir .'{,**/}*.php', GLOB_BRACE) as $file) {
            $entityName = $moduleName .':'. mb_substr($file, mb_strlen($entityDir), -4);
            $entityClass = $this->expand($entityName);
            // make sure this is indeed an entity
            if (!Debugger::isExtending($entityClass, 'Knit\Entity\AbstractEntity')) {
                continue;
            }

            $entityClasses[] = $entityClass;
        }

        return $entityClasses;
    }

}