<script src="../js/resource-watch/resource-watch-submitForm.js"></script>
<link rel="stylesheet" href="../css/resource-watch/resource-watch.css">

<!DOCTYPE html>
<html lang="en">
  <title>Resource Watch | Confirmation Page</title>
  <header>
    <?php
      echo Connection::createBreadCrumbs('Confirmation Page', array('Resource Watch') ,array('/ResourceWatch'),'Confirmation Page');
    ?>
  </header>

  <body>
    <h1 style="text-align:center; margin-top:50px; font-size:50px; line-height:50px">Your Submission Has Been Processed</h1>

    <div class="container" style="margin-top:40px; margin-bottom:30px">
      <div class="row center">
        <a class="btn btn-success" href="/ResourceWatch/No_Results_Found?q=rrid">Review Submission</a>
      </div>
      <div class="row center" style="margin-bottom:30px">
        <a class="btn btn-success" href="/ResourceWatch/Search?q=<?php echo $_GET['rrid'];?>">Make Another Submission For <?php echo $_GET['rrid']; ?></a>
      </div>
      <div class="row">
        <div class="col-lg-6 col-lg-offset-3 col-md-6 col-md-offset-2 col-sm-12">
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

  </body>
</html>
