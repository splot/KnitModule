<?php
namespace Splot\KnitModule;

use MD\Foundation\Exceptions\NotFoundException;

use Splot\Framework\Modules\AbstractModule;

use Splot\KnitModule\Knit\EntityFinder;
use Splot\KnitModule\Knit\Knit;

class SplotKnitModule extends AbstractModule
{

    protected $commandNamespace = 'knit';

    public function configure() {
        $config = $this->getConfig();
        $loggerProvider = $this->container->get('logger_provider');

        $this->container->set('knit.entity_finder', function($c) {
            return new EntityFinder($c->get('application'));
        });

        /*****************************************************
         * CONFIGURE KNIT STORES
         *****************************************************/
        $stores = $config->get('stores');

        // setup the default store
        $defaultStoreConfig = $stores['default'];
        unset($stores['default']);

        $this->container->set('knit.stores.default', function($c) use ($defaultStoreConfig) {
            return new $defaultStoreConfig['class'](
                $defaultStoreConfig,
                $c->get('logger_provider')->provide('Knit Default Store')
            );
        });

        // register other stores
        foreach($stores as $name => $storeConfig) {
            $this->container->set('knit.stores.'. $name, function($c) use ($name, $storeConfig) {
                return new $storeConfig['class'](
                    $storeConfig,
                    $c->get('logger_provider')->provide('Knit Store:'. $name)
                );
            });
        }

        /*****************************************************
         * CONFIGURE KNIT ITSELF
         *****************************************************/
        $entities = $config->get('entities');
        $this->container->set('knit', function($c) use ($stores, $entities) {
            $knit = new Knit(
                $c->get('knit.stores.default'),
                $c->get('knit.entity_finder')
            );

            // register all configured stores
            foreach($stores as $name => $storeConfig) {
                $knit->registerStore($name, $c->get('knit.stores.'. $name));
            }

            // configure entities
            foreach($entities as $entityClass => $entityConfig) {
                if (isset($entityConfig['store'])) {
                    $knit->setStoreNameForEntity($entityClass, $entityConfig['store']);
                }

                if (isset($entityConfig['repository'])) {
                    $knit->setRepositoryClassForEntity($entityClass, $entityConfig['repository']);
                }
            }

            return $knit;
        });
    }

}