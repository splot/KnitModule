<?php
namespace Splot\KnitModule;

use Splot\Framework\Modules\AbstractModule;

use MD\Foundation\Exceptions\NotFoundException;

use Knit\Knit;

class SplotKnitModule extends AbstractModule
{

    public function boot() {
        $config = $this->getConfig();
        $logProvider = $this->container->get('log_provider');

        /*****************************************************
         * SETUP KNIT
         *****************************************************/
        $stores = $config->get('stores');

        // setup the default store in a special way
        $defaultStoreConfig = $stores['default'];
        unset($stores['default']);
        $defaultStore = new $defaultStoreConfig['class']($defaultStoreConfig);
        $defaultStore->setLogger($logProvider->provideLogger('Knit Default Store'));
        $this->container->set('knit.stores.default', $defaultStore);

        // setup Knit and register it as a service
        $knit = new Knit($defaultStore);
        $this->container->set('knit', $knit, true);

        /*****************************************************
         * ADD OTHER STORES
         *****************************************************/
        foreach($stores as $name => $storeConfig) {
            // instantiate
            $store = new $storeConfig['class']($storeConfig);
            $store->setLogger($logProvider->provideLogger('Knit Store: '. $name));

            // register in Knit
            $knit->registerStore($name, $store);

            // register as a service as well
            $this->container->set('knit.stores.'. $name, $store, true);
        }

        /*****************************************************
         * CONFIGURE ENTITIES
         *****************************************************/
        $entities = $config->get('entities');

        foreach($entities as $entityClass => $entityConfig) {
            if (isset($entityConfig['store'])) {
                $knit->setStoreNameForEntity($entityClass, $entityConfig['store']);
            }

            if (isset($entityConfig['repository'])) {
                $knit->setRepositoryClassForEntity($entityClass, $entityConfig['repository']);
            }
        }
    }

}