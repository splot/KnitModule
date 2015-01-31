<?php
namespace Splot\KnitModule;

use MD\Foundation\Exceptions\NotFoundException;

use Splot\Framework\Modules\AbstractModule;

use Splot\KnitModule\Knit\EntityFinder;
use Splot\KnitModule\Knit\Knit;
use Splot\KnitModule\Knit\SlowQueryLogger;

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

        $this->container->set('knit.stores.default', function($c) use ($defaultStoreConfig, $config) {
            return new $defaultStoreConfig['class'](
                $defaultStoreConfig,
                new SlowQueryLogger(
                    $c->get('logger_provider')->provide('Knit Default Store'),
                    $config->get('slow_query_logger.enabled'),
                    $config->get('slow_query_logger.threshold'),
                    $config->get('slow_query_logger.raise_to_level')
                )
            );
        });

        // register other stores
        foreach($stores as $name => $storeConfig) {
            $this->container->set('knit.stores.'. $name, function($c) use ($name, $storeConfig, $config) {
                return new $storeConfig['class'](
                    $storeConfig,
                    new SlowQueryLogger(
                        $c->get('logger_provider')->provide('Knit Store:'. $name),
                        $config->get('slow_query_logger.enabled'),
                        $config->get('slow_query_logger.threshold'),
                        $config->get('slow_query_logger.raise_to_level')
                    )
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