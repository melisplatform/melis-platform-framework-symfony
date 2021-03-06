<?php

namespace MelisPlatformFrameworkSymfony\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Laminas\Config\Writer\PhpArray;

/**
 * Class SymfonyTranslationsListener
 * @package MelisPlatformFrameworkSymfony\EventListener
 */
class SymfonyTranslationsListener
{
    private $container;

    /**
     * SymfonyTranslationsListener constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    /**
     * Store translations to a file
     * so that Melis Platform can use it
     */
    public function onKernelRequest()
    {
        try {
            /**
             * THIS FILE MUST BE WRITABLE IN ORDER
             * TO WRITE ALL THE TRANSLATIONS
             */
            $transPath = __DIR__. '/../Resources/translations/melis/symfony-translations.phtml';

            if(file_exists($transPath)){
                if(is_writable($transPath)) {
                    /**
                     * Get all symfony translations
                     */
                    $translator = $this->container->get('translator');
                    //get only the translations on messages
                    $transList = $translator->getCatalogue()->all('messages');

                    /**
                     * We use Laminas PhpArray to store
                     * the translations to the file
                     */
                    $writer = new PhpArray();
                    file_put_contents($transPath, $writer->toString($transList));
                }
            }
        }catch (\Exception $ex) {

        }
    }
}