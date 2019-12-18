<?php

namespace App\Bundle\SymfonyTpl\Service;

use MelisPlatformFrameworkSymfony\MelisServiceManager;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SymfonyTplService
{
    /**
     * @var $melisServiceManager
     */
    protected $melisServiceManager;
    /**
     * @var $translator
     */
    protected $translator;

    /**
     * SymfonyToolService constructor.
     * @param MelisServiceManager $melisServiceManager
     * @param TranslatorInterface $translator
     */
    public function __construct(MelisServiceManager $melisServiceManager, TranslatorInterface $translator)
    {
        $this->melisServiceManager = $melisServiceManager;
        $this->translator = $translator;
    }

    /**
     * Update table display depending on
     * column display type in the config
     *
     * @param $data
     * @param $config
     * @return mixed
     */
    public function updateTableDisplay($data, $config)
    {
        foreach($data as $key => $val){
            foreach($val as $column => $info){
                if(array_key_exists($column, $config)){
                    /**
                     * Display a dot color instead of it's original value
                     */
                    if($config[$column] == 'dot_color'){
                        if(!is_array($info)) {
                            $data[$key][$column] = '<span class="text-'.($info ? 'success' : 'danger').'"><i class="fa fa-fw fa-circle"></i></span>';
                        }
                    }
                    /**
                     * Display the data with limit
                     */
                    elseif($config[$column] == 'char_length_limit'){
                        if(!is_array($info)) {
                            if (strlen($info) > 50)
                                $data[$key][$column] = substr($info, 0, 50) . '...';
                        }
                    }
                    /**
                     * Display the user name instead of it's id
                     */
                    elseif($config[$column] == 'admin_name'){
                        if(is_array($info)) {
                            if (!empty($info['usrName'])) {
                                $data[$key][$column] = $info['usrName'];
                            }
                        }
                    }
                    /**
                     * Display the template name instead of it's id
                     */
                    elseif($config[$column] == 'tpl_name'){
                        if(is_array($info)) {
                            if (!empty($info['tplName'])) {
                                $data[$key][$column] = $info['tplName'];
                            }
                        }
                    }
                    /**
                     * Display the language name instead of it's id
                     */
                    elseif($config[$column] == 'lang_name'){
                        if(is_array($info)) {
                            if (!empty($info['langCmsName'])) {
                                $data[$key][$column] = $info['langCmsName'];
                            }
                        }
                    }
                    /**
                     * Display the site name instead of it's id
                     */
                    elseif($config[$column] == 'site_name'){
                        if(is_array($info)) {
                            if (!empty($info['siteName'])) {
                                $data[$key][$column] = $info['siteName'];
                            }
                        }
                    }
                    /**
                     * Display its original data
                     */
                    elseif($config[$column] == 'raw_view'){
                        if(is_array($info)) {
                            if (!empty($info['rawData'])) {
                                $data[$key][$column] = $info['rawData'];
                            }
                        }
                    }
                }
            }
        }
        return $data;
    }

    /**
     * Get form errors
     * @param FormInterface $form
     * @return array
     */
    public function getErrorsFromForm(FormInterface $form)
    {
        $errors = array();
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }
        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = $this->getErrorsFromForm($childForm)) {
                    $errMessage = $childErrors[0] ?? null;
                    $fieldLabel = $childForm->getConfig()->getOption('label');
                    $fieldLabel = $this->translator->trans($fieldLabel);
                    $errors[$childForm->getName()] = ['error_message' => $errMessage, 'label' => $fieldLabel];
                }
            }
        }

        return $errors;
    }

    /**
     * Add logs to notification
     *
     * @param $title
     * @param $message
     * @param string $icon
     * @throws \Exception
     */
    public function addToFlashMessenger($title, $message, $icon = 'glyphicon-info-sign')
    {
        $icon = 'glyphicon '.$icon;
        $flashMessenger = $this->melisServiceManager->getService('MelisCoreFlashMessenger');
        $flashMessenger->addToFlashMessenger($title, $message, $icon);
    }

    /**
     * Save logs
     *
     * @param $title
     * @param $message
     * @param $success
     * @param $typeCode
     * @param $itemId
     * @throws \Exception
     */
    public function saveLogs($title, $message, $success, $typeCode, $itemId)
    {
        $logs = $this->melisServiceManager->getService('MelisCoreLogService');
        $logs->saveLog($title, $message, $success, $typeCode, $itemId);
    }

    /**
     * Translate some text in the config
     * @param $config
     * @return mixed
     */
    public function translateConfig($config)
    {
        foreach($config as $key => $value){
            if(is_array($value)){
                $config[$key] = $this->translateConfig($value);
            }else{
                $config[$key] = $this->translator->trans($value);
            }
        }
        return $config;
    }
}