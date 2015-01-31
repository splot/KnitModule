<?php
/**
 * Knit entity finder for Splot Framework that allows for finding entities
 * Splot modules registered in an application.
 * 
 * @package SplotKnitModule
 * @subpackage Knit
 * @author Michał Dudek <michal@michaldudek.pl>
 * 
 * @copyright Copyright (c) 2014, Michał Dudek
 * @license MIT
 */
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

    /**
     * Application to which the finder is bound.
     * 
     * @var AbstractApplication
     */
    protected $application;

    /**
     * Constructor.
     * 
     * @param AbstractApplication $application Application to which the finder is bound.
     */
    public function __construct(AbstractApplication $application) {
        $this->application = $application;
    }

    /**
     * Returns a list of all entities available in the application.
     * 
     * @return array
     */
    public function all() {
        $entityClasses = array();
        foreach($this->application->getModules() as $module) {
            $entityClasses = array_merge($entityClasses, $this->findInModule($module));
        }

        return $entityClasses;
    }

    /**
     * Returns a list of all entities available in the given module.
     * 
     * @param  AbstractModule $module Module for which the entities should be listed.
     * @return array
     */
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

    /**
     * Expands a resource-style entity name (i.e. SomeModuleName:EntityName) to its full class name.
     *
     * It does not check if the entity indeed exists.
     * 
     * @param  string $entityName Entity name to expand.
     * @return string
     *
     * @throws InvalidArgumentException When the entity name is not a valid resource name.
     * @throws NotFoundException When could not find a module for the given entity.
     */
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

}