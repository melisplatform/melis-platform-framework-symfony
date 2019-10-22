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
    protected $viewHelperManager;

    /**
     * MelisPlatformHelperExtension constructor.
     * @param $melisServiceManager
     */
    public function __construct($melisServiceManager)
    {
        $this->melisServiceManager = $melisServiceManager;
        $this->viewHelperManager = $melisServiceManager->getViewHelperManager();
    }

    /**
     * @return array|TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('create_table', [$this, 'createTable']),
            //register melis platform helper
            new TwigFunction('melis_helper', [$this, 'getMelisPlatformHelper']),
        ];
    }

    /**
     * Create table helper
     *
     * @param array $tableConfig
     * @param array $tableColumns
     * @return string
     */
    public function createTable($tableConfig = array('id' => 'tableId', 'class' => 'table'), $tableColumns = [])
    {
        $table = "<table";
        $thead = "<thead><tr>";
        $tbody = "<tbody></tbody>";
        foreach($tableConfig as $configAttrib => $configValues)
        {
            $table .= ' ' . $configAttrib . ' = "' . $configValues . '"';
        }
        $table .= ">";

        $columnName = '';
        foreach($tableColumns as $values)
        {
            $columnName .= '<th>' . $values . '</th>';
        }
        $thead .= $columnName;
        $thead .= "</tr></thead>";

        $table .= $thead;
        $table .= $tbody;
        $table .= "</table>";

        return $table;
    }

    /**
     * Register Melis Platform helper using the TwigFunction
     * Example usage inside view(twig template):
     *      melis_helper('melislink', null, [1, true]) - helper reference: MelisFront\View\Helper\MelisLinksHelper.php
     *      melis_helper('melisgenerictable', 'setColumns', ['column_name']) - helper reference: MelisCore\View\Helper\MelisGenericTable.php
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