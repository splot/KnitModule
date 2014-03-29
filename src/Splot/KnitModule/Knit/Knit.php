<?php
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

    public function getRepository($entityClass) {
        if (stripos($entityClass, ':')) {
            $entityClass = $this->entityFinder->expand($entityClass);
        }
        
        return parent::getRepository($entityClass);
    }

}