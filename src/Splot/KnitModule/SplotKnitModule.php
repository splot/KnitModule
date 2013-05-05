<?php
namespace Splot\KnitModule;

use Splot\Framework\Modules\AbstractModule;

use MD\Foundation\Exceptions\NotFoundException;

use Knit\Knit;

class SplotKnitModule extends AbstractModule
{

    public function boot() {
        $config = $this->getConfig();
        $loggerFactory = $this->container->get('logger_factory');

        /*****************************************************
         * SETUP KNIT
         *****************************************************/
        $stores = $config->get('stores');

        // setup the default store in a special way
        $defaultStoreConfig = $stores['default'];
        unset($stores['default']);
        $defaultStore = new $defaultStoreConfig['class']($defaultStoreConfig['host'], $defaultStoreConfig['user'], $defaultStoreConfig['password'], $defaultStoreConfig['database']);
        $defaultStore->setLogger($loggerFactory->create('Knit Default Store'));
        $this->container->set('knit.stores.default', $defaultStore);

        // setup Knit and register it as a service
        $knit = new Knit($defaultStore);
        $this->container->set('knit', $knit, true);

        /*****************************************************
         * ADD OTHER STORES
         *****************************************************/
        foreach($stores as $name => $storeConfig) {
            // prepare config array by fetching from config again to make sure proper exceptions are thrown if value not found
            $storeConfigArray = array(
                'host' => $config->get('stores.'. $name .'.host'),
                'port' => null,
                'user' => $config->get('stores.'. $name .'.user'),
                'password' => $config->get('stores.'. $name .'.password'),
                'database' => $config->get('stores.'. $name .'.database')
            );
            // port is not mandatory
            try {
                $storeConfigArray['port'] = $config->get('stores.'. $name .'.port');
            } catch (NotFoundException $e) {}

            // instantiate
            $store = new $storeConfig['class']($storeConfigArray['host'], $storeConfigArray['user'], $storeConfigArray['password'], $storeConfigArray['database'], $storeConfigArray['port']);
            $store->setLogger($loggerFactory->create('Knit Store: '. $name));

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