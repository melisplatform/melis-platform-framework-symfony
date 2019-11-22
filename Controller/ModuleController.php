<?php

namespace MelisPlatformFrameworkSymfony\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Yaml\Yaml;
use Zend\Config\Writer\PhpArray;

class ModuleController
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
                                        'dynamic-form-builder' => $this->processFormFields(),
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
        $langList = [];
        if(!empty($data['step6'])){
            /**
             * Loop through each language
             */
            foreach($data['step6'] as $lang => $data){
                $langLocale = explode('_', $lang);
                $transData[$langLocale[0]] = [];
                array_push($langList, $langLocale[0]);
                /**
                 * Loop through array that contains
                 * the translations
                 */
                foreach($data as $value){
                    $transArr = [];
                    /**
                     * Process the translations
                     */
                    foreach($value as $colName => $translations) {
                        //exclude field that starts in tcf
                        if(strpos($colName, 'tcf') === false) {
                            //don't include empty translations
//                            if (!empty($translations)) {
                                $key = 'tool_symfony_tpl_' . $colName;
                                if(strpos($colName, 'tcinputdesc') !== false)
                                    $key = str_replace('tcinputdesc', 'tooltip', $key);
                                $transArr[$key] = $translations;
//                            }
                        }
                    }
                    $transData[$langLocale[0]] = $transArr;
                }
            }
        }
        print_r($transData);
        print_r($langList);
    }

    private function processFormFields()
    {
        $builder = '$builder';
        $builder .= "
            ->add('alb_name', TextType::class, [
                'label' => 'tool_album_table_column_name',
                'label_attr' => [
                    'label_tooltip' => 'tool_album_table_column_name_tooltip'
                ],
                'constraints' => new NotBlank(),
                'required' => true,
            ])
            ->add('alb_song_num', null, [
                'label' => 'tool_album_table_column_song_no',
                'label_attr' => [
                    'label_tooltip' => 'tool_album_table_column_song_no_tooltip'
                ],
                'constraints' => [
                    new NotBlank(),
                    new Positive(['message' => 'tool_song_number_int_only'])
                ],
                'required' => true,
            ]);";
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
                     * Convert config.yaml.phtml into config.yaml file
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