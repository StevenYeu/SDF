<!DOCTYPE html>
<html>
	<title>Resource Watch | No Results Found</title>
	<header>
		<?php
			if ($_GET['q'] == 'rrid') {
				echo Connection::createBreadCrumbs('RRID Information', array('Resource Watch'), array('/ResourceWatch'), 'RRID Information');
			} else {
				echo Connection::createBreadCrumbs('No Results Found', array('Resource Watch'), array('/ResourceWatch'), 'No Results Found');
			}
		?>
	</header>

	<body>

		<!-- Start of Search block-->

		<div class="search-block-v2">
			<div class="container">
				<div class="row">
					<div class="col-lg-8 col-lg-offset-4 col-md-6 col-md-offset-3 col-sm-12 col-sm-offset-2 col-xs-12 col-xs-offset-1">
						<form class="form-group row" method="get" action="/ResourceWatch/Search"  style="margin-left:5%; display:flex">
							<a href="javascript:void(0);" style="float:left" data-html="true" data-toggle="popover" data-trigger="focus" title="What is a RRID?" data-placement="left"                                                         data-content="RRIDs are persistent and unique identifiers for referencing a research resource. <a href='/ResourceWatch/No_Results_Found?q=rrid' target='_blank'>[Learn More]</a>">
									<img src="/images/question.png" style="width:18px; height:18px; float:left; margin-right:5px; margin-top:7px"></img>
							</a>
							<label for="searchBar" class="col-form-label" style="font-size:22px; float:left; margin-right:5px">RRID:</label>
							<div>
								<input id="searchBar" class="form-control" style="width: 190px; float:left"  name="q" placeholder="Ex: AB_2341236" value="" type="text"/>
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
					<div class="col-lg-6 col-lg-offset-3 col-md-6 col-md-offset-2" style="min-width:600px">
						<p style="text-align:center; font-size:25px; color:red; margin-right:50px">
							<!-- TODO Replace RRID with the one used for the search -->
							<?php
								if ($_GET['q'] == '') {
									echo 'Search requires an RRID';
								} else if ($_GET['q'] == 'rrid') {
									// Do Nothing
								} else {
									echo 'The Following RRID:' . htmlspecialchars(str_replace("RRID:", "", $_GET['q'])) . ' is an Invalid RRID';
								}
							?>
						</p>
					</div>
				</div>
			</div>
		</div>

		<!-- End of Search block-->

		<div class="container" style="width:50%; min-width:800px; text-align:justify; text-justify:inter-word; margin-bottom:50px; font-size:17px">
			<div>
				<h3 style="color:black"><u>What is a RRID?</u></h3>
				<p>
					RRIDs are persistent and unique identifiers for referencing a research resource. <br/>
					<ul>
						<li style="margin-top:-10px">
							For further information, please reference
							<a id="rridLinkOut" href="https://dknet.org/about/rrid" target="_blank">dkNET's documentation on RRIDs</a>
						</li>
					</ul>
				</p>
				<br/>
				<h3 style="color:black"><u>How to locate a RRID for your resource:</u></h3>
				<p>
					<p>
						<b>Find a RRID:</b> <a href="https://dknet.org/about/rrid#section-5" target="_blank">[Learn More]</a>
						<b style="padding-left: 20px">Obtain a RRID:</b> <a href="https://dknet.org/about/rrid#section-6" target="_blank">[Learn More]</a>
					</p>
				</p>

				<br/>
				<h3 style="color:black"><u>Use the following links to search for the correct RRID:</u></h3>
				<p>
					Depending on the type of entity, you may need to use the following appropriate resource to look up their associated RRID.
				</p>
				<p>
					<a href="https://dknet.org/data/source/nif-0000-07730-1/search" target="_blank">Search for RRID for antibodies</a>
					<a href="https://dknet.org/data/source/SCR_013869-1/search" target="_blank" style="padding-left: 20px; padding-right: 20px">Search for RRID for cell lines</a>
					<!-- <a href="https://scicrunch.org/resources" target="_blank">Search for RRID for tools</a> -->
				</p>

			</div>
		</div>

		<!-- End of Search block-->

	</body>

</html>
