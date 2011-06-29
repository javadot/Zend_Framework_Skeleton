<?php

/**
 * Resource for getting modules config
 */
class SKL_Application_Resource_Doctrine
    extends Zend_Application_Resource_ResourceAbstract
{
    const DEVELOPMENT_ENV = 'development';

    /**
     * Enviroment type
     * 
     * @var string
     */
    protected $_enviroment;


    public function init()
    {
        $this->_loadDoctrineNamespaces();
        
        return $this->_getEntityManager();
    }

    /**
     * Get entity manager
     * 
     * @return \Doctrine\ORM\EntityManager
     */
    protected function _getEntityManager()
    {
        $options = $this->getOptions();
        
        $config = new \Doctrine\ORM\Configuration();
        
        // Set cache
        $cache = $this->_getCache();
        
        $config->setMetadataCacheImpl($cache);
        $config->setQueryCacheImpl($cache);

        // Set proxy
        $autoGenerateProxy = ($this->_getEnviroment() == self::DEVELOPMENT_ENV);
        $config->setAutoGenerateProxyClasses($autoGenerateProxy);

        $config->setProxyDir($options['path']['proxies']);
        $config->setProxyNamespace($options['proxy']['namespace']);

        // Set metadata driver
        $driverImpl = $config->newDefaultAnnotationDriver($options['path']['entities']);
        $config->setMetadataDriverImpl($driverImpl);

        // Database connection config
        $connectionOptions = array(
            'host'     => $options['conn']['host'],
            'driver'   => $options['conn']['driver'],
            'user'     => $options['conn']['user'],
            'password' => $options['conn']['pass'],
            'dbname'   => $options['conn']['dbname'],
        );

        $em = \Doctrine\ORM\EntityManager::create($connectionOptions, $config);

        return $em;
    }    

    /**
     * Load doctrine namespaces
     */
    protected function _loadDoctrineNamespaces()
    {
        $this->getBootstrap()->bootstrap('autoload');
    }
    
    /**
     * Get cache for Doctrine
     * 
     * @return \Doctrine\Common\Cache\Cache
     */
    protected function _getCache()
    {
        $cache = ($this->_getEnviroment() == self::DEVELOPMENT_ENV)
                 ? new \Doctrine\Common\Cache\ArrayCache
                 : new \Doctrine\Common\Cache\ApcCache;
        
        return $cache;
    }
    
    /**
     * Get enviroment type
     * 
     * @return string
     */
    protected function _getEnviroment()
    {
        if ( ! $this->_enviroment ) {
            // Set correct enviroment for cli and web
            $isWebServerRequest = isset($_SERVER['SERVER_NAME']);
            $this->_enviroment = $isWebServerRequest 
                                  ? APPLICATION_ENV 
                                  : self::DEVELOPMENT_ENV;
        }
        
        return $this->_enviroment;
    }
}