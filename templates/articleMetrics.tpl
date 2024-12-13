{**
 * plugins/generic/articleMetrics/templates/articleMetrics.tpl
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * 
 *}

<section class="pkp_block block_metrics">

    <h3 class="label">
        {translate key="plugins.block.articleMetrics.metrics"}
    </h3>
    <div class="metric-buttons">
        <button class="selected" id="button-total">{translate key="plugins.block.articleMetrics.total"}</button>
        <button id="button-year">{translate key="plugins.block.articleMetrics.year"}</button>
        <button id="button-month">{translate key="plugins.block.articleMetrics.month"}</button>
    </div>
    <div class="content">
        <div class="metrics_views">
            <span></span>
            <label>{translate key="plugins.block.articleMetrics.views"}</label>
        </div>
        <div class="metrics_downloads">
            <span></span>
            <label>{translate key="plugins.block.articleMetrics.downloads"}</label>
        </div>
        <div class="metrics_total">
            <span></span>
            <label>{translate key="plugins.block.articleMetrics.total"}</label>
        </div>
    </div>
</section>

<script type="text/javascript">
    const buttonTotal = document.getElementById("button-total");
    const buttonLastMonth = document.getElementById("button-month");
    const buttonLastYear = document.getElementById("button-year");
	const buttonsArray = [...document.querySelector(".metric-buttons").children];
	const buttons = document.querySelector(".metric-buttons");
	
   	const metricsViews = document.querySelector(".metrics_views");
	const metricsDownloads = document.querySelector(".metrics_downloads");
	const metricsTotal = document.querySelector(".metrics_total");

	const aggregatedMetrics =JSON.parse({$aggregatedMetrics|@json_encode});

	document.addEventListener("DOMContentLoaded", totalMetrics);
	buttonTotal.addEventListener("click",totalMetrics);
	buttonLastMonth.addEventListener("click",lastMonthMetrics);
	buttonLastYear.addEventListener("click",lastYearMetrics);

	function totalMetrics(){
		metricsViews.firstChild.textContent = aggregatedMetrics.total.views || "0";
		metricsDownloads.firstChild.textContent = aggregatedMetrics.total.downloads || "0";
		metricsTotal.firstChild.textContent = aggregatedMetrics.total.total || "0";
	}
	function lastMonthMetrics(){
		metricsViews.firstChild.textContent = aggregatedMetrics.lastMonth.views || "0";
		metricsDownloads.firstChild.textContent = aggregatedMetrics.lastMonth.downloads || "0";
		metricsTotal.firstChild.textContent = aggregatedMetrics.lastMonth.total || "0";
	}

	function lastYearMetrics(){
		metricsViews.firstChild.textContent = aggregatedMetrics.lastYear.views || "0";
		metricsDownloads.firstChild.textContent = aggregatedMetrics.lastYear.downloads || "0";
		metricsTotal.firstChild.textContent = aggregatedMetrics.lastYear.total || "0";
	}


	buttons.addEventListener("click",(e)=>{
		if(e.target.tagName=="BUTTON"){

			for (const button of buttonsArray) {
				button.classList.remove("selected");
			}

			e.target.classList.add("selected");
		}
	})


</script>