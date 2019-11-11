<?php
namespace MelisPlatformFrameworkSymfony\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MelisPlatformFrameworkSymfonyHelperExtension extends AbstractExtension
{
    /**
     * @var $melisServiceManager
     */
    protected $melisServiceManager;
    /**
     * @var $viewHelperManager
     */
    protected $viewHelperManager;
    /**
     * @var $container
     */
    protected $container;

    /**
     * MelisPlatformHelperExtension constructor.
     * @param $melisServiceManager
     * @param $container
     */
    public function __construct($melisServiceManager, $container)
    {
        $this->melisServiceManager = $melisServiceManager;
        $this->viewHelperManager = $melisServiceManager->getViewHelperManager();
        $this->container = $container;
    }

    /**
     * @return array|TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('create_modal', [$this, 'createModal']),
            //register melis platform helper
            new TwigFunction('melis_helper', [$this, 'getMelisPlatformHelper']),
        ];
    }

    /**
     * Create modal
     * @param array $modalConfig - The information needed for modal
     * @param array $btnSuccessConfig - The config needed for button success text and id. ex: ['id' => 'btnUpdate', 'text' => 'Update']
     * @return string
     */
    public function createModal($modalConfig = [], $btnSuccessConfig = [])
    {
        //get translation
        $translation = $this->container->get('translator');
        /**
         * Set modal default value
         */
        $modalConfig['id'] = $modalConfig['id'] ?? 'myModal';
        $modalConfig['title'] = $modalConfig['title'] ?? 'Title';
        $modalConfig['content'] = $modalConfig['content'] ?? 'Modal content';
        $modalConfig['class'] = $modalConfig['class'] ?? 'glyphicons plus';
        /**
         * Set modal button success default settings
         */
        $btnSuccessConfig['id'] = $btnSuccessConfig['id'] ?? 'btn-save';
        $btnSuccessConfig['text'] = $btnSuccessConfig['text'] ?? 'tool_modal_helper_btn_save';

        $loader = '<div id="loader" class="overlay-loader hidden"><img class="loader-icon spinning-cog" src="/MelisCore/assets/images/cog12.svg" data-cog="cog12"></div>';
        $modal =
            '<div class="modal fade" id="'.$modalConfig['id'].'">'.
                '<div class="modal-dialog" role="modal">'.
                    '<div class="modal-content" id="'.$modalConfig['id'].'">'.
                        $loader.
                        '<div class="modal-body padding-none">'.
                            '<div class="wizard">'.
                                '<div class="widget widget-tabs widget-tabs-double widget-tabs-responsive margin-none border-none">'.
                                    '<div class="widget-head">'.
                                        '<ul class="nav nav-tabs">'.
                                            '<li class="active">'.
                                                '<a href="#myTab" class="'.$modalConfig['class'].'" data-toggle="tab" aria-expanded="true"><i></i>'.
                                                    '<p class="modal-tab-title">'.$translation->trans($modalConfig['title']).'</p>'.
                                                '</a>'.
                                            '</li>'.
                                        '</ul>'.
                                    '</div>'.
                                    '<div class="widget-body innerAll inner-2x">'.
                                        '<div class="tab-content">'.
                                            '<div class="tab-pane active" id="myTab">'.$modalConfig['content'].'</div>'.
                                        '</div>'.
                                        '<div align="right">'.
                                            '<button type="button" data-dismiss="modal" class="btn btn-danger pull-left">'.$translation->trans('tool_modal_helper_btn_cancel').'</button>'.
                                            '<button type="button" class="btn btn-success" id="'.$btnSuccessConfig['id'].'">'.$translation->trans($btnSuccessConfig['text']).'</button>'.
                                        '</div>'.
                                    '</div>'.
                                '</div>'.
                            '</div>'.
                        '</div>'.
                    '</div>'.
                '</div>'.
            '</div>';

        return $modal;
    }

    /**
     * Register Melis Platform helper using the TwigFunction
     * Example usage inside view(twig template):
     *      melis_helper('melislink', null, [1, true]) - helper reference: MelisFront\View\Helper\MelisLinksHelper.php
     *
     * Parameters:
     *      1st param - melis helper name
     *      2nd param - function name to call inside melis helper; if null, the __invoke function will be called
     *      3rd param - parameters needed
     *
     * @param $helperName
     * @param null $functionName
     * @param null $params
     * @return mixed|string
     * @throws \Exception
     */
    public function getMelisPlatformHelper($helperName, $functionName = null, $params = null)
    {
        /**
         * Check if helper is in the list
         * of melis helper
         */
        if(in_array($helperName, $this->melisServiceManager->getMelisHelperList())) {
            $helperName = strtolower($helperName);
            $helper = $this->viewHelperManager->get($helperName);
            /**
             * Check function name if not empty
             * to execute it
             */
            if (!empty($functionName)) {
                /**
                 * Check parameters to apply
                 */
                if (!empty($params))
                    return $helper->$functionName($params);
                else
                    return $helper->$functionName();
            } else {
                //get helper method list
                $methods = get_class_methods(get_class($helper));
                /**
                 * If the helper has an __invoke method,
                 * then we execute it
                 */
                $invoke = '__invoke';
                if (in_array($invoke, $methods)) {
                    /**
                     * Check parameters to apply
                     */
                    if (!empty($params))
                        return call_user_func_array($helper, $params);
                    else
                        return $helper();
                } else {
                    return '';
                }
            }
        }else{
            throw new \Exception('Unrecognized helper name: '. $helperName);
        }
    }
}