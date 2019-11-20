# melis-platform-framework-symfony
This bundle is the gateway for Symfony in order to make a connection to Melis platform like accessing
the registered services and automatically use the database connection of the platform.

## Getting Started
These instructions will get you a copy of the project up and running on your machine.

### Prerequisites
You will need to install the following in order to have this module running:
* melisplatform/melis-platform-frameworks

It will automatically be done when using composer.

### Installing
Run the composer command:

```
composer require melisplatform/melis-platform-framework-symfony
```

## Running the code
### Activating the module
Activating this bundle is just the same way you activate your bundle inside symfony application. You just need to include its bundle class to the list of bundles inside symfony application (most probably in bundles.php file).

```
return [
    //All of the symfony activated bundles here
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    ...
    ...
    etc.
    //Melis Platform Custom Bundles
    MelisPlatformFrameworkSymfony\MelisPlatformFrameworkSymfonyBundle::class => ['all' => true]
];
```
### Accessing Melis Platform Service
##### MelisServiceManager Class
* This class is the gateway for Symfony in order to make a connection to Melis platform. Therefore,
using this class we can get all of Melis platform registered services.
* You can call this class inside symfony application as a service by calling its registered
service key ``melis_platform.service_manager`` or by using a Dependency Injection. (See example below)

Example:
```
//Using Dependency Injection

//Assuming we are inside a controller
protected $melisServiceManager;

//Inject MelisServiceManager in the controller constructor function
public function __construct(MelisServiceManager $melisServiceManager)
{
    $this->melisServiceManager = $melisServiceManager;
}
//And then we can use the melis provider like this
//to get the language list of the Back Office
$melisCoreTableLang = $this->melisServiceManager->getService('MelisCoreTableLang');
$melisCorelangList = $melisCoreTableLang->fetchAll()->toArray();

//Using service key (melis_platform.service_manager)

//Assuming we are inside of any custom Symfony controller that extends AbstractController of Symfony
//Calling the service
$melisServices = $this->get('melis_platform.service_manager');
//Calling the MelisCoreTableLang service registered in Melis Platform
$languageTable = $melisServices->getService('MelisCoreTableLang');
//Calling fetchAll function inside MelisCoreTableLang service and convert the result to array
$languageList = $languageTable->fetchAll()->toArray();
```
* Bundles that are inside the Symfony skeleton might have a problem accessing the ``melis_platform.service_manager`` service key
inside their Controller that extends AbstractController since AbstractController only uses a limited container that only contains some services.

    But we can still use the ``melis_platform.service_manager`` service key by overriding the ``getSubscribedServices`` function of the AbstractController
    inside our Controller to register our service.
    
``` 
public static function getSubscribedServices()
{
    return array_merge(parent::getSubscribedServices(),
        [
            'melis_platform.service_manager' => MelisServiceManager::class,
        ]);
}
```



### Event Listeners
##### DatabaseSwitcherListener
* This listener will force Symfony to use the Melis Platform database.
##### SymfonyTranslationsListener
* This listener will get all of Symfony translations and store it inside a file (Resources/translations/melis/symfony-translations.phtml) 
so that Melis Platform can use this translations. This file MUST be writable.

## Authors

* **Melis Technology** - [www.melistechnology.com](https://www.melistechnology.com/)

See also the list of [contributors](https://github.com/melisplatform/melis-platform-framework-symfony/contributors) who participated in this project.


## License

This project is licensed under the OSL-3.0 License - see the [LICENSE](LICENSE)
