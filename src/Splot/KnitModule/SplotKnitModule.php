<?php
namespace Splot\KnitModule;

use Splot\Framework\Modules\AbstractModule;

use Knit\AbstractModel;
use Knit\Store\MySQLStore;

class SplotKnitModule extends AbstractModule
{

    public function boot() {
        $config = $this->getConfig();

        if ($config->get('enabled')) {
            $this->container->set('model_store', function($c) use ($config) {
                $store = $config->get('default_store.class');
                return new $store($config->get('default_store.host'), $config->get('default_store.user'), $config->get('default_store.password'), $config->get('default_store.database'));
            }, true, true); 

            // by default set default mysql store
            AbstractModel::_setStore($this->container->get('model_store'));
        }
    }

}