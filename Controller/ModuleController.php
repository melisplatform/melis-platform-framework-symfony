<?php

namespace MelisPlatformFrameworkSymfony\Controller;

use http\Exception\InvalidArgumentException;
use MelisPlatformFrameworkSymfony\MelisServiceManager;
use mysql_xdevapi\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Yaml\Yaml;
use Zend\Config\Writer\PhpArray;
use Zend\Session\Container;
use function Composer\Autoload\includeFile;

class ModuleController extends AbstractController
{
    /**
     * @var MelisServiceManager
     */
    private $melisServiceManager;

    private $primary_table = '';
    private $pt_entity_name = '';
    private $pt_pk = '';
    private $secondary_table = '';
    private $st_entity_name = '';
    private $st_pk = '';
    private $st_fk = '';
    private $lang_fk = '';
    private $module_name = '';
    private $has_language = false;
    private $pre_add_trans = [
        'en' => [
            'tool_symfony_tpl_tab_properties' => 'Properties',
            'tool_symfony_tpl_tab_language' => 'Language',
            'tool_symfony_tpl_common_add' => 'Add',
            'tool_symfony_tpl_successfully_saved' => 'Record successfully saved',
            'tool_symfony_tpl_successfully_updated' => 'Record successfully updated',
            'tool_symfony_tpl_unable_to_update' => 'Unable to update record',
            'tool_symfony_tpl_unable_to_save' => 'Unable to save record',
            'tool_symfony_tpl_confirm_modal_yes' => 'Yes',
            'tool_symfony_tpl_confirm_modal_no' => 'No',
            'tool_symfony_tpl_confirm_modal_title' => 'Delete Record',
            'tool_symfony_tpl_confirm_modal_message' => 'Are you sure you want to delete this record?',
        ],
        'fr' => [
            'tool_symfony_tpl_tab_properties' => 'Properties',
            'tool_symfony_tpl_tab_language' => 'Language',
            'tool_symfony_tpl_common_add' => 'Ajouter',
            'tool_symfony_tpl_successfully_saved' => 'Record successfully saved',
            'tool_symfony_tpl_successfully_updated' => 'Record successfully updated',
            'tool_symfony_tpl_unable_to_update' => 'Unable to update record',
            'tool_symfony_tpl_unable_to_save' => 'Unable to save record',
            'tool_symfony_tpl_confirm_modal_yes' => 'Yes',
            'tool_symfony_tpl_confirm_modal_no' => 'No',
            'tool_symfony_tpl_confirm_modal_title' => 'Delete Record',
            'tool_symfony_tpl_confirm_modal_message' => 'Are you sure you want to delete this record ?',
        ]
    ];
    private $fileInputLists = [];
    private $componentsDir = '';
    private $assetsDir = '';
    private $zendModuleDir = '';
    private $savingType = '';

    /**
     * ModuleController constructor.
     * @param $melisServiceManager
     */
    public function __construct($melisServiceManager)
    {
        $this->melisServiceManager = $melisServiceManager;
    }

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
            'tcf-db-table-has-language' => '',
            'tcf-db-table-language-tbl' => '',
            'tcf-db-table-language-pri-fk' => '',
            'tcf-db-table-language-lang-fk' => '',
        ];

        $data['step4'] = [
            'tcf-db-table-cols' => [
                'cal_id', 'cal_event_title', 'cal_date_start',
                'cal_date_end', 'cal_created_by', 'cal_last_update_by',
                'cal_date_last_update', 'cal_date_added'
            ],
            'tcf-db-table-col-display' => [
                'raw_view', 'raw_view', 'raw_view',
                'raw_view', 'admin_name', 'admin_name',
                'raw_view', 'raw_view'
            ]
        ];


        $data['step5'] = [
            'tcf-db-table-col-editable' => [
                'cal_id', 'cal_event_title', 'cal_date_start',
                'cal_date_end', 'cal_created_by', 'cal_last_update_by',
                'cal_date_last_update', 'cal_date_added'
            ],
            'tcf-db-table-col-required' => [
                'cal_id', 'cal_event_title', 'cal_date_start',
                'cal_date_end', 'cal_date_added', 'tclangtblcol_cnews_text_id'
            ],
            'tcf-db-table-col-type' => [
                'MelisText', 'MelisCoreTinyMCE', 'Datepicker', 'Datepicker',
                'MelisCoreUserSelect', 'MelisCoreUserSelect', 'Datepicker', 'Datepicker',
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

    private function multipleData()
    {
        $data = [];

        $data['step1'] = [
            'tcf-name' => 'SampleTool',
            'tcf-tool-type' => 'db',
            'tcf-tool-edit-type' => 'tab',
            'tcf-create-microservice' => '0',
            'tcf-create-framework-tool' => '1',
            'tcf-tool-framework' => 'symfony',
        ];

        $data['step2'] = [
            'en_EN' => [
                'tcf-title' => 'Symfony Tool',
                'tcf-desc' => 'Symfony tool description',
                'tcf-lang-local' => 'en_EN'
            ],
            'fr_FR' => [
                'tcf-title' => '',
                'tcf-desc' => '',
                'tcf-lang-local' => 'fr_FR'
            ],
        ];

        $data['step3'] = [
            'tcf-db-table' => 'melis_cms_news',
            'tcf-db-table-has-language' => 1,
            'tcf-db-table-language-tbl' => 'melis_cms_news_texts',
            'tcf-db-table-language-pri-fk' => 'cnews_id',
            'tcf-db-table-language-lang-fk' => 'cnews_lang_id',
        ];

        $data['step4']['tcf-db-table-cols'] = [
            'cnews_id', 'cnews_status', 'cnews_image1',
            'cnews_image2', 'cnews_image3', 'cnews_documents1',
            'cnews_documents2', 'cnews_documents3', 'cnews_creation_date',
            'cnews_publish_date', 'cnews_unpublish_date', 'cnews_slider_id',
            'cnews_site_id', 'tclangtblcol_cnews_text_id', 'tclangtblcol_cnews_title',
            'tclangtblcol_cnews_subtitle', 'tclangtblcol_cnews_paragraph1', 'tclangtblcol_cnews_paragraph2',
            'tclangtblcol_cnews_paragraph3', 'tclangtblcol_cnews_paragraph4', 'tclangtblcol_cnews_lang_id',
        ];

        $data['step4']['tcf-db-table-col-display'] = [
            'raw_view', 'dot_color', 'char_length_limit',
            'char_length_limit', 'char_length_limit', 'char_length_limit',
            'char_length_limit', 'raw_view', 'raw_view',
            'raw_view', 'raw_view', 'raw_view',
            'site_name', 'raw_view', 'raw_view',
            'raw_view', 'char_length_limit', 'char_length_limit',
            'raw_view', 'raw_view', 'lang_name',
        ];


        $data['step5'] = [
            'tcf-db-table-col-editable' => [
                'cnews_id',
                'cnews_status', 'cnews_image1',
                'cnews_image2', 'cnews_image3', 'cnews_documents1',
                'cnews_documents2', 'cnews_documents3', 'cnews_creation_date',
                'cnews_publish_date', 'cnews_unpublish_date', 'cnews_slider_id',
                'cnews_site_id', 'tclangtblcol_cnews_title',
                'tclangtblcol_cnews_subtitle', 'tclangtblcol_cnews_paragraph1', 'tclangtblcol_cnews_paragraph2',
                'tclangtblcol_cnews_paragraph3', 'tclangtblcol_cnews_paragraph4', 'tclangtblcol_cnews_id',
            ],
            'tcf-db-table-col-required' => [
                'cnews_id','cnews_status', 'cnews_image1', 'cnews_image2', 'cnews_creation_date', 'cnews_title'
            ],
            'tcf-db-table-col-type' => [
                'MelisText',
                'Switch', 'File', 'File',
                'File', 'File', 'File', 'File',
                'Datetimepicker', 'Datetimepicker', 'Datetimepicker', 'MelisText',
                'MelisCmsPluginSiteSelect', 'MelisText', 'TextArea',
                'MelisCoreTinyMCE', 'MelisCoreTinyMCE', 'MelisCoreTinyMCE', 'MelisCoreTinyMCE', 'MelisText'
            ]
        ];

        $data['step6'] = [
            'en_EN' => [
                'pri_tbl' => [
                    'cnews_id' => 'ID',
                    'cnews_id_tcinputdesc' => '',
                    'cnews_status' => 'Status',
                    'cnews_status_tcinputdesc' => '',
                    'cnews_image1' => 'Image 1',
                    'cnews_image1_tcinputdesc' => '',
                    'cnews_image2' => 'Image 2',
                    'cnews_image2_tcinputdesc' => '',
                    'cnews_image3' => 'Image 3',
                    'cnews_image3_tcinputdesc' => '',
                    'cnews_documents1' => 'Docs 1',
                    'cnews_documents1_tcinputdesc' => '',
                    'cnews_documents2' => 'Docs 2',
                    'cnews_documents2_tcinputdesc' => '',
                    'cnews_documents3' => 'Docs 3',
                    'cnews_documents3_tcinputdesc' => '',
                    'cnews_creation_date' => 'Date created',
                    'cnews_creation_date_tcinputdesc' => '',
                    'cnews_publish_date' => 'Date published',
                    'cnews_publish_date_tcinputdesc' => '',
                    'cnews_unpublish_date' => 'Date unpublished',
                    'cnews_unpublish_date_tcinputdesc' => '',
                    'cnews_slider_id' => 'Slider ID',
                    'cnews_slider_id_tcinputdesc' => '',
                    'cnews_site_id' => 'Site ID',
                    'cnews_site_id_tcinputdesc' => '',
                    'tcf-lang-local' => 'en_EN',
                    'tcf-tbl-type' => 'pri_tbl',
                ],
                'lang_tbl' => [
                    'tclangtblcol_cnews_text_id' => 'ID',
                    'tclangtblcol_cnews_text_id_tcinputdesc' => '',
                    'tclangtblcol_cnews_title' => 'Title',
                    'tclangtblcol_cnews_title_tcinputdesc' => '',
                    'tclangtblcol_cnews_subtitle' => 'Sub Title',
                    'tclangtblcol_cnews_subtitle_tcinputdesc' => '',
                    'tclangtblcol_cnews_paragraph1' => 'P1',
                    'tclangtblcol_cnews_paragraph1_tcinputdesc' => '',
                    'tclangtblcol_cnews_paragraph2' => 'P2',
                    'tclangtblcol_cnews_paragraph2_tcinputdesc' => '',
                    'tclangtblcol_cnews_paragraph3' => 'P3',
                    'tclangtblcol_cnews_paragraph3_tcinputdesc' => '',
                    'tclangtblcol_cnews_paragraph4' => 'P4',
                    'tclangtblcol_cnews_paragraph4_tcinputdesc' => '',
                    'tclangtblcol_cnews_lang_id' => 'Language ID',
                    'tclangtblcol_cnews_lang_id_tcinputdesc' => '',
                    'tcf-lang-local' => 'en_EN',
                    'tcf-tbl-type' => 'lang_tbl',
                ]
            ],
            'fr_FR' => [
                'pri_tbl' => [
                    'cnews_id' => '',
                    'cnews_id_tcinputdesc' => '',
                    'cnews_status' => '',
                    'cnews_status_tcinputdesc' => '',
                    'cnews_image1' => '',
                    'cnews_image1_tcinputdesc' => '',
                    'cnews_image2' => '',
                    'cnews_image2_tcinputdesc' => '',
                    'cnews_image3' => '',
                    'cnews_image3_tcinputdesc' => '',
                    'cnews_documents1' => '',
                    'cnews_documents1_tcinputdesc' => '',
                    'cnews_documents2' => '',
                    'cnews_documents2_tcinputdesc' => '',
                    'cnews_documents3' => '',
                    'cnews_documents3_tcinputdesc' => '',
                    'cnews_creation_date' => '',
                    'cnews_creation_date_tcinputdesc' => '',
                    'cnews_publish_date' => '',
                    'cnews_publish_date_tcinputdesc' => '',
                    'cnews_unpublish_date' => '',
                    'cnews_unpublish_date_tcinputdesc' => '',
                    'cnews_slider_id' => '',
                    'cnews_slider_id_tcinputdesc' => '',
                    'cnews_site_id' => '',
                    'cnews_site_id_tcinputdesc' => '',
                    'tcf-lang-local' => 'fr_FR',
                    'tcf-tbl-type' => 'lang_tbl',
                ],
                'lang_tbl' => [
                    'tclangtblcol_cnews_text_id' => '',
                    'tclangtblcol_cnews_text_id_tcinputdesc' => '',
                    'tclangtblcol_cnews_title' => '',
                    'tclangtblcol_cnews_title_tcinputdesc' => '',
                    'tclangtblcol_cnews_subtitle' => '',
                    'tclangtblcol_cnews_subtitle_tcinputdesc' => '',
                    'tclangtblcol_cnews_paragraph1' => '',
                    'tclangtblcol_cnews_paragraph1_tcinputdesc' => '',
                    'tclangtblcol_cnews_paragraph2' => '',
                    'tclangtblcol_cnews_paragraph2_tcinputdesc' => '',
                    'tclangtblcol_cnews_paragraph3' => '',
                    'tclangtblcol_cnews_paragraph3_tcinputdesc' => '',
                    'tclangtblcol_cnews_paragraph4' => '',
                    'tclangtblcol_cnews_paragraph4_tcinputdesc' => '',
                    'tclangtblcol_cnews_lang_id' => '',
                    'tclangtblcol_cnews_lang_id_tcinputdesc' => '',
                    'tcf-lang-local' => 'fr_FR',
                    'tcf-tbl-type' => 'lang_tbl',
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
            $this->st_fk = $data['step3']['tcf-db-table-language-pri-fk'] ?? '';
            $this->lang_fk = $data['step3']['tcf-db-table-language-lang-fk'] ?? '';
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

    /**
     * Function to create
     * Symfony module
     *
     * @return JsonResponse
     */
    public function createSymfonyModule()
    {
        $result = [
            'success' => true,
            'message' => '',
        ];

        $container = new Container('melistoolcreator');
//        $data = $container['melis-toolcreator'];
        $data = $this->multipleData();

        if(!empty($data['step1']['tcf-name'])){
            //get module name
            $this->module_name = $this->generateCase($data['step1']['tcf-name'], 3);
            //get saving type
            $this->savingType = $data['step1']['tcf-tool-edit-type'] ?? 'modal';
            //Get Primary table name
            //use table as our entity name
            $this->setTableNameAndEntityName($data);

            $frameworkDir = $_SERVER['DOCUMENT_ROOT'] . '/../thirdparty/Symfony';
            $destination = $frameworkDir . '/src/Bundle/' . $this->module_name;
            $source = dirname(__FILE__) . '/../install/moduleTemplate/SymfonyTpl';
            $this->componentsDir = dirname(__FILE__) . '/../install/moduleTemplate/Components';
            $this->assetsDir = dirname(__FILE__) . '/../install/moduleTemplate/Assets';
            $this->zendModuleDir = $_SERVER['DOCUMENT_ROOT'] . '/../module/'.$this->module_name;

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
                                     * Process config
                                     */
                                    $this->processConfigs($data, $destination);
                                    /**
                                     * Process Form Builder and Entity
                                     */
                                    $this->processFormBuilderAndEntity($data, $destination);
                                    /**
                                     * Process module translations
                                     */
                                    $this->processTranslations($data, $destination);
                                    /**
                                     * Update the controller for some codes to insert
                                     */
                                    $this->updateController($destination);
                                    /**
                                     * Process views
                                     */
                                    $this->processViews($destination);
                                    /**
                                     * Process the replacement of file
                                     * and contents
                                     */
                                    $textToChange = [
                                        'SymfonyTpl' => ucfirst($this->module_name),
                                        'symfonyTpl' => lcfirst($this->module_name),
                                        'symfonytpl' => strtolower($this->module_name),
                                        'SYMFONYTPL' => strtoupper($this->module_name),
                                        'symfony_tpl' => $this->generateCase($this->module_name, 2),
                                        'SampleEntity' => ucfirst($this->pt_entity_name),
                                        'sampleEntity' => strtolower($this->pt_entity_name),
                                        'sample_table_name' => $this->primary_table,
                                        'sample_primary_id' => $this->pt_pk,
                                        'SamplePrimaryId' => ucfirst($this->generateCase($this->pt_pk, 4)),
                                        'samplePrimaryId' => $this->generateCase($this->pt_pk, 4),
                                        'savingType' => strtolower($this->savingType),
                                        'SavingType' => ucfirst($this->savingType),
                                    ];
                                    //if has second table(language)
                                    if($this->has_language){
                                        //include other text needed to change
                                        $textToChange = array_merge($textToChange, [
                                            'secondary_table_pk' => $this->st_pk,
                                            'secondary_tbl_pt_id' => $this->st_fk,
                                            'SecondaryTblPtId' => ucfirst($this->generateCase($this->st_fk, 4)),
                                            'LanguageFkId' => ucfirst($this->generateCase($this->lang_fk, 4)),
                                            'languageFkId' => $this->generateCase($this->lang_fk, 4),
                                            'secondaryTableName' => $this->generateCase($this->secondary_table, 4),
                                            'SampleLanguageEntity' => ucfirst($this->st_entity_name),
                                        ]);
                                    }
                                    $this->mapDirectory($destination, $textToChange);
                                    /**
                                     * Let's put the assets inside the Zend Module that we created
                                     */
                                    $this->processAssets();
                                    /**
                                     * After we successfully created the bundle
                                     * and adding the assets,
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
                    $regex = '/(];(?![\s\S]*];[\s\S]*$))/im';
                    $newBundles = preg_replace($regex, "$bundleKey$1", file_get_contents($bundle));
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
     * @param $tableName
     * @return array
     * @throws \Exception
     */
    private function getTableColumnType($tableName)
    {
        try {
            $columnType = [];
            if ($this->has('doctrine.dbal.default_connection')) {
                $conn = $this->get('doctrine.dbal.default_connection');
                $sm = $conn->getSchemaManager();
                $columns = $sm->listTableColumns($tableName);

                foreach ($columns as $column) {
                    $columnType[$column->getName()] = $column->getType();
                }
            }
            return $columnType;
        }catch (\Exception $ex){
            throw new \Exception('Error on getting column type');
        }
    }

    /**
     * Create module config
     *
     * @param $data
     * @param $dir
     * @throws \Exception
     */
    private function processConfigs($data, $dir)
    {
        try {
            $configFile = $dir . '/Resources/config/config.yaml.phtml';
            if(file_exists($configFile)){
                if(is_writable($configFile)){
                    $configData = include($configFile);
                    if (!empty($configData)) {
                        if (!empty($data['step4'])) {
                            $colList = [];
                            $columns = $data['step4']['tcf-db-table-cols'];
                            $columnsDisplay = $data['step4']['tcf-db-table-col-display'];
                            $searchableCols = [];
                            $colsDisplay = [];
                            /**
                             * Process Table columns
                             */
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
                                $colsDisplay[$this->generateCase($col, 4)] = $columnsDisplay[$key];
                            }

                            $configData['symfony_tpl']['table']['symfony_tpl_table']['columns'] = $colList;
                            $configData['symfony_tpl']['table']['symfony_tpl_table']['searchables'] = $searchableCols;
                            $configData['symfony_tpl']['table']['symfony_tpl_table']['columnDisplay'] = $colsDisplay;

                            /**
                             * Add properties tab on modal
                             */
                            $type = $this->savingType;
                            $configSavingType = [$type =>
                                [
                                    'symfony_tpl_'.$type => [
                                        'id' => 'symfonyTpl'.ucfirst($type),
                                        'btnSubmitText' => 'Save',
                                        'btnSubmitId' => 'btn-save-symfonytpl',
                                        'tabs' => []
                                    ]
                                ]
                            ];
                            $propertiesTab = $this->getModalTabs('tab_properties', 'tool_symfony_tpl_tab_properties',
                                'glyphicons tag','symfonytpl_prop_form', $this->pt_entity_name, 'form.html.twig');
                        $configSavingType[$type]['symfony_tpl_'.$type]['tabs'] = array_merge($configSavingType[$type]['symfony_tpl_'.$type]['tabs'], $propertiesTab);
                            /**
                             * Add language tab on modal
                             */
                            if ($this->has_language) {
                                $modalLangTab = $this->getModalTabs('tab_language', 'tool_symfony_tpl_tab_language',
                                    'glyphicons language','symfonytpl_lang_form', $this->st_entity_name, 'form_language.html.twig');
                                $configSavingType[$type]['symfony_tpl_'.$type]['tabs'] = array_merge($configSavingType[$type]['symfony_tpl_'.$type]['tabs'], $modalLangTab);
                            } else {
                                unlink($dir . '/Resources/views/form_language.html.twig');
                            }

                            $configData['symfony_tpl'] = array_merge($configData['symfony_tpl'], $configSavingType);
                            $writer = new PhpArray();
                            file_put_contents($configFile, $writer->toString($configData));
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
     * @param $name
     * @param $title
     * @param $class
     * @param $formId
     * @param $entity
     * @param $form
     * @return array
     */
    private function getModalTabs($name, $title, $class, $formId, $entity, $form)
    {
        return [
            $name =>
            [
                'title' => $title,
                'content' => '',
                'class' => $class,
                'form' => [
                    'form_id' => $formId,
                    'entity_class_name' => "App\Bundle\SymfonyTpl\Entity\\$entity",
                    'form_type_class_name' => "App\Bundle\SymfonyTpl\Form\Type\\".$entity."FormType",
                    'form_view_file' => '@SymfonyTpl/'.$form,
                ]
            ]
        ];
    }

    /**
     * Function to process assets
     */
    public function processAssets()
    {
        $publicDir = $this->zendModuleDir.'/public/';
        if(is_dir($publicDir) && file_exists($publicDir)){
            $jsDir = $publicDir.'js';
            $cssDir = $publicDir.'css';
            if(!file_exists($jsDir))
                mkdir($jsDir, 0777);
            if(!file_exists($cssDir))
                mkdir($cssDir, 0777);

            //get the js file
            $jsFileTemplate = $this->assetsDir.'/js';
            if($this->savingType == 'tab')
                $jsFileTemplate .= '/tab.js';
            else
                $jsFileTemplate .= '/modal.js';

            if(file_exists($jsFileTemplate)){
                $jsContent = file_get_contents($jsFileTemplate);
                $find = ['symfonytpl', 'symfonyTpl', 'symfony_tpl'];
                $replace = [strtolower($this->module_name), lcfirst($this->module_name), $this->generateCase($this->module_name, 2)];
                $this->createFilesAndReplaceTexts($jsContent, $find, $replace,$jsDir.'/tool.js');
            }

            //get css file
            $cssFileTemplate = $this->assetsDir.'/css/tool.css';
            if(file_exists($cssFileTemplate)){
                $cssContent = file_get_contents($cssFileTemplate);
                $this->createFilesAndReplaceTexts($cssContent, '', '',$cssDir.'/tool.css');
            }

        }
    }

    /**
     * Create module translations
     *
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

                                        if (strpos($colName, 'tclangtblcol_') !== false)
                                            $colName = str_replace('tclangtblcol_', '', $colName);

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
                                $value = str_replace('tooltip', '', $value);
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

                        //include other translations
                        if(isset($this->pre_add_trans[$lang])){
                            $translations = array_merge($translations, $this->pre_add_trans[$lang]);
                        }else{
                            /**
                             * If $lang(es for Spanish) is not in the $this->pre_add_trans,
                             * then we use translations of en language in es language
                             * so that the translations still exist in every language
                             */
                            $translations = array_merge($translations, $this->pre_add_trans['en']);
                        }

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
                $stBuilderViewTransformer = '';
                $ptBuilderViewTransformer = '';
                $stFileValidations = '';
                $ptFileValidations = '';

                $modName = $this->generateCase($this->module_name, 2);
                $fields = $fieldsInfo['tcf-db-table-col-editable'] ?? [];

                /**
                 * Get Table Column Type
                 */
                $secTableColType = $this->getTableColumnType($this->secondary_table);
                $firstTableColType = $this->getTableColumnType($this->primary_table);

                $ptOtherFields = $firstTableColType;
                $stOtherFields = $secTableColType;

                foreach ($fields as $key => $fieldName) {
                    //check if we have a secondary table (language table)
                    if($this->has_language && strpos($fieldName, 'tclangtblcol_') !== false){
                        //process secondary table
                        $fieldName = str_replace('tclangtblcol_', '', $fieldName);

                        if(array_key_exists($fieldName, $stOtherFields)){
                            unset($stOtherFields[$fieldName]);
                        }

                        $isPrimary = ($this->st_pk == $fieldName) ? : false;
                        $this->constructBuilderAndEntity($st_getterSetter,$st_builder,$stFileValidations,
                            $stBuilderViewTransformer, $this->st_pk, $fieldsInfo, $fieldName, $key,
                            $modName, $isPrimary, false, $secTableColType);
                    }else{
                        if(array_key_exists($fieldName, $ptOtherFields)){
                            unset($ptOtherFields[$fieldName]);
                        }
                        //process primary table
                        $isPrimary = ($this->pt_pk == $fieldName) ? : false;
                        $this->constructBuilderAndEntity($pt_getterSetter,$pt_builder, $ptFileValidations,
                            $ptBuilderViewTransformer, $this->pt_pk, $fieldsInfo, $fieldName, $key,
                            $modName, $isPrimary, true, $firstTableColType);
                    }
                }

                /**
                 * Include other columns in the entity that is not
                 * selected in the tool creator
                 */
                //primary table
                foreach($ptOtherFields as $columnName => $columnType){
                    $isPrimary = ($this->pt_pk == $columnName) ? : false;
                    $pt_getterSetter = $this->constructEntitySettersGetters($pt_getterSetter, $columnName,
                        $isPrimary, null, 'string', null, true,
                        $ptOtherFields);
                }
                //secondary table
                foreach($stOtherFields as $columnName => $columnType){
                    $isPrimary = ($this->st_pk == $columnName) ? : false;
                    $st_getterSetter = $this->constructEntitySettersGetters($st_getterSetter, $columnName,
                        $isPrimary, null, 'string', null, false,
                        $stOtherFields);
                }

                /**
                 * Process the creation of
                 * Entity, Repository and form builder
                 */

                $entity_filename = $modulePath.'/Entity/SampleEntity.php';
                $form_filename = $modulePath.'/Form/Type/SampleEntityFormType.php';

                /**
                 * Process Files for
                 * secondary table
                 */
                if($this->has_language){
                    //Entity
                    $entity_content = file_get_contents($entity_filename);
                    $fileName = $modulePath.'/Entity/'.$this->st_entity_name.'.php';
                    $this->createFilesAndReplaceTexts($entity_content,'//ENTITY_SETTERS_GETTERS', $st_getterSetter, $fileName);
                    $this->replaceFileTextContent($fileName, $fileName, '//FILE_FIELDS_VALIDATION', $stFileValidations);
                    //FORM BUILDER
                    //include builder view transformer
                    $st_builder = $st_builder.$stBuilderViewTransformer;
                    $form_content = file_get_contents($form_filename);
                    $fileName = $modulePath.'/Form/Type/'.$this->st_entity_name.'FormType.php';
                    $this->createFilesAndReplaceTexts($form_content, '//MODULE_FORM_BUILDER', $st_builder, $fileName);
                    //REPOSITORY
                    $repo_content = file_get_contents($modulePath.'/Repository/SampleEntityRepository.php');
                    $fileName = $modulePath.'/Repository/'.$this->st_entity_name.'Repository.php';
                    $this->createFilesAndReplaceTexts($repo_content, '', '', $fileName);

                    /**
                     * Add connection to first table and secondary table
                     */
                    //Add connection to the first table entity with the second table entity
                    $assoc = "\t/**\n\t".'* @ORM\OneToMany(targetEntity="'.$this->st_entity_name.'",mappedBy="'.$this->st_fk.'")'."\n\t*/";
                    $pt_getterSetter = $this->constructEntitySettersGetters($pt_getterSetter, $this->secondary_table, false, '', '\Doctrine\ORM\PersistentCollection', $assoc);
                }
                //Create primary table entity
                $this->replaceFileTextContent($entity_filename, $entity_filename, '//ENTITY_SETTERS_GETTERS', $pt_getterSetter);
                $this->replaceFileTextContent($entity_filename, $entity_filename, '//FILE_FIELDS_VALIDATION', $ptFileValidations);
                //Create primary table form builder
                //include builder view transformer
                $pt_builder = $pt_builder.$ptBuilderViewTransformer;
                $this->replaceFileTextContent($form_filename, $form_filename, '//MODULE_FORM_BUILDER', $pt_builder);
            }
            return $entity_formBuilder;
        }catch (\Exception $ex){
            throw new \Exception('Cannot create form builder and entity: '. $ex->getMessage());
        }
    }

    /**
     * Update some text inside controller
     * @param $modulePath
     */
    public function updateController($modulePath)
    {
        $controller_filename = $modulePath.'/Controller/SampleEntityController.php';
        /**
         * Process Files for
         * secondary table
         */
        if($this->has_language){
            /**
             * Update some of the data that's
             * need to update inside Controller
             */
            //use second table entity name
            $str = "use App\Bundle\SymfonyTpl\Entity\SampleLanguageEntity;\nuse App\Bundle\SymfonyTpl\Form\Type\SampleLanguageEntityFormType;";
            $this->replaceFileTextContent($controller_filename, $controller_filename, '//ADDITIONAL_USE', $str);
            //update listing of data in table to include the secondary table info
            $this->replaceFileTextContent($controller_filename, $controller_filename, '//SECOND_TABLE_DATA', @file_get_contents($this->componentsDir.'/language-data-list.phtml'));
            //update modal content to include language tab and its forms
            $this->replaceFileTextContent($controller_filename, $controller_filename, '//LANGUAGE_FORM_BUILDER', @file_get_contents($this->componentsDir.'/language-form-builder.phtml'));
            $this->replaceFileTextContent($controller_filename, $controller_filename, '//SAVE_FUNCTIONS', @file_get_contents($this->componentsDir.'/save-with-language.phtml'));
        }else{
            $this->replaceFileTextContent($controller_filename, $controller_filename, '//SECOND_TABLE_DATA', '');
            $this->replaceFileTextContent($controller_filename, $controller_filename, '//LANGUAGE_FORM_BUILDER', '');
            $this->replaceFileTextContent($controller_filename, $controller_filename, '//SAVE_FUNCTIONS', @file_get_contents($this->componentsDir.'/save-data.phtml'));
            $this->replaceFileTextContent($controller_filename, $controller_filename, '//ADDITIONAL_USE', '');
        }

        if($this->savingType == 'tab'){
            $this->replaceFileTextContent($controller_filename, $controller_filename, '//SAVING_TYPE', '');
            $this->replaceFileTextContent($controller_filename, $controller_filename, '//FORM_RETURN_DATA', 'return $this->render("@SymfonyTpl/tab.html.twig", ["data" => $data, "tabConfig" => $this->getTabConfig(), "id" => $id]);');
        }else{
            $this->replaceFileTextContent($controller_filename, $controller_filename, '//SAVING_TYPE', '"modalConfig" => $this->getModalConfig()');
            $this->replaceFileTextContent($controller_filename, $controller_filename, '//FORM_RETURN_DATA', 'return new JsonResponse($data);');
        }
    }

    /**
     * @param $modulePath
     */
    public function processViews($modulePath)
    {
        $viewPath = $modulePath.'/Resources/views';
        if($this->savingType == 'tab'){
            $this->replaceFileTextContent($viewPath.'/lists.html.twig',$viewPath.'/lists.html.twig', '{#MAKE_MODAL#}','');
        }else{
            $this->replaceFileTextContent($viewPath.'/lists.html.twig',$viewPath.'/lists.html.twig', '{#MAKE_MODAL#}','{{ create_modal(modalConfig)|raw }}');
            unlink($viewPath.'/tab.html.twig');
        }
    }

    /**
     * @param $content
     * @param $find
     * @param $replace
     * @param $fileName
     */
    private function createFilesAndReplaceTexts($content, $find, $replace, $fileName)
    {
        $content = str_replace($find, $replace, $content);
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
     * @param $fileValidations
     * @param $builderViewTransformer
     * @param $primaryKeyName
     * @param $fieldsInfo
     * @param $fieldName
     * @param $key
     * @param $modName
     * @param $isPrimaryKey
     * @param $isPrimaryTable
     * @param $columnType
     */
    private function constructBuilderAndEntity(&$getterSetter, &$builder, &$fileValidations, &$builderViewTransformer,
                                               $primaryKeyName, $fieldsInfo, $fieldName, $key, $modName, $isPrimaryKey,
                                               $isPrimaryTable = true, $columnType = [])
    {
        $fieldsRequired = $fieldsInfo['tcf-db-table-col-required'] ?? [];
        $fieldsType = $fieldsInfo['tcf-db-table-col-type'] ?? [];

        $this->constructEntitySettersGetters($getterSetter, $fieldName, $isPrimaryKey, $fieldsType[$key], 'string', null, $isPrimaryTable, $columnType);
        //check if field is required
        $isRequired = (in_array($fieldName, $fieldsRequired)) ? true : false;
        //get field type option
        $fieldOpt = $this->getFieldTypeAndAttr($fieldsType[$key], $fieldName, $isPrimaryTable);

        $builder .= "
            ->add('" . $fieldName . "', " . $fieldOpt['type'] . "::class, [
                'label' => 'tool_" . $modName . "_" . $fieldName . "',
                'label_attr' => [
                    'label_tooltip' => 'tool_" . $modName . "_" . $fieldName . "_tooltip'
                ]";
        if(!empty($fieldOpt['attr'])){
            $builder .= $fieldOpt['attr'];
        }
        //Dont the make the switch field as required since it will pass 0 and 1
        if ($isRequired && $fieldsType[$key] != 'Switch') {
            //For the file, we put our validation inside the entity(using a callback to validated the file)
            if($fieldsType[$key] == 'File'){
                if(empty($fileValidations)){
                    $fileValidations .= 'if (null !== $this->get'.ucfirst($this->generateCase($primaryKeyName, 4)).'())
            {
                return;
            }';
                }

                $fileValidations .= "\n\t\t".'if (null === $this->get'.ucfirst($this->generateCase($fieldName, 4)).'()) {
                $notBlank = new NotBlank();
                $context->buildViolation($notBlank->message)->atPath(\''.$fieldName.'\')->addViolation();
            }
                ';

                $builder .= "\n\t\t\t])";
            }else{
                $builder .= ",\n\t\t\t\t'constraints' => new NotBlank(),
                'required' => true,
            ])";
            }
        } else {
            $builder .= ",\n\t\t\t\t'required' => false,
            ])";
        }

        //construct view transformer for Switch
        if($fieldsType[$key] == 'Switch') {
            $builderViewTransformer .= ";\n\n\t\t\t".'$builder->get("'.$fieldName.'")->addViewTransformer(new CallbackTransformer(
                function ($normalizedFormat) {
                    return $normalizedFormat;
                },
                function ($submittedFormat) {
                    return ( $submittedFormat === "0") ? null : (string) $submittedFormat;
                }
            ))';
        }
    }

    /**
     * @param $getterSetter
     * @param $column
     * @param $isPrimaryKey
     * @param $fieldType
     * @param $type
     * @param $assoc
     * @param $isPrimaryTable
     * @param $columnType
     * @param $addSetters
     * @return string
     */
    private function constructEntitySettersGetters(&$getterSetter, $column, $isPrimaryKey, $fieldType = null, $type = "string", $assoc = null, $isPrimaryTable = true, $columnType = [], $addSetters = true)
    {
        $fieldSelectType = ['MelisCoreUserSelect', 'MelisCmsLanguageSelect', 'MelisCmsPluginSiteSelect', 'MelisCmsTemplateSelect'];
        $funcName = ucfirst($this->generateCase($column, 4));
        //variable header
        if(in_array($fieldType, $fieldSelectType)){
            if($fieldType == "MelisCoreUserSelect") {
                $entity = "MelisPlatformFrameworkSymfony\Entity\MelisUser";
                $refCOl = "usr_id";
            }elseif($fieldType == "MelisCmsLanguageSelect"){
                $entity = "MelisPlatformFrameworkSymfony\Entity\MelisCmsLanguage";
                $refCOl = "lang_cms_id";
            }elseif($fieldType == "MelisCmsPluginSiteSelect"){
                $entity = "MelisPlatformFrameworkSymfony\Entity\MelisCmsSite";
                $refCOl = "site_id";
            }else{
                $entity = "MelisPlatformFrameworkSymfony\Entity\MelisCmsTemplate";
                $refCOl = "tpl_id";
            }
            $type = "\\$entity";
            $getterSetter .= "\t/**\n\t".'* @ORM\OneToOne(targetEntity="'.$entity.'")'."\n\t".
                             '* @ORM\JoinColumn(name="'.$column.'", referencedColumnName="'.$refCOl.'")'."\n\t*/";
        }else{
            if($isPrimaryKey){
                $getterSetter .= "/**\n\t* @ORM\Id()\n\t* @ORM\GeneratedValue()\n\t* @ORM\Column(type=\"integer\")\n\t*/";
                $type = 'int';
            }else{
                if(!$isPrimaryTable && $column == $this->st_fk){
                    /**
                     * This is to add association to the second table
                     * foreign key to connect with the first table
                     */
                    $getterSetter .= "\t/**\n\t".'* @ORM\ManyToOne(targetEntity="'.$this->pt_entity_name.'", inversedBy="'.$this->secondary_table.'")'."\n\t".
                        '* @ORM\JoinColumn(name="'.$this->pt_pk.'", referencedColumnName="'.$this->st_fk.'")'."\n\t*/";
                    $type = $this->pt_entity_name;
                }else{
                    if(!empty($assoc)){
                        $getterSetter .= $assoc;
                    }else {
                        //Apply column type
                        if(!empty($columnType)){
                            if(array_key_exists($column, $columnType)){
                                if($columnType[$column] == 'Integer') {
                                    $type = 'int';
                                    $colType = 'integer';
                                }elseif($columnType[$column] == 'Boolean') {
                                    $type = 'bool';
                                    $colType = 'boolean';
                                }elseif($columnType[$column] == 'Text') {
                                    $type = 'string';
                                    $colType = 'string';
                                }else {
                                    $type = 'string';
                                    $colType = 'string';
                                }
                                $getterSetter .= "\t/**\n\t* @ORM\Column(type=\"".$colType."\")\n\t*/";
                            }
                        }else {
                            $getterSetter .= "\t/**\n\t* @ORM\Column(type=\"string\")\n\t*/";
                        }
                    }
                }
            }
        }

        //variables
        $getterSetter .= "\n\tprivate $".$column.";\n\n";
        //getters
        $getterSetter .= "\tpublic function get".$funcName."(): ?".$type."\n".
                        "\t{\n".
                            "\t\t".'return $this->'.$column.";\n".
                        "\t}\n\n";
        //setters
        if($addSetters) {
            $getterSetter .= "\tpublic function set" . $funcName . "(?" . $type . " $" . $column . "): self\n" .
                "\t{\n" .
                "\t\t" . '$this->' . $column . " = $" . $column . ";\n" .
                "\t\t" . 'return $this' . ";\n" .
                "\t}\n\n";
        }

        return $getterSetter;
    }

    /**
     * @param $field
     * @param $fieldName
     * @param $isPrimaryTable
     * @return array
     */
    private function getFieldTypeAndAttr($field, $fieldName, $isPrimaryTable)
    {
        //default entity select type of melis platform
        $fieldSelectType = ['MelisCoreUserSelect', 'MelisCmsLanguageSelect', 'MelisCmsPluginSiteSelect', 'MelisCmsTemplateSelect'];

        $opt = [
            'type' => 'TextType',
            'attr' => ''
        ];

        if(!empty($field)){
            if(in_array($field, $fieldSelectType)){
                if($field == 'MelisCoreUserSelect'){
                    $entityName = 'MelisUser';
                    $choiceLabel = 'usr_name';
                }elseif($field == 'MelisCmsLanguageSelect'){
                    $entityName = 'MelisCmsLanguage';
                    $choiceLabel = 'lang_cms_name';
                }elseif($field == 'MelisCmsPluginSiteSelect'){
                    $entityName = 'MelisCmsSite';
                    $choiceLabel = 'site_name';
                }else{
                    $entityName = 'MelisCmsTemplate';
                    $choiceLabel = 'tpl_name';
                }
                $opt['type'] = '\MelisPlatformFrameworkSymfony\Form\Type\MelisEntitySelectType';
                $opt['attr'] = ",\n\t\t\t\t'class' => \MelisPlatformFrameworkSymfony\Entity\\".$entityName."::class".
                    ",\n\t\t\t\t'choice_label' => '".$choiceLabel."'".
                    ",\n\t\t\t\t'placeholder' => 'tool_symfony_tpl_common_select_choose'";
                //add translation
                $this->pre_add_trans['en']['tool_symfony_tpl_common_select_choose'] = 'Choose';
                $this->pre_add_trans['fr']['tool_symfony_tpl_common_select_choose'] = 'Choisissez';
            }elseif($field == 'MelisText') {
                if(!$isPrimaryTable){
                    /**
                     * If second table foreign key is equal to
                     * the field name, then we make the field
                     * a entity type
                     */
                    if($this->st_fk == $fieldName){
                        $opt['type'] = '\MelisPlatformFrameworkSymfony\Form\Type\MelisEntitySelectType';
                        $opt['attr'] = ",\n\t\t\t\t'class' => \App\Bundle\SymfonyTpl\Entity\\".$this->pt_entity_name."::class".
                            ",\n\t\t\t\t'choice_label' => '".$this->pt_pk."'".
                            ",\n\t\t\t\t'placeholder' => 'tool_symfony_tpl_common_select_choose'";

                        $this->pre_add_trans['en']['tool_symfony_tpl_common_select_choose'] = 'Choose';
                        $this->pre_add_trans['fr']['tool_symfony_tpl_common_select_choose'] = 'Choisissez';
                    }else{
                        $opt['type'] = 'TextType';
                        //make primary key of table readonly
                        if($this->st_pk == $fieldName){
                            $opt['attr'] = ",\n\t\t\t\t'attr' => [
                                'readonly' => 'true',
                            ]";
                        }
                    }
                }else{
                    $opt['type'] = 'TextType';
                    //make primary key of table readonly
                    if($this->pt_pk == $fieldName){
                        $opt['attr'] = ",\n\t\t\t\t'attr' => [
                            'readonly' => 'true',
                        ]";
                    }
                }
            }elseif($field == 'MelisCoreTinyMCE') {
                $opt['type'] = '\MelisPlatformFrameworkSymfony\Form\Type\MelisTinyMceType';
            }elseif($field == "Datepicker" || $field == "Datetimepicker"){
                $opt['type'] = '\MelisPlatformFrameworkSymfony\Form\Type\MelisDateType';
                $format = ($field == 'Datepicker') ? 'YYYY-MM-DD' : 'YYYY-MM-DD HH:mm:ss';
                $opt['attr'] = ",\n\t\t\t\t'attr' => [
                    'date_format' => '".$format."',
                ]";
            }elseif($field == 'Switch'){
                $opt['type'] = '\MelisPlatformFrameworkSymfony\Form\Type\MelisSwitchType';
                $labelOn = 'tool_symfony_tpl_'.$fieldName.'_switch_on_label';
                $labelOff= 'tool_symfony_tpl_'.$fieldName.'_switch_off_label';
                $opt['attr'] = ",\n\t\t\t\t'attr' => [
                    'data-on-label' => '".$labelOn."',
                    'data-off-label' => '".$labelOff."',
                    'data-label-icon' => 'glyphicon glyphicon-resize-horizontal',
                ]";
                //add translation
                $this->pre_add_trans['en'][$labelOn] = 'On';
                $this->pre_add_trans['fr'][$labelOn] = 'On';
                $this->pre_add_trans['en'][$labelOff] = 'Off';
                $this->pre_add_trans['fr'][$labelOff] = 'Off';
            }elseif($field == 'File'){
                $opt['type'] = '\MelisPlatformFrameworkSymfony\Form\Type\MelisFileType';
                $fileBtnText = 'tool_symfony_tpl_common_choose_file';
                $opt['attr'] = ",\n\t\t\t\t'attr' => [
                    'filestyle_options' => [
                        'buttonBefore' => true,
                        'buttonText' => '".$fileBtnText."',
                    ]
                ],\n\t\t\t\t'data_class' => null";
                //add translation
                if(!array_key_exists($fileBtnText, $this->pre_add_trans['en'])) {
                    $this->pre_add_trans['en'][$fileBtnText] = 'Choose file';
                    $this->pre_add_trans['fr'][$fileBtnText] = 'Choisir un fichier';
                }
                //get all file field name here
                //to prepare the file upload in the controller
                $this->fileInputLists[] = $fieldName;
            }elseif($field == 'TextArea'){
                $opt['type'] = 'TextareaType';
            }else {
                $opt['type'] = 'TextType';
            }
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