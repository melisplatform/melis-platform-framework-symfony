<?php

namespace App\Bundle\SymfonyTpl\Controller;

use App\Bundle\SymfonyTpl\Service\SymfonyTplService;
use App\Bundle\SymfonyTpl\Entity\SampleEntity;
use App\Bundle\SymfonyTpl\Form\Type\SampleEntityFormType;
use MelisPlatformFrameworkSymfony\MelisServiceManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Contracts\Translation\TranslatorInterface;

class SampleEntityController extends AbstractController
{
    /**
     * Store all the parameters
     * @var ParameterBagInterface
     */
    protected $parameters;
    /**
     * @var $toolService
     */
    protected $toolService;

    /**
     * SampleEntityController constructor.
     * @param ParameterBagInterface $parameterBag
     * @param SymfonyTplService $toolService
     */
    public function __construct(ParameterBagInterface $parameterBag, SymfonyTplService $toolService)
    {
        $this->parameters = $parameterBag;
        $this->toolService = $toolService;
    }

    /**
     * Override getSubscribedServices function inside AbstractController
     * to add the MelisServiceManager and translator
     * since AbstractController only uses a limited container
     * that only contains some services.
     * Or you can use Dependency Injection.
     *
     * @return array
     */
    public static function getSubscribedServices()
    {
        return array_merge(parent::getSubscribedServices(),
        [
            'melis_platform.service_manager' => MelisServiceManager::class,
            'translator' => TranslatorInterface::class,
        ]);
    }

    /**
     * Function to get the tool
     *
     * @return Response
     */
    public function getSymfonyTplTool(): Response
    {
        try {
            $view = $this->render('@SymfonyTpl/lists.html.twig',
                [
                    'tableConfig' => $this->getTableConfig(),
                    'modalConfig' => $this->getModalConfig()
                ])->getContent();

            return new Response($view);
        }catch (\Exception $ex){
            exit($ex->getMessage());
        }
    }

    /**
     * Get data
     * @param Request $request
     * @return JsonResponse
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function getSampleEntityData(Request $request)
    {
        /**
         * Prepare the serializer to convert
         * Entity object to array
         */
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        /**
         * Prepare all the parameters needed
         * for data table
         */
        //get sort order
        $sortOrder = $request->get('order', 'ASC');
        $sortOrder = $sortOrder[0]['dir'];
        //get column name to sort
        $colId = array_keys($this->getTableConfigColumns());
        $selCol = $request->get('order', 'sample_primary_id');
        $selCol = $colId[$selCol[0]['column']];
        //convert column name(ex. albName) to exact field name in the table(ex. alb_name)
        $selCol = ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $selCol)), '_');
        //get draw
        $draw = $request->get('draw', 1);
        //get offset
        $start = (int)$request->get('start', 1);
        //get limit
        $length = (int)$request->get('length', 5);
        //get search value
        $search = $request->get('search', null);
        $search = $search['value'];

        //get repository
        $repository = $this->getDoctrine()->getRepository(SampleEntity::class);
        //get total records
        $total = $repository->getTotalRecords();
        //get data
        $tableData = $repository->getSampleEntityData($search, $this->getSearchableColumns(), $selCol, $sortOrder, $length, $start);
        //convert entity object to array
        $tableData = $serializer->normalize($tableData, null);

        for ($ctr = 0; $ctr < count($tableData); $ctr++) {
            // add DataTable RowID, this will be added in the <tr> tags in each rows
            //insert id to every row
            $tableData[$ctr]['DT_RowId'] = $tableData[$ctr]['samplePrimaryId'];
        }

        /**
         * Update column display
         */
        $tableData = $this->toolService->updateTableDisplay($tableData, $this->getTableConfigColumnsDisplay());

        //get total filtered record
        $totalFilteredRecord = $serializer->normalize($repository->getTotalFilteredRecord());

        return new JsonResponse(array(
            'draw' => (int) $draw,
            'recordsTotal' => (int) $total,
            'recordsFiltered' => (int) count($totalFilteredRecord),
            'data' => $tableData,
        ));
    }

    /**
     * Get SymfonyTpl Modal Content
     * @param $id
     * @return Response
     */
    public function getSymfonyTplModalContent($id)
    {
        try{
            $data = [];
            $translator = $this->get('translator');
            foreach($this->getModalConfig()['tabs'] as $tabName => $tab) {
                /**
                 * Form key on the modal config is optional
                 * but if it exist, then we gonna use it as
                 * our modal content, else we use the content
                 * key value in the modal config. Content key value is
                 * the default content of modal
                 *
                 *
                 * Check if modal tab is gonna use a form
                 */
                if(!empty($tab['form'])) {
                    $entityName = $tab['form']['entity_class_name'];
                    $formTypeName = $tab['form']['form_type_class_name'];
                    $formView = $tab['form']['form_view_file'];
                    $formId = $tab['form']['form_id'];
                    /**
                     * If id is not empty,
                     * then we retrieve the data by id and
                     * pass it data to form
                     * else just create the form
                     */
                    if (!empty($id)) {
                        $entity = $this->getDoctrine()
                            ->getRepository($entityName)
                            ->find($id);

                        if (!$entity) {
                            throw $this->createNotFoundException(
                                $translator->trans('tool_symfony_tool_no_found_item') . ' ' . $id
                            );
                        }
                    } else {
                        $entity = new $entityName();
                    }
                    /**
                     * Create form
                     */
                    $form = $this->createForm($formTypeName, $entity, [
                        'attr' => [
                            'id' => $formId
                        ]
                    ]);
                    /**
                     * get languages if we have a language tab
                     */
                    if($tabName == 'tab_language'){
                        $languages = $this->toolService->getCmsLanguages();
                        $param['languages'] = $languages;
                        $forms = [];
                        /**
                         * This will create a form per language
                         */
                        foreach($languages as $key => $lang){
                            $locale = $lang['lang_cms_locale'];
                            $forms[$locale] = $form->createView();
                        }
                        $param['form'] = $forms;
                    }else {
                        $param['form'] = $form->createView();
                    }
                    $data[$tabName] = $this->renderView($formView, $param);
                }
            }
            return new JsonResponse($data);
        }catch (\Exception $ex){
            exit($ex->getMessage());
        }
    }

    /**
     * Save SampleEntity
     * @param $id
     * @param Request $request
     * @return JsonResponse
     */
    public function saveSampleEntity($id, Request $request): JsonResponse
    {
        $itemId = null;
        $result = [
            'title' => 'SampleEntity',
            'success' => false,
            'message' => '',
            'errors' => []
        ];

        try {
            $translator = $this->get('translator');
            if($request->getMethod() == 'POST') {
                $entityManager = $this->getDoctrine()->getManager();
                if (empty($id)) {//create new album
                    $entity = new SampleEntity();
                    //set typeCode form logs
                    $typeCode = 'SYMFONYTPL_TOOL_SAVE';
                } else {//update album
                    $entity = $entityManager->getRepository(SampleEntity::class)->find($id);
                    //set typeCode form logs
                    $typeCode = 'SYMFONYTPL_TOOL_UPDATE';
                    if (!$entity) {
                        throw $this->createNotFoundException(
                            $translator->trans('tool_symfony_tpl_no_found_item') .' '. $id
                        );
                    }
                }
                $form = $this->createForm(SampleEntityFormType::class, $entity);
                $form->handleRequest($request);
                //validate form
                if($form->isSubmitted() && $form->isValid()) {
                    $entity = $form->getData();
                    // tell Doctrine you want to (eventually) save the data (no queries yet)
                    $entityManager->persist($entity);
                    // executes the queries
                    $entityManager->flush();
                    //get id
                    $itemId = $entity->getSamplePrimaryId();

                    $result['message'] = (empty($id)) ? $translator->trans('tool_symfony_tpl_successfully_saved') : $translator->trans('tool_symfony_tpl_successfully_updated');
                    $result['success'] = true;
                    //set icon for flash messenger
                    $icon = 'glyphicon-info-sign';
                }else{
                    $result['message'] = (empty($id)) ? $translator->trans('tool_symfony_tpl_unable_to_save') : $translator->trans('tool_symfony_tpl_unable_to_update');
                    $result['errors'] = $this->toolService->getErrorsFromForm($form);
                    //set icon for flash messenger
                    $icon = 'glyphicon-warning-sign';
                }

                //add message notification
                $this->toolService->addToFlashMessenger($result['title'], $result['message'], $icon);
                //save logs
                $this->toolService->saveLogs($result['title'], $result['message'], $result['success'], $typeCode, $itemId);
            }
        }catch (\Exception $ex){
            $result['message'] = $ex->getMessage();
        }

        return new JsonResponse($result);
    }

    /**
     * Delete SampleEntity
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function deleteSampleEntity(Request $request): JsonResponse
    {
        $icon = 'glyphicon-warning-sign';
        $typeCode = 'SYMFONYTPL_TOOL_DELETE';
        $id = $request->get('id', null);

        $translator = $this->get('translator');

        $result = [
            'title' => 'SampleEntity',
            'success' => false,
            'message' => $translator->trans('tool_symfony_tpl_cannot_delete'),
        ];
        try {
            $entityManager = $this->getDoctrine()->getManager();
            $entity = $entityManager->getRepository(SampleEntity::class)->find($id);
            $entityManager->remove($entity);
            $entityManager->flush();
            $result['message'] = $translator->trans('tool_symfony_tpl_successfully_deleted');
            $result['success'] = true;
            $icon = 'glyphicon-info-sign';
        }catch (\Exception $ex){
            throw new \Exception($ex->getMessage());
        }

        //add message notification
        $this->toolService->addToFlashMessenger($result['title'], $result['message'], $icon);
        //save logs
        $this->toolService->saveLogs($result['title'], $result['message'], $result['success'], $typeCode, $id);

        return new JsonResponse($result);
    }

    /**
     * Get searchable columns
     * @return array|mixed
     */
    private function getSearchableColumns()
    {
        if(!empty($this->getTableConfig()['searchables'])){
            return $this->getTableConfig()['searchables'];
        }
        return [];
    }

    /**
     * Get table columns
     * @return array
     */
    private function getTableConfigColumnsDisplay()
    {
        if(!empty($this->getTableConfig()['columnDisplay'])){
            return $this->getTableConfig()['columnDisplay'];
        }
        return [];
    }

    /**
     * Get table columns
     * @return array
     */
    private function getTableConfigColumns()
    {
        if(!empty($this->getTableConfig()['columns'])){
            return $this->getTableConfig()['columns'];
        }
        return [];
    }

    /**
     * Get table config
     * @return mixed|string
     */
    private function getTableConfig()
    {
        $tableConfig = [];
        if(!empty($this->parameters->get('symfony_tpl_table'))){
            $tableConfig = $this->parameters->get('symfony_tpl_table');
            $tableConfig = $this->toolService->translateConfig($tableConfig);
        }
        return $tableConfig;
    }

    /**
     * Get modal config
     * @return array|mixed
     */
    private function getModalConfig()
    {
        $modalConfig = [];
        if(!empty($this->parameters->get('symfony_tpl_modal'))){
            $modalConfig = $this->parameters->get('symfony_tpl_modal');
            $modalConfig = $this->toolService->translateConfig($modalConfig);
        }
        return $modalConfig;
    }

    /**
     * Get Melis Service Manager
     * @return object
     */
    private function melisServiceManager()
    {
        return $this->get('melis_platform.service_manager');
    }
}