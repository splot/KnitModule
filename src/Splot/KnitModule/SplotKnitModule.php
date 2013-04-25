<?php
namespace Splot\KnitModule;

use Splot\Framework\Modules\AbstractModule;
use Splot\Log\LogContainer;

use Knit\Knit;

class SplotKnitModule extends AbstractModule
{

    public function boot() {
        $config = $this->getConfig();

        /*****************************************************
         * SETUP KNIT
         *****************************************************/
        $stores = $config->get('stores');

        // setup the default store in a special way
        $defaultStoreConfig = $stores['default'];
        unset($stores['default']);
        $defaultStore = new $defaultStoreConfig['class']($defaultStoreConfig['host'], $defaultStoreConfig['user'], $defaultStoreConfig['password'], $defaultStoreConfig['database']);
        $defaultStore->setLogger(LogContainer::create('Knit Default Store'));
        $this->container->set('knit.stores.default', $defaultStore);

        // setup Knit and register it as a service
        $knit = new Knit($defaultStore);
        $this->container->set('knit', $knit, true);

        /*****************************************************
         * ADD OTHER STORES
         *****************************************************/
        foreach($stores as $name => $storeConfig) {
            // instantiate
            $store = new $storeConfig['class']($storeConfig['host'], $storeConfig['user'], $storeConfig['password'], $storeConfig['database']);
            $store->setLogger(LogContainer::create('Knit Store: '. $name));

            // register in Knit
            $knit->registerStore($name, $store);

            // register as a service as well
            $this->container->set('knit.stores.'. $name, $store, true);
        }

        // @todo Map entity classes to stores using the config.
    }

}