<?php

namespace App\Bundle\SymfonyTpl\Controller;

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
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameters = $parameterBag;
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

        //insert album id to every row
        for ($ctr = 0; $ctr < count($tableData); $ctr++) {
            // add DataTable RowID, this will be added in the <tr> tags in each rows
            $tableData[$ctr]['DT_RowId'] = $tableData[$ctr]['samplePrimaryId'];
        }

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
     * Create form
     * @param $id
     * @return Response
     */
    public function createSampleEntityForm($id)
    {
        try{
            $translator = $this->get('translator');
            /**
             * If id is not empty,
             * then we retrieve the data by id and
             * pass it data to form
             * else just create the form
             */
            if(!empty($id)) {
                $entity = $this->getDoctrine()
                    ->getRepository(SampleEntity::class)
                    ->find($id);

                if (!$entity) {
                    throw $this->createNotFoundException(
                        $translator->trans('tool_symfony_tpl_no_found_item').' '. $id
                    );
                }
            }else{
                $entity = new SampleEntity();
            }

            /**
             * Create album form
             */
            $form = $this->createForm(SampleEntityFormType::class, $entity, [
                'attr' => [
                    'id' => 'sampleEntity_form'
                ]
            ]);

            return $this->render('@SymfonyTpl/form.html.twig', ['form' => $form->createView()]);
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
                    $result['errors'] = $this->getErrorsFromForm($form);
                    //set icon for flash messenger
                    $icon = 'glyphicon-warning-sign';
                }

                //add message notification
                $this->addToFlashMessenger($result['title'], $result['message'], $icon);
                //save logs
                $this->saveLogs($result['title'], $result['message'], $result['success'], $typeCode, $itemId);
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
        $this->addToFlashMessenger($result['title'], $result['message'], $icon);
        //save logs
        $this->saveLogs($result['title'], $result['message'], $result['success'], $typeCode, $id);

        return new JsonResponse($result);
    }

    /**
     * Get form errors
     * @param FormInterface $form
     * @return array
     */
    private function getErrorsFromForm(FormInterface $form)
    {
        $translator = $this->get('translator');
        $errors = array();
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }
        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = $this->getErrorsFromForm($childForm)) {
                    $errMessage = $childErrors[0] ?? null;
                    $fieldLabel = $childForm->getConfig()->getOption('label');
                    $fieldLabel = $translator->trans($fieldLabel);
                    $errors[$childForm->getName()] = ['error_message' => $errMessage, 'label' => $fieldLabel];
                }
            }
        }

        return $errors;
    }

    /**
     * Add logs to notification
     * @param $title
     * @param $message
     * @param string $icon
     */
    private function addToFlashMessenger($title, $message, $icon = 'glyphicon-info-sign')
    {
        $icon = 'glyphicon '.$icon;
        $flashMessenger = $this->melisServiceManager()->getService('MelisCoreFlashMessenger');
        $flashMessenger->addToFlashMessenger($title, $message, $icon);
    }

    /**
     * Save logs
     * @param $title
     * @param $message
     * @param $success
     * @param $typeCode
     * @param $itemId
     */
    private function saveLogs($title, $message, $success, $typeCode, $itemId)
    {
        $logs = $this->melisServiceManager()->getService('MelisCoreLogService');
        $logs->saveLog($title, $message, $success, $typeCode, $itemId);
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
        $translator = $this->get('translator');
        $tableConfig = [];
        if(!empty($this->parameters->get('symfony_tpl_table'))){
            $tableConfig = $this->parameters->get('symfony_tpl_table');
            $tableConfig = $this->translateConfig($tableConfig, $translator);
        }
        return $tableConfig;
    }

    /**
     * Get modal config
     * @return array|mixed
     */
    private function getModalConfig()
    {
        $translator = $this->get('translator');
        $modalConfig = [];
        if(!empty($this->parameters->get('symfony_tpl_modal'))){
            $modalConfig = $this->parameters->get('symfony_tpl_modal');
            $modalConfig = $this->translateConfig($modalConfig, $translator);
        }
        return $modalConfig;
    }

    /**
     * Translate some text in the config
     * @param $config
     * @param $translator
     * @return mixed
     */
    private function translateConfig($config, $translator)
    {
        foreach($config as $key => $value){
            if(is_array($value)){
                $config[$key] = $this->translateConfig($value, $translator);
            }else{
                $config[$key] = $translator->trans($value);
            }
        }
        return $config;
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