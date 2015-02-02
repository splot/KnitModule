<?php
/**
 * Knit ORM bridge class that adds support for various Splot Framework specific features.
 * 
 * @package SplotKnitModule
 * @subpackage Knit
 * @author Michał Dudek <michal@michaldudek.pl>
 * 
 * @copyright Copyright (c) 2014, Michał Dudek
 * @license MIT
 */
namespace Splot\KnitModule\Knit;

use Knit\Store\StoreInterface;
use Knit\Knit as BaseKnit;

use Splot\KnitModule\Knit\EntityFinder;

class Knit extends BaseKnit
{

    /**
     * Entity finder.
     * 
     * @var EntityFinder
     */
    protected $entityFinder;

    /**
     * Constructor.
     * 
     * @param StoreInterface $defaultStore Default store that will be used with all repositories (if no other store defined).
     * @param EntityFinder $entityFinder Entity finder.
     * @param array $options [optional] Array of options.
     */
    public function __construct(StoreInterface $defaultStore, EntityFinder $entityFinder, array $options = array()) {
        parent::__construct($defaultStore, $options);
        $this->entityFinder = $entityFinder;
    }

    /**
     * Returns repository for the given entity.
     *
     * Supports module resource name syntax, ie. SomeModuleName:EntityName.
     * 
     * @param string|object $entityClass Class name (full, with namespace) of the entity, or an entity.
     * @return Repository
     */
    public function getRepository($entityClass) {
        if (is_string($entityClass) && stripos($entityClass, ':')) {
            $entityClass = $this->entityFinder->expand($entityClass);
        }
        
        return parent::getRepository($entityClass);
    }

}