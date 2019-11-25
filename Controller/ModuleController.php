<?php

namespace MelisPlatformFrameworkSymfony\Controller;

use MelisPlatformFrameworkSymfony\MelisServiceManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Yaml\Yaml;
use Zend\Config\Writer\PhpArray;

class ModuleController extends AbstractController
{
    private function sampleData()
    {
        $data = [];

        $data['step1'] = [
            'tcf-name' => 'LaravelTool',
            'tcf-tool-type' => 'db',
            'tcf-tool-edit-type' => 'modal',
            'tcf-create-microservice' => '0',
            'tcf-create-framework-tool' => '1',
            'tcf-tool-framework' => 'laravel',
        ];
        $data['step4']['tcf-db-table-cols'] = [
            'cal_id', 'cal_event_title', 'cal_date_start',
            'cal_date_end', 'cal_created_by', 'cal_last_update_by',
            'cal_date_last_update', 'cal_date_added'
        ];

        $data['step2'] = [
            'en_EN' => [
                'tcf-title' => 'Laravel Tool',
                'tcf-desc' => 'Laravel tool description',
                'tcf-lang-local' => 'en_EN'
            ],
            'fr_FR' => [
                'tcf-title' => '',
                'tcf-desc' => '',
                'tcf-lang-local' => 'fr_FR'
            ],
        ];

        $data['step5'] = [
            'tcf-db-table-col-editable' => [
                'cal_id', 'cal_event_title', 'cal_date_start',
                'cal_date_end', 'cal_created_by', 'cal_last_update_by',
                'cal_date_last_update', 'cal_date_added'
            ],
            'tcf-db-table-col-required' => [
                'cal_id', 'cal_event_title', 'cal_date_start',
                'cal_date_end', 'cal_date_added'
            ],
            'tcf-db-table-col-type' => [
                'MelisText', 'MelisCoreTinyMCE', 'Datepicker', 'Datepicker',
                'MelisText', 'MelisText', 'Datetimepicker', 'Datetimepicker'
            ]
        ];

        $data['step6'] = [
            'en_EN' => [
                'pri_tbl' => [
                    'cal_id' => 'ID',
                    'cal_id_tcinputdesc' => null,
                    'cal_event_title' => 'Event',
                    'cal_event_title_tcinputdesc' => null,
                    'cal_date_start' =>' Date start',
                    'cal_date_start_tcinputdesc' => null,
                    'cal_date_end' => 'Date end',
                    'cal_date_end_tcinputdesc' => null,
                    'cal_created_by' => 'Created by',
                    'cal_created_by_tcinputdesc' => null,
                    'cal_last_update_by' => 'Last update by',
                    'cal_last_update_by_tcinputdesc' => null,
                    'cal_date_last_update' => 'Date last update',
                    'cal_date_last_update_tcinputdesc' => null,
                    'cal_date_added' => 'Date created',
                    'cal_date_added_tcinputdesc' => null,
                    'tcf-lang-local'=> 'en_EN',
                    'tcf-tbl-type' => 'pri_tbl',
                ]
            ],
            'fr_FR' => [
                'pri_tbl' => [
                    'cal_id' => 'ID Fr',
                    'cal_id_tcinputdesc' => null,
                    'cal_event_title' => null,
                    'cal_event_title_tcinputdesc' => null,
                    'cal_date_start' => null,
                    'cal_date_start_tcinputdesc' => null,
                    'cal_date_end' => null,
                    'cal_date_end_tcinputdesc' => null,
                    'cal_created_by' => null,
                    'cal_created_by_tcinputdesc' => null,
                    'cal_last_update_by' => null,
                    'cal_last_update_by_tcinputdesc' => null,
                    'cal_date_last_update' => null,
                    'cal_date_last_update_tcinputdesc' => null,
                    'cal_date_added' => null,
                    'cal_date_added_tcinputdesc' => 'Sa Fr',
                    'tcf-lang-local'=> 'fr_FR',
                    'tcf-tbl-type' => 'pri_tbl',
                ]
            ]
        ];

        return $data;
    }

    public function createSymfonyModule()
    {
        try {

            $data = $this->sampleData();

            $moduleName = 'MyModule';
            $entityName = 'MyEntity';

            $frameworkDir = $_SERVER['DOCUMENT_ROOT'] . '/../thirdparty/Symfony';
            //check if framework exist
            if(file_exists($frameworkDir)){
                //check if framework is writable
                if(is_writable($frameworkDir)) {

                    $destination = $frameworkDir . '/src/Module/' . $moduleName;
                    $source = dirname(__FILE__) . '/../install/moduleTemplate/SymfonyTplBundle';

                    /**
                     * Check if module already exist
                     */
                    if (!file_exists($destination)) {
                        $res = $this->xcopy($source, $destination);
                        if ($res) {
                            if(is_writable($destination)) {
                                /**
                                 * Add Table columns to the config
                                 */
                                $this->processTableColumns($data, $destination);
                                /**
                                 * Process module translations
                                 */
                                $this->processTranslations($data, $destination);
                                /**
                                 * Process Form Builder
                                 */
                                $builder = $this->processFormBuilder($data, $moduleName);
                                /**
                                 * Process the replacement of file
                                 * and contents
                                 */
                                $this->mapDirectory($destination,
                                    [
                                        'SymfonyTpl' => ucfirst($moduleName),
                                        'SYMFONYTPL' => strtoupper($moduleName),
                                        'symfony_tpl' => $this->generateCase($moduleName, 2),
                                        'SampleEntity' => ucfirst($entityName),
                                        'sampleEntity' => strtolower($entityName),
                                        'dynamic-form-builder' => $builder,
                                    ]
                                );
                            }else{
                                exit('not writable');
                            }
                        }
                    }else{
                        exit('already exist');
                    }
                }else{

                }
            }else{

            }
            return new JsonResponse([]);
        }catch (\Exception $ex){
            exit($ex->getMessage());
        }
    }

    /**
     * @param $data
     * @param $dir
     * @throws \Exception
     */
    private function processTableColumns($data, $dir)
    {
        try {
            $configFile = $dir . '/Resources/config/config.yaml.phtml';
            if(file_exists($configFile)){
                if(is_writable($configFile)){
                    $configData = include($configFile);
                    if (!empty($configData)) {
                        /**
                         * Insert table columns
                         * and searchable columns
                         */
                        if (!empty($configData['symfony_tpl']['table']['symfony_tpl_table'])) {
                            if (!empty($data['step4'])) {
                                $colList = [];
                                $columns = $data['step4']['tcf-db-table-cols'];
                                foreach ($columns as $key => $col) {
                                    $colList[$this->generateCase($col, 4)] = [
                                        'text' => 'tool_symfony_tpl_' . $col,
                                        'class' => [
                                            'width' => '20%',
                                            'padding-right' => 0
                                        ],
                                        'sortable' => true,
                                    ];
                                }

                                $configData['symfony_tpl']['table']['symfony_tpl_table']['columns'] = $colList;
                                $configData['symfony_tpl']['table']['symfony_tpl_table']['searchables'] = $columns;
                                $writer = new PhpArray();
                                file_put_contents($configFile, $writer->toString($configData));
                            }
                        }
                    }
                }else{
                    throw new \Exception($configFile.' file is not writable.');
                }
            }else{
                throw new \Exception($configFile.' file does not exist.');
            }
        }catch (\Exception $ex){
            throw new \Exception('Cannot create module: '. $ex->getMessage());
        }
    }

    /**
     * @param $data
     * @param $moduleDir
     */
    private function processTranslations($data, $moduleDir)
    {
        $transFolder = $moduleDir . '/Resources/translations';

        $transData = [];
        $notEmptyKeyHolder = [];
        if(!empty($data['step6'])){
            /**
             * Loop through each language
             */
            foreach($data['step6'] as $lang => $transContainer){
                $langLocale = explode('_', $lang);
                $transData[$langLocale[0]] = [];
                /**
                 * Loop through array that contains
                 * the translations
                 */
                foreach($transContainer as $value){
                    //include step2 translations
                    $value = array_merge($value, $data['step2'][$lang]);
                    /**
                     * Process the translations
                     */
                    foreach($value as $colName => $translations) {
                        //exclude field that starts in tcf
                        if (!in_array($colName, ['tcf-lang-local', 'tcf-tbl-type'])){

                            if(strpos($colName, 'tcf') !== false)
                                $colName = str_replace('tcf-', '', $colName);

                            $key = 'tool_symfony_tpl_' . $colName;
                            if(strpos($colName, 'tcinputdesc') !== false)
                                $key = str_replace('tcinputdesc', 'tooltip', $key);

                            $transData[$langLocale[0]][$key] = $translations;

                            if (!empty($translations)) {
                                $notEmptyKeyHolder[$key] = $translations;
                            }
                        }
                    }
                }
            }
        }

        /**
         * Get value from a language
         * and assign it to those fields that
         * are empty from the different language
         */
        foreach ($transData As $local => $texts)
            foreach ($notEmptyKeyHolder As $key => $text)
                if (empty($texts[$key]))
                    $transData[$local][$key] = $text;

        /**
         * Add value to fields that are empty
         */
        foreach($transData as $local => $trans){
            foreach($trans as $key => $text){
                if(empty($text)){
                    $value = str_replace('tool_symfony_tpl_', '', $key);
                    $value = str_replace('_', ' ', $value);
                    $transData[$local][$key] = ucfirst($value);
                }
            }
        }

        /**
         * Lets create a file and put the translations on it
         */
        $writer = new PhpArray();
        foreach($transData as $lang => $translations){
            $fileName = $transFolder.'/messages.'.$lang.'.yaml.phtml';
            $fp = fopen($fileName,'x+');
            fwrite($fp, $writer->toString($translations));
            fclose($fp);
        }
    }

    /**
     * Generate form builder
     *
     * @param $data
     * @param $moduleName
     * @return string
     */
    private function processFormBuilder($data, $moduleName)
    {
        $builder = '';
        if(!empty($data['step5'])) {
            $fieldsInfo = $data['step5'];
            $builder = '$builder';
            $modName = $this->generateCase($moduleName, 2);

            $fields = $fieldsInfo['tcf-db-table-col-editable'] ?? [];
            $fieldsRequired = $fieldsInfo['tcf-db-table-col-required'] ?? [];
            $fieldsType = $fieldsInfo['tcf-db-table-col-type'] ?? [];

            foreach($fields as $key => $fieldName){
                //check if field is required
                $isRequired = (in_array($fieldName, $fieldsRequired)) ? true : false;
                //get field type
                if(!empty($fieldsType[$key])){
                    if($fieldsType[$key] == 'MelisText')
                        $type = 'TextType';
                    else
                        $type = 'TextType';
                }else{
                    $type = 'TextType';
                }

                $builder .= "
                ->add('".$fieldName."', ".$type."::class, [
                    'label' => 'tool_".$modName."_".$fieldName."',
                    'label_attr' => [
                        'label_tooltip' => 'tool_".$modName."_".$fieldName."_tooltip'
                    ]";
                if($isRequired) {
                    $builder .= ",\n\t\t\t\t\t'constraints' => new NotBlank(),
                    'required' => true,
                    ])";
                }else{
                    $builder .= "])";
                }
            }
        }
        return $builder;
    }

    /**
     * Copy a file, or recursively copy a folder and its contents
     * @author      Aidan Lister <aidan@php.net>
     * @version     1.0.1
     * @link        http://aidanlister.com/2004/04/recursively-copying-directories-in-php/
     * @param       string   $source    Source path
     * @param       string   $dest      Destination path
     * @param       int      $permissions New folder creation permissions
     * @return      bool     Returns true on success, false on failure
     */
    private function xcopy($source, $dest)
    {
        // Check for symlinks
        if (is_link($source))
        {
            return symlink(readlink($source), $dest);
        }

        // Simple copy for a file
        if (is_file($source))
        {
            return copy($source, $dest);
        }

        // Make destination directory
        if (!is_dir($dest))
        {
            mkdir($dest, '0777', true);
        }

        // Loop through the folder
        $dir = dir($source);
        while (false !== $entry = $dir->read())
        {
            // Skip pointers
            if ($entry == '.' || $entry == '..')
            {
                continue;
            }
            // Deep copy directories
            $this->xcopy("$source/$entry", "$dest/$entry");
        }

        // Clean up
        $dir->close();
        return true;
    }

    /**
     * This method will map a directory to change some specific word
     * that match the target and replace by new word
     *
     * @param $dir
     * @param $contentUpdate
     */
    private function mapDirectory($dir, $contentUpdate)
    {
        $cdir = scandir($dir);
        foreach ($cdir as $key => $value)
        {
            if (!in_array($value,array(".","..")))
            {
                if (is_dir($dir . '/' . $value))
                {
                    $this->mapDirectory($dir . '/' . $value, $contentUpdate);
                }
                else
                {
                    foreach($contentUpdate as $search => $replace) {
                        $newFileName = str_replace($search, $replace, $value);
                        if ($value != $newFileName) {
                            rename($dir . '/' . $value, $dir . '/' . $newFileName);
                            $value = $newFileName;
                        }

                        $fileName = $dir . '/' . $value;
                        $this->replaceFileTextContent($fileName, $fileName, $search, $replace);
                    }
                    /**
                     * Convert example.yaml.phtml into example.yaml file
                     */
                    if (strpos($value, '.yaml.phtml') !== false) {
                        $data = Yaml::dump(include($fileName), 10);
                        $newYamlFName = $dir . '/' . str_replace('.phtml', '', $value);
                        rename($fileName, $newYamlFName);
                        file_put_contents($newYamlFName, $data);
                    }
                }
            }
        }
    }

    /**
     * This method is replacing a single string match on file content
     * and store/save after replacing
     *
     * @param String $fileName
     * @param String $outputFileName
     * @param String $lookupText
     * @param String $replaceText
     */
    private function replaceFileTextContent($fileName, $outputFileName, $lookupText, $replaceText)
    {
        $file = @file_get_contents($fileName);
        $file = str_replace($lookupText, $replaceText, $file);
        @file_put_contents($outputFileName, $file);
    }

    /**
     * Generate case (default is snake case)
     *
     * @param $string
     * @param int $case
     * @return mixed|string
     */
    private function generateCase($string, $case = 1)
    {
        $snakeCase = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $string));
        switch($case){
            case 1:
                $str = $snakeCase;
                break;
            case 2://underscore case
                $str = str_replace('-', '_', $snakeCase);
                break;
            case 3:
                $str = $this->generateModuleNameCase($string);
                break;
            case 4: //underscore case to camel case
                $str = lcfirst(str_replace('_', '', ucwords($snakeCase, '_')));
                break;
            default:
                $str = $snakeCase;

        }
        return $str;
    }

    /**
     * @param $str
     * @return mixed|string|string[]|null
     */
    private function generateModuleNameCase($str) {
        //store the given module name
        $strBp = $str;

        $replaceMent = "$1 $2";
        $i = array("-","_");

        /**
         * Process the module name
         * generation
         */
        $str = preg_replace('/([a-z])([A-Z])/',  $replaceMent, $str);
        $str = str_replace($i, ' ', $str);
        $str = str_replace(' ', '', ucwords(strtolower($str)));
        $str = strtolower(substr($str,0,1)).substr($str,1);
        $str = ucfirst($str);

        /**
         * if the given name is already correct,
         * we just need to return it, else we make
         * it small letters aside from first letter
         */
        if($strBp == $str){
            return $str;
        }else{
            return $this->generateModuleNameCase($strBp);
        }
    }

    private function underscoreToCamelCase($string, $capitalizeFirstCharacter = false)
    {

        $str = str_replace('_', '', ucwords($string, '_'));

        if (!$capitalizeFirstCharacter) {
            $str = lcfirst($str);
        }

        return $str;
    }
}