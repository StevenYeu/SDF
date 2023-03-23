<!DOCTYPE html>
<html>
	<title>Resource Watch | About</title>
    <header>
        <?php
            echo Connection::createBreadCrumbs('About Page', array('Resource Watch') ,array('/ResourceWatch'),'About Page');
		?>
    </header>

	<body>
		<div class="container">
			<div class="col-sm-offset-3">
				<div style="width:65%; margin-top:50px; margin-bottom:50px">
					<h1>About Resource Watch</h1>
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
			</div>
		</div>

	</body>

</html>

