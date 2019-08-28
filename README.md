# melis-platform-framework-symfony
This bundle is the gateway in order for Symfony to make a connection to Melis platform. Like accessing
the registered services and automatically used the database connection of the platform.

## Getting Started
This instructions will get you a copy of the project up and running on your machine.

### Prerequisites
You will need to install the following in order to have this module running:
* melisplatform/melis-platform-frameworks

This will automatically be done when using composer.

### Installing
Run the composer command:

```
composer require melisplatform/melis-platform-framework-symfony
```

## Running the code
### Activating the module
Activating this bundle is just the same the way you activate your bundle inside symfony application. You just need to include it's bundle class to the list of bundle
inside symfony application most probably in bundles.php file.

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
### Services
##### MelisPlatformFrameworkSymfonyService
* This service is the gateway in order for Symfony to use the Melis Platform registered services.
* You can call this service inside Symfony application by calling it's registered
service id ``melis_platform.services``.

Example:
```
//Assuming we are inside of any custom Symfony controller that extends AbstractController of Symfony

//Calling the service
$melisServices = $this->get('melis_platform.services');
//Calling the MelisCoreTableLang service registered in Melis Platform
$languageTable = $melisServices->getService('MelisCoreTableLang');
//Calling fetchAll function inside MelisCoreTableLang service and convert the result to array
$languageList = $languageTable->fetchAll()->toArray();
```

## Authors

* **Melis Technology** - [www.melistechnology.com](https://www.melistechnology.com/)

See also the list of [contributors](https://github.com/melisplatform/melis-platform-framework-symfony/contributors) who participated in this project.


## License

This project is licensed under the OSL-3.0 License - see the [LICENSE](LICENSE)