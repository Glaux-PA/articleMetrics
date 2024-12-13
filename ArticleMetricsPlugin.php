<?php
/**
 * @file ArticleMetricsPlugin.php
 *
 * Copyright (c) 2017-2023 Simon Fraser University
 * Copyright (c) 2017-2023 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ArticleMetricsPlugin
 * @brief Plugin class for the ArticleMetrics plugin.
 */

namespace APP\plugins\generic\articleMetrics;

use PKP\plugins\GenericPlugin;
use APP\core\Application;
use APP\core\Services;
use APP\facades\Repo;
use PKP\plugins\Hook;

class ArticleMetricsPlugin extends GenericPlugin
{
    /**
     * Register the plugin and enable hooks.
     *
     * @param string $category
     * @param string $path
     * @param null|int $mainContextId
     * @return bool
     */

    public function register($category, $path, $mainContextId = null): bool
    {
        $success = parent::register($category, $path);
    
        if ($success && $this->getEnabled()) {
            $this->addLocaleData();
            // Display the publication statement on the article details page
            Hook::add('TemplateManager::display', [$this,'injectBlock']);
            Hook::add('TemplateManager::display', [$this, 'addAssets']); 
        }

        return $success;
    }

    /**
     * Get the display name of this plugin.
     *
     * @return string
     */
    public function getDisplayName()
    {
        return __('plugins.block.articleMetrics.displayName');
    }

    /**
     * Get a description of the plugin.
     *
     * @return string
     */
    public function getDescription()
    {
        return __('plugins.block.articleMetrics.description');
    }

    
    /**
     * Get the content for the block.
     *
     * @param object $templateMgr
     * @param null|mixed $request
     * @return string
     */
    public function injectBlock($hookName, $args) {

        $templateMgr = $args[0];
        $request = Application::get()->getRequest();
        $router = $request->getRouter();
        $requestedArgs = $router->getRequestedArgs($request);

        $submissionId = $requestedArgs[0] ?? null;
        $submission = is_numeric($submissionId)? Repo::submission()->get(intval($submissionId)) ?? null:null;
    
        if($submission !== null){
            $templateMgr->assign('aggregatedMetrics', $this->getAggregatedMetrics($submissionId));
            $block = $templateMgr->fetch($this->getTemplateResource('articleMetrics.tpl'));
            $templateMgr->assign('articleMetricsHtml', $block);
        }
     
        return false;
    }

    public function addAssets($hookName, $args)
    {
        $request = Application::get()->getRequest();
        $router = $request->getRouter();
        $requestedArgs = $router->getRequestedArgs($request);

        $submissionId = $requestedArgs[0] ?? null;
        $submission = is_numeric($submissionId)? Repo::submission()->get(intval($submissionId)) ?? null:null;

        if($submission !== null){

            $templateManager = $args[0];
            $templateManager->addStyleSheet('styles-css','/'.$this->getPluginPath().'/css/styles.css"');
        }
        return false;
    }

    /**
     * Fetch aggregated metrics for an article.
     *
     * @param int $idArticle
     * 
     */
    private function getAggregatedMetrics($idArticle)
    {
        $request = Application::get()->getRequest();
        $statsService = Services::get('publicationStats');
        $metricsByTypeLastMonth = $statsService->getTotalsByType($idArticle, $request->getContext()->getId(), date("Y-n-j", strtotime("-1 month")), date("Y-n-j"));
        $metricsByTypeLastYear = $statsService->getTotalsByType($idArticle, $request->getContext()->getId(), date("Y-n-j",strtotime("-1 year")), date("Y-n-j"));
        $metricsByTypeTotal = $statsService->getTotalsByType($idArticle, $request->getContext()->getId(),null, null);

        $metricsByType=["lastMonth"=>$metricsByTypeLastMonth,"lastYear"=>$metricsByTypeLastYear,"total"=>$metricsByTypeTotal];


        $metricsAggregated=array_map(function ($metric){
            $views=$metric['abstract'] ?? 0;
            $downloads =array_reduce(array_keys($metric), function($carry, $key) use ($metric){
                
                    if($key !== 'abstract' && $key !== 'suppFileViews'){
                        $carry+= $metric[$key];
                      
                    }
                     return $carry;
            });
            return [
                'views' => $views,
                'downloads' => $downloads,
                'total'=>$views+$downloads

            ];
        },$metricsByType );
   
        return json_encode($metricsAggregated);
    }

}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\plugins\generic\articleMetrics\ArticleMetricsPlugin', '\ArticleMetricsPlugin');
}
