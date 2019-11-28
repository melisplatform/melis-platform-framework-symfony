<?php

namespace MelisPlatformFrameworkSymfony\Controller;

use http\Exception\InvalidArgumentException;
use MelisPlatformFrameworkSymfony\MelisServiceManager;
use mysql_xdevapi\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Yaml\Yaml;
use Zend\Config\Writer\PhpArray;
use function Composer\Autoload\includeFile;

class ModuleController extends AbstractController
{
    private $primary_table = '';
    private $pt_entity_name = '';
    private $pt_pk = '';
    private $secondary_table = '';
    private $st_entity_name = '';
    private $st_pk = '';
    private $module_name = '';
    private $has_language = false;

    private function sampleData()
    {
        $data = [];

        $data['step1'] = [
            'tcf-name' => 'SymfonyTool',
            'tcf-tool-type' => 'db',
            'tcf-tool-edit-type' => 'modal',
            'tcf-create-microservice' => '0',
            'tcf-create-framework-tool' => '1',
            'tcf-tool-framework' => 'laravel',
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

        $data['step3'] = [
            'tcf-db-table' => 'melis_calendar',
            'tcf-db-table-has-language' => 1,
            'tcf-db-table-language-tbl' => 'melis_cms_news_texts',
            'tcf-db-table-language-pri-fk' => 'cnews_id',
            'tcf-db-table-language-lang-fk' => 'cnews_lang_id',
        ];

        $data['step4']['tcf-db-table-cols'] = [
            'cal_id', 'cal_event_title', 'cal_date_start',
            'cal_date_end', 'cal_created_by', 'cal_last_update_by',
            'cal_date_last_update', 'cal_date_added',
            'tclangtblcol_cnews_text_id', 'tclangtblcol_cnews_title'
        ];


        $data['step5'] = [
            'tcf-db-table-col-editable' => [
                'cal_id', 'cal_event_title', 'cal_date_start',
                'cal_date_end', 'cal_created_by', 'cal_last_update_by',
                'cal_date_last_update', 'cal_date_added',
                'tclangtblcol_cnews_text_id', 'tclangtblcol_cnews_title'
            ],
            'tcf-db-table-col-required' => [
                'cal_id', 'cal_event_title', 'cal_date_start',
                'cal_date_end', 'cal_date_added', 'tclangtblcol_cnews_text_id'
            ],
            'tcf-db-table-col-type' => [
                'MelisText', 'MelisCoreTinyMCE', 'Datepicker', 'Datepicker',
                'MelisText', 'MelisText', 'Datetimepicker', 'Datetimepicker',
                'MelisText', 'MelisText'
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

    /**
     * @param $data
     */
    private function setTableNameAndEntityName($data)
    {
        if(!empty($data['step3']['tcf-db-table'])){
            $this->primary_table = $data['step3']['tcf-db-table'];
            $this->pt_entity_name = str_replace('melis_', '', $this->primary_table);
            $this->pt_entity_name = ucfirst($this->generateCase($this->pt_entity_name, 4));
            $this->has_language = $data['step3']['tcf-db-table-has-language'] ?? false;
        }else{
            $this->pt_entity_name = $this->module_name;
        }

        //check if we have a secondary table (language table)
        if($this->has_language){
            $this->secondary_table = $data['step3']['tcf-db-table-language-tbl'];
            $this->st_entity_name = str_replace('melis_', '', $this->secondary_table);
            $this->st_entity_name = ucfirst($this->generateCase($this->st_entity_name, 4));
        }
    }

    public function createSymfonyModule()
    {
        $result = [
            'success' => true,
            'message' => '',
        ];

        $data = $this->sampleData();

        if(!empty($data['step1']['tcf-name'])){
            //get module name
            $this->module_name = $this->generateCase($data['step1']['tcf-name'], 3);
            //Get Primary table name
            //use table as our entity name
            $this->setTableNameAndEntityName($data);

            $frameworkDir = $_SERVER['DOCUMENT_ROOT'] . '/../thirdparty/Symfony';
            $destination = $frameworkDir . '/src/Bundle/' . $this->module_name;
            $source = dirname(__FILE__) . '/../install/moduleTemplate/SymfonyTpl';
            //check if framework exist
            if(file_exists($frameworkDir)){
                //check if framework is writable
                if(is_writable($frameworkDir)) {
                    /**
                     * Check if module already exist
                     */
                    if (!file_exists($destination)) {
                        try {
                            //get tables primary key
                            $this->pt_pk = $this->getTablePrimaryKey($this->primary_table);
                            $this->st_pk = $this->getTablePrimaryKey($this->secondary_table);
                            //copy bundle template
                            $res = $this->xcopy($source, $destination);

                            if ($res) {
                                if (is_writable($destination)) {
                                    /**
                                     * Add Table columns to the config
                                     */
                                    $this->processTableColumns($data, $destination);
                                    /**
                                     * Process module translations
                                     */
                                    $this->processTranslations($data, $destination);
                                    /**
                                     * Process Form Builder and Entity
                                     */
                                    $this->processFormBuilderAndEntity($data, $destination);
                                    /**
                                     * Process the replacement of file
                                     * and contents
                                     */
                                    $this->mapDirectory($destination,
                                        [
                                            'SymfonyTpl' => ucfirst($this->module_name),
                                            'symfonyTpl' => lcfirst($this->module_name),
                                            'SYMFONYTPL' => strtoupper($this->module_name),
                                            'symfony_tpl' => $this->generateCase($this->module_name, 2),
                                            'SampleEntity' => ucfirst($this->pt_entity_name),
                                            'sampleEntity' => strtolower($this->pt_entity_name),
                                            'sample_table_name' => $this->primary_table,
                                            'sample_primary_id' => $this->pt_pk,
                                            'SamplePrimaryId' => ucfirst($this->generateCase($this->pt_pk, 4)),
                                            'samplePrimaryId' => $this->generateCase($this->pt_pk, 4),
                                        ]
                                    );

                                    /**
                                     * After we successfully created the bundle,
                                     * lets try to activate it
                                     */
                                    $this->activateBundle($frameworkDir);
                                } else {
                                    throw new \Exception('File not writable: '. $destination);
                                }
                            }
                        }catch (\Exception $ex){
                            if(file_exists($destination))
                                $this->deleteDir($destination);

                            $result['message'] = 'Error occurred while creating module: '.$ex->getMessage();
                            $result['success'] = false;
                        }
                    }else{
                        $result['message'] = 'Module name already exist.';
                        $result['success'] = false;
                    }
                }else{
                    $result['message'] = 'Symfony framework folder is not writable';
                    $result['success'] = false;
                }
            }else{
                $result['message'] = 'Symfony framework skeleton does not exist';
                $result['success'] = false;
            }
        }else{
            $result['message'] = 'Module name is required';
            $result['success'] = false;
        }

        return new JsonResponse([$result]);
    }

    /**
     * Function to activate bundle
     * as well as including the bundle route
     * to the main route
     *
     * @param $frameworkDir
     * @throws \Exception
     */
    private function activateBundle($frameworkDir)
    {
        try {
            $bundle = $frameworkDir . '/config/bundles.php';
            if (file_exists($bundle)) {
                if (is_writable($bundle)) {
                    //Add newly created bundle to the bundle lists
                    /**
                     * we will find a ]; inside the bundles file
                     * and then we are going to insert our bundle
                     * just above it using a regex
                     */
                    $bundleKey = "\tApp\Bundle\\" . $this->module_name . "\\" . $this->module_name . "Bundle::class => ['all' => true],\n";
                    $bodyRegex = '/(];(?![\s\S]*];[\s\S]*$))/im';
                    $newBundles = preg_replace($bodyRegex, "$bundleKey$1", file_get_contents($bundle));
                    file_put_contents($bundle, $newBundles);

                    //include Bundle routes in the main routes
                    $routesPath = $frameworkDir . '/config/routes.yaml';
                    $routes = Yaml::parseFile($routesPath);
                    $routes[$this->generateCase($this->module_name, 2)] = [
                        'resource' => '@'.$this->module_name.'Bundle/Resources/config/routing.yaml',
                        'prefix' => '/'
                    ];
                    $newRoutes = Yaml::dump($routes);
                    file_put_contents($routesPath, $newRoutes);
                }else{
                    throw new \Exception($bundle.' file is not writable');
                }
            }else{
                throw new \Exception($bundle.' file does not exist');
            }
        }catch (\Exception $ex){
            throw new \Exception($ex->getMessage());
        }
    }

    /**
     * @param $tableName
     * @return string
     * @throws \Exception
     */
    private function getTablePrimaryKey($tableName)
    {
        try {
            $primaryKey = '';
            if ($this->has('doctrine.dbal.default_connection')) {
                $conn = $this->get('doctrine.dbal.default_connection');
                $sm = $conn->getSchemaManager();
                $table = $sm->listTableIndexes($tableName);

                foreach ($table as $index) {
                    if ($index->isPrimary()) {
                        foreach ($index->getColumns() as $colName) {
                            $primaryKey = $colName;
                            break;
                        }
                    }
                    if (!empty($primaryKey))
                        break;
                }
            }
            return $primaryKey;
        }catch (\Exception $ex){
            throw new \Exception('Error on getting table primary key');
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
                                $searchableCols = [];
                                foreach ($columns as $key => $col) {
                                    $col = ($this->has_language) ? str_replace('tclangtblcol_', '', $col) : $col ;
                                    $colList[$this->generateCase($col, 4)] = [
                                        'text' => 'tool_symfony_tpl_' . $col,
                                        'css' => [
                                            'width' => '20%',
                                            'padding-right' => 0
                                        ],
                                        'sortable' => true,
                                    ];
                                    array_push($searchableCols, $col);
                                }

                                $configData['symfony_tpl']['table']['symfony_tpl_table']['columns'] = $colList;
                                $configData['symfony_tpl']['table']['symfony_tpl_table']['searchables'] = $searchableCols;
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
            throw new \Exception('Cannot create table columns: '. $ex->getMessage());
        }
    }

    /**
     * @param $data
     * @param $moduleDir
     * @throws \Exception
     */
    private function processTranslations($data, $moduleDir)
    {
        try {
            $transFolder = $moduleDir . '/Resources/translations';

            if(file_exists($transFolder)){
                if(is_writable($transFolder)){
                    $transData = [];
                    $notEmptyKeyHolder = [];
                    if (!empty($data['step6'])) {
                        /**
                         * Loop through each language
                         */
                        foreach ($data['step6'] as $lang => $transContainer) {
                            $langLocale = explode('_', $lang);
                            $transData[$langLocale[0]] = [];
                            /**
                             * Loop through array that contains
                             * the translations
                             */
                            foreach ($transContainer as $value) {
                                //include step2 translations
                                $value = array_merge($value, $data['step2'][$lang]);
                                /**
                                 * Process the translations
                                 */
                                foreach ($value as $colName => $translations) {
                                    //exclude field that starts in tcf
                                    if (!in_array($colName, ['tcf-lang-local', 'tcf-tbl-type'])) {

                                        if (strpos($colName, 'tcf') !== false)
                                            $colName = str_replace('tcf-', '', $colName);

                                        $key = 'tool_symfony_tpl_' . $colName;
                                        if (strpos($colName, 'tcinputdesc') !== false)
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
                    foreach ($transData as $local => $trans) {
                        foreach ($trans as $key => $text) {
                            if (empty($text)) {
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
                    foreach ($transData as $lang => $translations) {
                        $fileName = $transFolder . '/messages.' . $lang . '.yaml.phtml';
                        $fp = fopen($fileName, 'x+');
                        fwrite($fp, $writer->toString($translations));
                        fclose($fp);
                    }
                }else{
                    throw new \Exception('File is not writable: '.$transFolder);
                }
            }else{
                throw new \Exception('File does not exist: '.$transFolder);
            }
        }catch (\Exception $ex){
            throw new \Exception('Cannot create translations: '. $ex->getMessage());
        }
    }

    /**
     * Generate form builder and Entity
     *
     * @param $data
     * @param $modulePath
     * @return array
     * @throws \Exception
     */
    private function processFormBuilderAndEntity($data, $modulePath)
    {
        try {
            $entity_formBuilder = [
                'builder' => '',
                'entity' => '',
            ];
            if (!empty($data['step5'])) {
                $fieldsInfo = $data['step5'];
                $st_builder = '$builder';
                $st_getterSetter = '';
                $pt_builder = '$builder';
                $pt_getterSetter = '';
                $modName = $this->generateCase($this->module_name, 2);
                $fields = $fieldsInfo['tcf-db-table-col-editable'] ?? [];

                foreach ($fields as $key => $fieldName) {
                    //check if we have a secondary table (language table)
                    if($this->has_language && strpos($fieldName, 'tclangtblcol_') !== false){
                        //process secondary table
                        $fieldName = str_replace('tclangtblcol_', '', $fieldName);
                        $isPrimary = ($this->st_pk == $fieldName) ? : false;
                        $this->constructBuilderAndEntity($st_getterSetter,$st_builder, $fieldsInfo, $fieldName, $key, $modName, $isPrimary);
                    }else{
                        //process primary table
                        $isPrimary = ($this->pt_pk == $fieldName) ? : false;
                        $this->constructBuilderAndEntity($pt_getterSetter,$pt_builder, $fieldsInfo, $fieldName, $key, $modName, $isPrimary);
                    }
                    //don't include primary key
                }

                /**
                 * Process the creation of
                 * Entity, Repository and form builder
                 */

                $entity_filename = $modulePath.'/Entity/SampleEntity.php';
                $form_filename = $modulePath.'/Form/Type/SampleEntityFormType.php';

                //create entity for secondary table
                if($this->has_language){
                    //Entity
                    $entity_content = file_get_contents($entity_filename);
                    $fileName = $modulePath.'/Entity/'.$this->st_entity_name.'.php';
                    $this->createSecondaryTableFiles($entity_content, '//ENTITY_SETTERS_GETTERS', $st_getterSetter, $fileName);
                    //FORM BUILDER
                    $form_content = file_get_contents($form_filename);
                    $fileName = $modulePath.'/Form/Type/'.$this->st_entity_name.'FormType.php';
                    $this->createSecondaryTableFiles($form_content, '//MODULE_FORM_BUILDER', $st_builder, $fileName);
                    //REPOSITORY
                    $repo_content = file_get_contents($modulePath.'/Repository/SampleEntityRepository.php');
                    $fileName = $modulePath.'/Repository/'.$this->st_entity_name.'Repository.php';
                    $this->createSecondaryTableFiles($repo_content, '', '', $fileName);
                }

                //Create primary table entity
                $this->replaceFileTextContent($entity_filename, $entity_filename, '//ENTITY_SETTERS_GETTERS', $pt_getterSetter);
                //Create primary table form builder
                $this->replaceFileTextContent($form_filename, $form_filename, '//MODULE_FORM_BUILDER', $pt_builder);
            }
            return $entity_formBuilder;
        }catch (\Exception $ex){
            throw new \Exception('Cannot create form builder and entity: '. $ex->getMessage());
        }
    }

    /**
     * @param $entity_content
     * @param $find
     * @param $replace
     * @param $fileName
     */
    private function createSecondaryTableFiles($entity_content, $find, $replace, $fileName)
    {
        $content = str_replace($find, $replace, $entity_content);
        $content = str_replace('SampleEntity', $this->st_entity_name, $content);
        $content = str_replace('sample_table_name', $this->secondary_table, $content);
        $content = str_replace('sample_primary_id', $this->st_pk, $content);
        $fp = fopen($fileName, 'x+');
        fwrite($fp, $content);
        fclose($fp);
    }

    /**
     * @param $getterSetter
     * @param $builder
     * @param $fieldsInfo
     * @param $fieldName
     * @param $key
     * @param $modName
     * @param $isPrimary
     */
    private function constructBuilderAndEntity(&$getterSetter, &$builder, $fieldsInfo, $fieldName, $key, $modName, $isPrimary)
    {
        $fieldsRequired = $fieldsInfo['tcf-db-table-col-required'] ?? [];
        $fieldsType = $fieldsInfo['tcf-db-table-col-type'] ?? [];

        $this->constructEntitySettersGetters($getterSetter, $fieldName, $isPrimary);
        //check if field is required
        $isRequired = (in_array($fieldName, $fieldsRequired)) ? true : false;
        //get field type
        $fieldOpt = $this->getFieldTypeAndAttr($fieldsType[$key]);

        $builder .= "
            ->add('" . $fieldName . "', " . $fieldOpt['type'] . "::class, [
                'label' => 'tool_" . $modName . "_" . $fieldName . "',
                'label_attr' => [
                    'label_tooltip' => 'tool_" . $modName . "_" . $fieldName . "_tooltip'
                ]";
        if(!empty($fieldOpt['attr'])){
            $builder .= $fieldOpt['attr'];
        }
        if ($isRequired) {
            $builder .= ",\n\t\t\t\t'constraints' => new NotBlank(),
                'required' => true,
            ])";
        } else {
            $builder .= "])";
        }
    }

    /**
     * @param $getterSetter
     * @param $column
     * @param $isPrimary
     * @return string
     */
    private function constructEntitySettersGetters(&$getterSetter, $column, $isPrimary)
    {
        $funcName = ucfirst($this->generateCase($column, 4));
        //variable header
        $type = "";
        if($isPrimary){
            $getterSetter .= "/**\n\t* @ORM\Id()\n\t* @ORM\GeneratedValue()\n\t* @ORM\Column(type=\"integer\")\n\t*/";
            $type = 'int';
        }else{
            $getterSetter .= "\t/**\n\t* @ORM\Column(type=\"string\", length=255)\n\t*/";
            $type = 'string';
        }
        //variables
        $getterSetter .= "\n\tprivate $".$column.";\n\n";
        //getters
        $getterSetter .= "\tpublic function get".$funcName."(): ?".$type."\n".
                        "\t{\n".
                            "\t\t".'return $this->'.$column.";\n".
                        "\t}\n\n";
        //setters
        if(!$isPrimary) {
            $getterSetter .= "\tpublic function set" . $funcName . "(?string $" . $column . "): self\n" .
                "\t{\n" .
                "\t\t" . '$this->' . $column . " = $" . $column . ";\n" .
                "\t\t" . 'return $this' . ";\n" .
                "\t}\n\n";
        }
        return $getterSetter;
    }

    /**
     * @param $field
     * @return array
     */
    private function getFieldTypeAndAttr($field)
    {
        $opt = [
            'type' => 'TextType',
            'attr' => ''
        ];

        if(!empty($field)){
            if($field == 'MelisText') {
                $opt['type'] = 'TextType';
            }
            elseif($field == 'MelisCoreTinyMCE') {
                $opt['type'] = 'TextareaType';
            }elseif($field == "Datepicker"){
                $opt['type'] = 'TextType';
                $opt['attr'] = ",\n\t\t\t\t'attr' => [
                    'class' => 'form-control input-inline datepicker',
                    'data-provide' => 'datepicker',
                ]";
            }else
                $opt['type'] = 'TextType';
        }else{
            $opt['type'] = 'TextType';
        }

        return $opt;
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
     * @throws \Exception
     */
    private function mapDirectory($dir, $contentUpdate)
    {
        $cdir = scandir($dir);
        foreach ($cdir as $key => $value) {
            if (!in_array($value, array(".", ".."))) {
                if (is_dir($dir . '/' . $value)) {
                    $this->mapDirectory($dir . '/' . $value, $contentUpdate);
                } else {
                    foreach ($contentUpdate as $search => $replace) {
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

    /**
     * @param $dirPath
     */
    private function deleteDir($dirPath) {
        if (! is_dir($dirPath)) {
            throw new InvalidArgumentException("$dirPath must be a directory");
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }
}