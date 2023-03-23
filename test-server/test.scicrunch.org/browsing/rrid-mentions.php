<?php if(!isset($_SESSION["user"])): ?>
   <h4>This page is not available to the public yet.</h4>
    <?php return; ?>
<?php endif ?>

<script src="/js/angular-1.7.9/angular.min.js"></script>
<script src="/js/ui-bootstrap-tpls-2.5.0.min.js"></script>
<script src="/js/angular-1.7.9/angular-sanitize.js"></script>
<script src="/js/module-error.js"></script>
<script src="/js/module-rrid-mentions.js"></script>

<style>
    .loading-overlay {
        z-index: 999;
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: #000;
        opacity: 0.3;
    }
    .facet-item {
        padding: 5px;
        border-bottom: 1px solid #CCC;
    }
</style>

<div class="margin-top-20" id="rrid-mentions-app" ng-controller="rridMentionsController as rmc" ng-cloak>
    <div class="row">
        <h4>Research Resource Dashboard</h4>
        <p>
            This dashboard allows users to view and filter all papers that use research resource identifiers, RRIDs, curated by the SciCrunch Team. This source is updated every seven days with new data.
        </p>
    </div>
    <div class="row">
        <div class="col-md-2">
            <form ng-submit="rmc.requestQuery()">
                <input type="text" ng-model="rmc.query"/>
                <input type="submit" class="btn btn-success" value="Search" />
            </form>
            <h4>{{ rmc.results_count | number }} results</h4>
        </div>
        <div class="col-md-10">
            <div>
                <strong>Facets:</strong>
                <span class="label label-info" style="margin-left:5px; cursor:pointer" ng-click="rmc.requestFacet(facet.facet, facet.value)" ng-repeat="facet in rmc.facets">
                    <i class="fa fa-times-circle" style="color:red"></i>
                    {{ facet.facet }}: {{ facet.value }}
                </span>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div>
                <div style="position: relative">
                    <div ng-class="{'loading-overlay': rmc.loading}"></div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <div class="row">
                                <div class="col-md-8">
                                    <ul uib-pagination
                                        ng-hide="!rmc.results_count"
                                        total-items="rmc.results_count"
                                        items-per-page="rmc.per_page"
                                        ng-model="rmc.page"
                                        ng-change="rmc.requestChangePage()"
                                        max-size="7"
                                        boundary-links="true"
                                    ></ul>
                                </div>
                                <div class="col-md-4">
                                    <div style="margin: 10px" class="pull-right">
                                        <a target="_self" ng-href="/php/rrid-mentions-csv.php?{{ rmc.getGetQueryParams() }}">
                                            <button class="btn btn-primary">
                                                <i class="fa fa-download"></i> Download
                                            </button>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <thead>
                                <tr>
                                    <th colspan="4" class="text-center">Publication</th>
                                    <th colspan="3" class="text-center">RRID</th>
                                </tr>
                                <tr>
                                    <th>PMID</th>
                                    <th>
                                        Journal
                                        <span dropdown auto-close="outsideClick" on-toggle="rmc.toggleFacetDropdown(open)">
                                            <a href="javascript:void(0)" dropdown-toggle>Facets</a>
                                            <ul class="dropdown-menu" dropdown-menu>
                                                <li class="facet-item">
                                                    Filter:
                                                    <input type="text" ng-model="rmc.facetDropdownFilter" />
                                                </li>
                                                <li class="facet-item" ng-repeat="journal in rmc.facet_counts.journal | filter:rmc.facetDropdownFilter | orderBy:'-count' | limitTo:20" ng-if="journal.facet">
                                                    <a href="javascript:void(0)" ng-click="rmc.requestFacet('journal', journal.facet)">
                                                        <i ng-show="rmc.isFacetInUse('journal', journal.facet)" style="color:green" class="fa fa-check"></i>
                                                        {{ journal.facet }}: {{ journal.count }}
                                                    </a>
                                                </li>
                                            </ul>
                                        </span>
                                    </th>
                                    <th>
                                        Year
                                        <span dropdown auto-close="outsideClick" on-toggle="rmc.toggleFacetDropdown(open)">
                                            <a href="javascript:void(0)" dropdown-toggle>Facets</a>
                                            <ul class="dropdown-menu" dropdown-menu>
                                                <li class="facet-item">
                                                    Filter:
                                                    <input type="text" ng-model="rmc.facetDropdownFilter" />
                                                </li>
                                                <li class="facet-item" ng-repeat="year in rmc.facet_counts.year | filter:rmc.facetDropdownFilter | orderBy:'-count' | limitTo:20" ng-if="year.facet">
                                                    <a href="javascript:void(0)" ng-click="rmc.requestFacet('year', year.facet)">
                                                        <i ng-show="rmc.isFacetInUse('year', year.facet)" style="color:green" class="fa fa-check"></i>
                                                        {{ year.facet }}: {{ year.count }}
                                                    </a>
                                                </li>
                                            </ul>
                                        </span>
                                    </th>
                                    <th>Funding cited</th>
                                    <th>RRID</th>
                                    <th>Name</th>
                                    <th style="position:relative">
                                        Provider
                                        <span dropdown auto-close="outsideClick" on-toggle="rmc.toggleFacetDropdown(open)">
                                            <a href="javascript:void(0)" dropdown-toggle>Facets</a>
                                            <ul class="dropdown-menu" style="left:auto;right:-1px" dropdown-menu>
                                                <li class="facet-item">
                                                    Filter:
                                                    <input type="text" ng-model="rmc.facetDropdownFilter" />
                                                </li>
                                                <li class="facet-item" ng-repeat="provider in rmc.facet_counts.provider | filter:rmc.facetDropdownFilter | orderBy:'-count' | limitTo:20" ng-if="provider.facet">
                                                    <a href="javascript:void(0)" ng-click="rmc.requestFacet('provider', provider.facet)">
                                                        <i ng-show="rmc.isFacetInUse('provider', provider.facet)" style="color:green" class="fa fa-check"></i>
                                                        {{ provider.facet }}: {{ provider.count }}
                                                    </a>
                                                </li>
                                            </ul>
                                        </span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr ng-repeat="res in rmc.results">
                                    <td><a target="_blank" ng-href="/{{ res.pmid }}">PMID:{{ res.pmid }}</a></td>
                                    <td>
                                        <span ng-show="!!res.journal">
                                            <a href="javascript:void(0)" ng-click="rmc.requestFacet('journal', res.journal)"><i class="fa fa-filter"></i></a>
                                            {{ res.journal }}
                                        </span>
                                    </td>
                                    <td>
                                        <span ng-show="!!res.publication_year">
                                            <a href="javascript:void(0)" ng-click="rmc.requestFacet('year', res.publication_year)"><i class="fa fa-filter"></i></a>
                                            {{ res.publication_year }}
                                        </span>
                                    </td>
                                    <td>
                                        <div ng-repeat="(agency,grant_info) in res.grants">
                                            <a href="javascript:void(0)" ng-click="rmc.requestFacet('funder', agency)"><i class="fa fa-filter"></i></a>
                                            <span tooltip-placement="top" tooltip="{{ grant_info.join(', ') }}">{{ agency }}</span>
                                        </div>
                                    </td>
                                    <td><a target="_blank" ng-href="<?php echo PROTOCOL . "://" . FQDN ?>/resolver/{{ res.rrid }}">{{ res.rrid }}</a></td>
                                    <td>{{ res.name }}</td>
                                    <td>
                                        <a href="javascript:void(0)" ng-click="rmc.requestFacet('provider', res.provider)"><i class="fa fa-filter"></i></a>
                                        {{ res.provider }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <ul uib-pagination
                            ng-hide="!rmc.results_count"
                            total-items="rmc.results_count"
                            items-per-page="rmc.per_page"
                            ng-model="rmc.page"
                            ng-change="rmc.requestChangePage()"
                            max-size="7"
                            boundary-links="true"
                        ></ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
