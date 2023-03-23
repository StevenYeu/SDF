<?php

$user = $data["user"];
$community = $data["community"];
$error = $data["error"];

$rrid_reports = RRIDReport::loadArrayBy(Array("uid"), Array($user->id));
$base_uri = Community::fullURLStatic($community) . "/rin/rrid-report";

?>

<div id="rrid-report-overview" ng-controller="overviewController as ctrl">
    <div class="row">
        <div class="col-md-8">
            <h2><strong>Creating a dkNET Authentication Report</strong></h2>
            <p>
              For NIH grant applications, dkNET can assist you in preparing authentication plans for cell lines or antibodies to <a target="_blank" href="https://dknet.org/about/NIH-Policy-Rigor-Reproducibility">comply with the NIH Submission Policy</a>. View an example of authentication plan <a target="_blank" href="https://dknet.org/upload/community-components/ExampleAuthenticationKeyBiologicalChemicalResources201609b.pdf">here</a>, prepared by Dr. Anita Bandrowski, University of California San Diego.
            </p>
            <p>
              Below is general information on best practices for authenticating cell lines and antibodies. dkNET has also created an automated tool to enable researchers to create a customized report based on the cell lines and antibodies they plan to use.  These customized reports provide additional information such as known problems with a particular cell line or antibody. If you wish to use dkNET tools to create a dkNET Authentication Report, click <a href="<?php echo $community->fullURL().'/rin/rrid-report/overview' ?>" class="btn btn-primary">Start here</a>
            </p>
            <br>
            <h2><strong>Basic Authentication Information Without Creating an Authentication Report</strong></h2>
            <details style="color:#408dc9">
              <summary><strong><span style="font-size:20px; color:#408dc9; cursor: pointer;">Cell Line Authentication Plan Information</span></strong></summary>
              <div>
                <p>
                  The authentication plan for the cell lines is based on the International Cell Line Authentication Committee (ICLAC)'s "Cell Line Checklist for Manuscripts and Grant Applications"(1).
                </p>
                <ol type="A">
                  <li>
                    <p>Check dkNET for Misidentified Cell Lines</p>
                    <p>If you have not created a dkNET Authentication Report, you can check for this information via dkNET Resource Reports.
                      <br><a target="_blank" href="https://dknet.org/data/source/SCR_013869-1/search"><i class="fa fa-external-link"></i><strong><u> Check Resource Information</u></strong></a></p>
                    <p>To verify that this is not a false cell line, misidentified, or to check this is known to be an authentic stock, please check via dkNET. dkNET obtains this information from Cellosaurus (2).</p>
                    <a target="_blank" href="/upload/community-components/CellLineResourceReport1_3def184ad8f4755f.png"><img src="/upload/community-components/CellLineResourceReport1_3def184ad8f4755f.png" class="w3-round-large" alt="Norway" style="width:50%"></a>
                    <br><i class="fa fa-search-plus"><small> Click to enlarge</small></i>
                  </li><br>
                  <li>
                    <p>
                      Provide the following information in the Authentication of key biological and/or chemical resources attachment(3) in the grant application:
                    </p>
                    <ol type="a">
                      <li>
                        <p>Identification of cell lines</p>
                        <ol type="i">
                          <li>
                            <p>Provide Name, Vendor, Catalog#, RRID (Find RRID via dkNET Resource Report)</p>
                          </li>
                          <li>
                            <p>If you can't find your cell lines in the system, please register it at Cellosaurus(2). An RRID will be generated in 1-2 business day.</p>
                          </li>
                        </ol>
                      </li>
                      <li>
                        <p>Short Tandem Repeat (STR) Profiling</p>
                        <p>
                          The gold standard for authentication testing of cell lines is STR profiling. STR profiling should be performed and compared to results from donor tissue, or to online databases of cell line STR profiles. Authentication testing should be performed on established cell lines regardless of the application, and the test method and results included in the Materials and Methods section of any research publication. Testing should be done, at minimum, at the beginning and end of experimental work(4, 5).
                        </p>
                      </li>
                    </ol>
                  </li>
                </ol>
                <p>Sources:</p>
                <ol>
                  <li>
                    <a target="_blank" href="http://iclac.org/resources/cell-line-checklist/">Cell Line Checklist for Manuscripts and Grant Applications</a>, International Cell Line Authentication Committee (ICLAC), <a target="_blank" href="http://iclac.org/wp-content/uploads/ICLAC_Cell-Line-Checklist_v1_2.docx">version 1.2</a>, updated on May 9, 2014.
                  </li>
                  <li>
                    <a target="_blank" href="https://web.expasy.org/cellosaurus/">Cellosaurus (https://web.expasy.org/cellosaurus/)</a> is a cell line knowledge resource and nomenclature authority. Cellosaurus covers the information of "ICLAC register of misidentified cell lines" (<a target="_blank" href="http://iclac.org/wp-content/uploads/Cross-Contaminations-v8_0.pdf">latest version 8.0</a>, released Dec. 1, 2016,) and provides more updated information.
                  </li>
                  <li>
                    <a target="_blank" href="https://grants.nih.gov/policy/reproducibility/guidance.htm">Guidance: Rigor and Reproducibility in Grant Applications</a>
                  </li>
                  <li>
                    <a target="_blank" href="https://www.ncbi.nlm.nih.gov/pmc/articles/PMC4907466/">Standards for Cell Line Authentication and Beyond, The National Institute of Standards and Technology (NIST)</a>.
                  </li>
                  <li>
                    Published standard: <a target="_blank" href="https://webstore.ansi.org/RecordDetail.aspx?sku=ANSI%2FATCC+ASN-0002-2011&gclid=EAIaIQobChMItvfblI6T3AIVkspkCh1yPguAEAAYASAAEgIaJfD_BwE">ANSI/ATCC ASN-0002-2011</a>, Authentication of Human Cell Lines: Standardization of STR Profiling, American National Standards Institute (ANSI).
                  </li>
              </div>
            </details>
            <details style="color:#408dc9">
              <summary><strong><span style="font-size:20px; color:#408dc9; cursor: pointer;">Antibody Authentication Plan Information</span></strong></summary>
              <div>
                <p>
                  This authentication plan for antibodies is based on the methods suggested in "A proposal for validation of antibodies" (Uhlen M et. al., 2016)(1), the guideline published in the Journal of Comparative Neurology (Saper C, 2005)(2), and the Example Authentication of of Key Biological and/or Chemical Resources (Bandrowski A)(3).
                </p>
                <ol type="A">
                  <li>
                    <p>Check dkNET for Known Issues in Antibodies</p>
                    <p>If you have not created dkNET Authentication Reports, you can check the following information at dkNET Resource Reports.
                      <br><a target="_blank" href="https://dknet.org/data/source/nif-0000-07730-1/search"><i class="fa fa-external-link"></i><strong><u> Check Resource Information</u></strong></a></p>
                    <p>Please check your antibody information at dkNET to determine whether there are any known issue. dkNET obtains this information from the Antibody Registry(4).</p>
                  </li>
                  <li>
                    <p>
                      Provide the following information in the Authentication of key biological and/or chemical resources attachment(5) in the grant application:
                    </p>
                    <ol type="a">
                      <li>
                        <p>Identification of antibodies</p>
                        <ol type="i">
                          <li>
                            <p>Provide Name, Vendor, Catalog#, RRID (Find RRID via dkNET Resource Report)</p>
                          </li>
                          <li>
                            <p>
                              If you can't find your antibody in the system, please register it at Antibody Registry(4). An RRID will be generated in 1-2 business day.
                            </p>
                          </li>
                        </ol>
                      </li>
                      <li>
                        <p>Validation</p>
                        <ol type="i">
                          <li>
                            <p>
                              Antibody validation must be carried out in an application- and context-specific manner (Uhlen et al., 2016), i.e., just because you used an antibody successfully in one application, doesn’t mean it will translate. To validate an antibody, it must be shown to be specific, selective, and reproducible in the context for which it is to be used (<a target="_blank" href="https://www.ncbi.nlm.nih.gov/pmc/articles/PMC3891910/">Bordeaux et al., 2014</a>). See ii. Suggested validation methods. When selecting an antibody to use, pay special attention to the following:
                              <br><a target="_blank" href="https://dknet.org/data/source/nif-0000-07730-1/search"><i class="fa fa-external-link"></i><strong><u> Check Resource Information</u></strong></a>
                            </p>
                            <ol type="a">
                              <li>
                                <p>
                                  <strong>Check Target Organism</strong> - Check to see if the antibody has been developed for and tested in the target species for your experiment in the Target Organism field.
                                </p>
                              </li>
                              <li>
                                <p>
                                  <strong>Check Application</strong> - Check if your planned applications included in the recommended applications provided by the vendor listed in the Comments field. We do not recommend using an antibody that has not been tested for a specific application.
                                </p>
                              </li>
                              <li>
                                <p>
                                  <strong>Check Validation Information</strong> - Check validation information to see if it is a widely used reagent or if any concerns have been raised.
                                </p>
                              </li>
                            </ol>
                          </li>
                          <a target="_blank" href="/upload/community-components/AntibodyResourceReport2_006f52e9102a8d3b.png"><img src="/upload/community-components/AntibodyResourceReport2_006f52e9102a8d3b.png" class="w3-round-large" alt="Norway" style="width:50%"></a>
                          <br><i class="fa fa-search-plus"><small> Click to enlarge</small></i><br><br>
                          <li>Suggested validation methods based on applications(1)</li>
                        </ol>
                      </li>
                    </ol>
                    <br>
                    <table class="table table-striped" width="100%">
                      <tr>
                        <th class="showing" style="width:15%;">Validation strategy</th>
                        <th class="showing" style="width:17%;">Genetic</th>
                        <th class="showing" style="width:17%;">Orthogonal</th>
                        <th class="showing" style="width:17%;">Independent antibody</th>
                        <th class="showing" style="width:17%;">Tagged protein expression</th>
                        <th class="showing" style="width:17%;">IMS</th>
                      </tr>
                      <tr>
                        <td class="showing">Validation principle</td>
                        <td class="showing">The expression of the target protein is eliminated or significantly reduced by genome editing or RNA interference</td>
                        <td class="showing">Expression of the target protein is compared with an antibody-independent method</td>
                        <td class="showing">Expression of the target protein is compared using two antibodies with nonoverlapping epitopes</td>
                        <td class="showing">The target protein is expressed using a tag, preferably expressed at endogenous levels</td>
                        <td class="showing">The target protein is captured using an antibody and analyzed using MS</td>
                      </tr>
                      <tr>
                        <td class="showing">Validation criteria</td>
                        <td class="showing">Elimination or significant reduction in antibody labeling after gene disruption or mRNA knockdown<br><br>
                          <a target="_blank" href="https://dknet.org/data/source/nlx_154697-1/search"><button class="btn btn-primary" style="white-space: normal; width:95%;">Search dkNET for knockout organism</button></a>
                        </td>
                        <td class="showing">Significant correlation of protein levels detected by an antibody and an orthogonal method (e.g., MS)</td>
                        <td class="showing">Significant correlation of protein levels detected by two different antibodies recognizing independent regions of the same target protein</td>
                        <td class="showing">Significant correlation between antibody labeling and detection of the epitope tag</td>
                        <td class="showing">Target protein peptides among the most abundant detected by MS following immunocapture</td>
                      </tr>
                      <tr>
                        <td class="showing">Suitable for these applications</td>
                        <td class="showing">WB, IHC, ICC, FS, SA, IP/ChIP, RP</td>
                        <td class="showing">WB, IHC, ICC, FS, SA, RP</td>
                        <td class="showing">WB, IHC, ICC, FS, SA, IP/ChIP, RP</td>
                        <td class="showing">WB, IHC, ICC, FS</td>
                        <td class="showing">IP/ChIP</td>
                      </tr>
                    </table>
                    <small>WB, western blot; IHC, immunohistochemistry; ICC, immunocytochemistry, including immunofluorescence microscopy; FS, flow sorting and analysis of cells; SA, sandwich assays, including ELISA; IP, immunoprecipitation; ChIP, chromatin immunoprecipitation; and RP, reverse-phase protein arrays.</small>
                  </li>
                </ol>
                <p>Sources:</p>
                <ol>
                  <li><a target="_blank" href="https://www.ncbi.nlm.nih.gov/pubmed/27595404">Uhlen M et. al. A proposal for validation of antibodies. Nature Methods, Oct;13(10):823-7. 2016.</a></li>
                  <li><a target="_blank" href="https://onlinelibrary.wiley.com/doi/full/10.1002/cne.20839">Saper C. An open letter to our readers on the use of antibodies. Journal of comparative neurology, 493(4):477-8, 2005.</a></li>
                  <li><a target="_blank" href="https://doi.org/10.6075/J0RB72JC">Bandrowski A.  Example Authentication of of Key Biological and/or Chemical Resources, NIH Policy on Rigor and Reproducibility Section, UC San Diego Library website.</a></li>
                  <li><a target="_blank" href="https://antibodyregistry.org">AntibodyRegistry (https://antibodyregistry.org)</a></li>
                  <li><a target="_blank" href="https://grants.nih.gov/policy/reproducibility/guidance.htm">Guidance: Rigor and Reproducibility in Grant Applications, National Institute of Health Office of Extramural Research Website.</a></li>
                </ol>
              </div>
            </details>
            <br>
            <p>
              <strong>Please note that for NIH grant applications, the plan should be no more than one page.</strong> You can copy and paste the information and edit the information in a word file.
            </p>
        </div>
        <div class="col-md-4" style="border-left: 1px solid grey; height: 100%">
          <br>
          <p>
              <strong>Create Authentication Reports</strong>
          </p>
          <p>The following information is included in the report:
              <ol>
                  <li>Resource information (the current system is limited to antibodies and cell lines.  Additional resources will be added in the future)</li>
                  <li>RRIDs, which are used as unambiguous identifiers of the resources</li>
                  <li>Any noted issues or problems with the resources</li>
                  <li>An authentication plan based on submitted information</li>
              </ol>
          </p>
          <br>
          <p>Go to Authentication Reports Dashboard to begin the process or view my Authentication Reports</p>
          <div>
              <a href="<?php echo $community->fullURL().'/rin/rrid-report/overview' ?>" class="btn btn-primary">Start here</a>
          </div>
          <br>&nbsp;<br>
          <p>An example of Authentication Report</p>
          <a target="_blank" href="/upload/community-components/ReproducibilityReport021519.pdf"><img src="/upload/community-components/ReproducibilityReportCellLine_3988c7f88ebcb58c.png" class="w3-round-large" alt="Norway" style="width:10%"></a>
          <br><i class="fa fa-search-plus"> Click to enlarge</i><br><br>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <p>
                <?php echo \helper\htmlElement("rin/rrid-report-disclaimer") ?>
            </p>
        </div>
    </div>

    <script type="text/ng-template" id="add-report-template.html">
        <div method="post" id="name-form" action="/forms/rrid-report-forms/new-rrid-report.php" class="sky-form" enctype="multipart/form-data">
            <div class="modal-header"><h2>Create new authentication report</h2></div>
            <div class="modal-body">
                <form class="sky-form" ng-submit="submit()">
                    <section>
                        <p class="text-danger">
                            {{ create_error }}
                        </p>
                    </section>
                    <section>
                        <label class="label">Report name</label>
                        <label class="input">
                            <input ng-model="name" type="text" name="name" required>
                        </label>
                    </section>
                    <section>
                        <label class="label">Description</label>
                        <label class="input">
                            <textarea ng-model="description" name="description" style="width:100%" required></textarea>
                        </label>
                    </section>
                    <section>
                        <button class="btn btn-success">Submit</button>
                        <button ng-click="cancel()" class="btn btn-danger">Cancel</button>
                    </section>
                </form>
            </div>
        </div>
    </script>
</div>

<script src="/js/angular-1.7.9/angular.min.js"></script>
<script src="/js/ui-bootstrap-tpls-2.5.0.min.js"></script>
<script src="/js/angular-1.7.9/angular-sanitize.js"></script>
<script src="/js/module-error.js"></script>
<script>
$(function() {
    var app = angular.module("rridReportOverviewApp", ["errorApp", "ui.bootstrap"]);

    app.controller("overviewController", ["$log", "$uibModal", function($log, $uibModal) {
        var that = this;

        this.newReport = function() {
            var modal_instance = $uibModal.open({
                animation: true,
                templateUrl: "add-report-template.html",
                controller: "addTemplateModalController"
            });
            modal_instance
        };
    }]);

    app.controller("addTemplateModalController", ["$http", "$log", "$uibModalInstance", "$scope", function($http, $log, $uibModalInstance, $scope) {
        $scope.submit = function() {
            var data = {
                name: $scope.name,
                description: $scope.description
            };
            $http.post("/api/1/rrid-report/new", data)
                .then(function(response) {
                    window.location.href = window.location.pathname + "/" + response.data.data.id;
                    $uibModalInstance.close();
                }, function(error) {
                    $scope.create_error = "There was a problem creating your new authentication report.  Please try again";
                });
        };

        $scope.cancel = function() {
            $uibModalInstance.dismiss();
        };
    }]);

    angular.bootstrap(document.getElementById("rrid-report-overview"), ["rridReportOverviewApp"]);
});
</script>
