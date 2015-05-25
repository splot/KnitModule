<?php
namespace Splot\KnitModule;

use MD\Foundation\Exceptions\NotFoundException;

use Knit\Extensions\Softdeletable;

use Splot\Framework\Modules\AbstractModule;

use Splot\KnitModule\Knit\EntityFinder;
use Splot\KnitModule\Knit\Knit;
use Splot\KnitModule\Knit\SlowQueryLogger;

class SplotKnitModule extends AbstractModule
{

    protected $commandNamespace = 'knit';

    public function configure() {
        parent::configure();

        $config = $this->getConfig();

        foreach(array(
            'slow_query_logger.enabled',
            'slow_query_logger.threshold',
            'slow_query_logger.raise_to_level'
        ) as $key) {
            $this->container->setParameter('knit.'. $key, $config->get($key));
        }

        /*****************************************************
         * CONFIGURE KNIT STORES
         *****************************************************/
        $stores = $config->get('stores');
        foreach($stores as $name => $storeConfig) {
            $notify = $name === 'default' ? array() : array(
                array('@knit', 'registerStore', array($name, '@'))
            );

            // register the store under its name
            $this->container->register('knit.stores.'. $name, array(
                'class' => $storeConfig['class'],
                'arguments' => array(
                    $storeConfig,
                    '@knit.stores.'. $name .'.slow_query_logger'
                ),
                'notify' => $notify
            ));

            // register the logger for this store
            $this->container->register('knit.stores.'. $name .'.logger', array(
                'factory' => array('@logger_provider', 'provide', array('Knit Store: '. $name))
            ));

            // register the wrapping slow query logger for this store
            $this->container->register('knit.stores.'. $name .'.slow_query_logger', array(
                'class' => 'Splot\\KnitModule\\Knit\\SlowQueryLogger',
                'arguments' => array(
                    '@knit.stores.'. $name .'.logger',
                    '%knit.slow_query_logger.enabled%',
                    '%knit.slow_query_logger.threshold%',
                    '%knit.slow_query_logger.raise_to_level%'
                )
            ));
        }

        /*****************************************************
         * CONFIGURE ENTITIES
         *****************************************************/
        $knitDefinition = $this->container->getDefinition('knit');

        foreach($config->get('entities') as $entityClass => $entityConfig) {
            if (isset($entityConfig['store'])) {
                $knitDefinition->addMethodCall('setStoreNameForEntity', array($entityClass, $entityConfig['store']));
            }

            if (isset($entityConfig['repository'])) {
                $knitDefinition->addMethodCall('setRepositoryClassForEntity', array($entityClass, $entityConfig['repository']));
            }
        }
    }

}