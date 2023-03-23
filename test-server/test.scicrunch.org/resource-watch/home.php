<link rel="stylesheet" href="../assets/plugins/owl-carousel/owl-carousel/owl.carousel.css">
<link rel="stylesheet" href="../assets/plugins/owl-carousel/owl-carousel/owl.theme.css">

<script type="text/javascript" src="../assets/plugins/owl-carousel/owl-carousel/owl.carousel.js"></script>
<script src="../js/term/highcharts5.0.9.js" type="text/javascript"></script>

<script src="../js/angular-chips/ui-bootstrap.js"></script>
<script src="../js/resource-watch/resource-watch.js"></script>

<?php
	// Start Session
	\helper\scicrunch_session_start();

	// TODO Move to a separate file
	$affiliates =
		[
			[
				"url" => "https://web.expasy.org/cellosaurus/",
				"logo" => "cellosaurus_logo.png",
				"name" => "Cellosaurus"
			],
			[
				"url" => "https://antibodyregistry.org/",
				"logo" => "antibody_registry_logo.png",
				"name" => "Antibody Registry"
			],
			[
				"url" => "https://www.encodeproject.org/",
				"logo" => "ENCODE.jpeg",
				"name" => "ENCODE Project"
			],
			[
				"url" => "https://www.proteinatlas.org/",
				"logo" => "Human_Protein_Atlas.png",
				"name" => "The Human Protein Atlas"
			]
		];
?>

<!DOCTYPE html>
<html lang="en">
	<title>Resource Watch | Home</title>
	<header>
		<?php
			echo Connection::createBreadCrumbs('Resource Watch', array() ,array('/ResourceWatch'),'Resource Watch');
		?>
	</header>

	<body>

		<!-- Start of Search block-->

		<div class="search-block-v2" id="searchBlock">
			<div class="container" style="margin-left:3%">
				<div class="row">
					<div class="col-lg-6 col-lg-offset-3 col-md-6 col-md-offset-3 col-sm-12 col-sm-offset-1">
						<form class="form-group row" style="margin-left:27%; min-width:40px; display:flex" method="get" action="/ResourceWatch/Search">
							<a href="javascript:void(0);" style="float:left" data-html="true" data-toggle="popover" data-trigger="focus" title="What is a RRID?" data-placement="left"
								data-content="RRIDs are persistent and unique identifiers for referencing a research resource. <a href='/ResourceWatch/No_Results_Found?q=rrid' target='_blank'>[Learn More]</a>">
								<img src="/images/question.png" style="width:18px; height:18px; float:left; margin-right:5px; margin-top:7px"></img>
							</a>
							<label for="searchBar" class="col-form-label" style="font-size:22px; float:left; margin-right:5px">RRID:</label>
							<div>
								<input id="searchBar" class="form-control" style="width: 190px; float:left" name="q" placeholder="Ex: AB_2341236" value="" type="text"/>
							</div>
							<span>
								<button class="btn-u" type="search">
									<i class="fa fa-search"></i>
								</button>
							</span>
						</form>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-10 col-lg-offset-1 col-md-4 col-md-offset-2 col-sm-10 col-sm-offset-1">
						<p style="text-align:center; min-width:700px">
							<b>If the RRID of the entity you're searching for isn't known, use the following links:</b>
						</p>
					</div>
					<div class="col-lg-4 col-lg-offset-4 col-md-4 col-md-offset-3 col-sm-10 col-sm-offset-2 col-xs-6 col-xs-offset-1">
						<div style="font-size:19px; min-width:700px">
							<a href="https://dknet.org/data/source/nif-0000-07730-1/search" target="_blank" style="float:left"><u>Search for RRID for antibodies</u>&nbsp;|	&nbsp;</a>
							<a href="https://dknet.org/data/source/SCR_013869-1/search" target="_blank"  style="float:left"><u>Search for RRID for cell lines</u></a>
							<!-- <a href="https://scicrunch.org/resources" target="_blank" style="float:left"><u>Search for RRID for tools</u></a> -->
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- End of Search block-->

		<!-- Start of Main block-->

		<div class="container content" style="margin-left:8%; padding-top:10px">

			<div class="row">

				<div class="col-lg-3 col-md-6 col-sm-6 " style="font-size:17px; min-width:300px">
					<div class="col-md-12 vertical-line" style="padding-left:0px; margin-bottom:20px">
						<h2>Report Issue or Validation Information</h2>
						<p>
							<b>It's easy! Follow these steps:</b>
						</p>
						<ul>
							<li>Find your resource</li>
							<li>Fill in a simple form</li>
							<li>Upload the document</li>
						</ul>
						<a class="btn btn-success" href="/ResourceWatch/No_Results_Found?q=rrid">Contribute Information to Resource Watch</a>
					</div>

					<div class="col-md-12 vertical-line" style="padding-left:0px; margin-bottom:60px; min-width:300px">
						<h2>My Submissions</h2>
						<p>
							View pending and past submissions
						</p>
						<a class="btn btn-success redirectBttn" href="/ResourceWatch">Ability To View Submissions Coming Soon...</a>
					</div>
					<!--
					<?php if($_SESSION['user']->role > 0): ?>

						<div class="col-md-12 vertical-line" style="padding-left:0px; margin-bottom:20px">
							<h2>For Curators</h2>
							<p style="font-size:15px">
								Helpful links for curators
								<ul>
									<li><a href="http://0.0.0.0:8090/ResourceWatch"><b>Curator Dashboard</b></a></li>
									<li><a><b>Test1</b></a></li>
									<li><a><b>Test2</b></a></li>
								</ul>
							</p>
						</div>
					<?php endif ?>
					-->
				</div>

				<div class="col-lg-9" id="detailBlock">
					<div class="col-md-6 col-sm-6 col-xs-12 vertical-line">
						<p style="font-size:17px; text-align:justify; text-justify:inter-word">
							Resource Watch serves as a repository for storing validation and problematic information about a variety of entities within
							the biomedical domain, such as antibodies, cell lines, etc.
							<br/><br/>
							Resource Watch allows researchers the ability to access federated data on entities related to their research. Information such
							as whether an antibody has validation information, or whether a cell line has been labeled as contaminated, becomes searchable.
							<br/><br/>
							On top of making such information searchable, the platform enables researchers to submit their own claims about a specific entity.
							This in part enriches Resource Watch, where over time, it will grow to be a central platform for the biomedical community to view
							and share information on materials that they’ve used within their research.
							<br/><br/>
							As a result, the idea of making validation/problematic information
							<a href="https://www.nature.com/articles/sdata201618" target="_blank">F.A.I.R. (Findable, Accessable, Interoperable, Reuseable)</a>
							becomes achievable through the existence of Resource Watch.
						</p>
					</div>

					<div class="col-lg-5 col-md-12 col-sm-12" style="margin-left:30px; text-align:center">
						<h2>Resource Watch Data Repository</h2>
						<div>
							<p>
								<strong style="font-size:15px">
								Resource Watch contains information from various resources:
								</strong>
							</p>
						</div>
						<div id="graph-term" style="width:100%; height:400px;"></div>
					</div>

				</div>
				<div class="row col-lg-6 col-lg-offset-3 col-md-12 col-md-offset-1">
					<div class="headline">
					   <h3 style="text-transform:uppercase;font-size:17px">Data Provided By:</h3></div>
					   <div class="owl-carousel col-md-12" id="affiliates" style="opacity: 1; display: block;">
							<?php foreach($affiliates as $affiliate): ?>
								<div class="item">
									<a href="<?php echo $affiliate['url'] ?>" target="_blank">
										<img class="sponsor-img img-responsive" src="<?php echo './images/' .$affiliate['logo'] ?>"
											 title="<?php echo $affiliate["name"] ?>" alt="<?php echo $affiliate["name"] ?>"
											 style="width:90%; height:90%"/>
									</a>
								</div>
							<?php endforeach ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- End of Main block-->
	</body>

</html>
