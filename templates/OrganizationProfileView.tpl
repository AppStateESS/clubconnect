<!-- INCLUDE mod/sdr/templates/HeaderView.tpl -->
<div class="row">
  <div class="col-lg-4 col-lg-push-8">

    <!-- BEGIN BACKLINK -->
      <p class="backlink">{BACKLINK}</p>
    <!-- END BACKLINK -->

    <!-- BEGIN LOGO -->
      {LOGO}
    <!-- END LOGO -->

    <div id="fb-root"></div><script src="https://connect.facebook.net/en_US/all.js#appId=241152695910310&amp;xfbml=1"></script><fb:like href="https://clubconnect.appstate.edu/sdr/clubs/{ID}" send="true" layout="standard" show_faces="true" font="arial"></fb:like>

  </div>
  <div class="col-lg-8 col-lg-pull-4">

    <!-- BEGIN PURPOSE -->
      <p class="lead">{PURPOSE}</p>
    <!-- END PURPOSE -->

    <!-- BEGIN DESCRIPTION -->
      <div>{DESCRIPTION}</div>
    <!-- END DESCRIPTION -->
  </div>
</div>
<div class="row">

  <!-- BEGIN MEETINGS -->
    <div class="col-lg-4">
      <div class="panel">
        <div class="panel-heading">
          <h3 class="panel-title">Meetings</h3>
        </div>
        <div class="panel-body panel-icons">
          <p><i class="icon-time" title="Date and Time"></i> {DATE}</p>
          <p><i class="icon-map-marker" title="Location"></i> {LOCATION}</p>
        </div>
      </div>
    </div>
  <!-- END MEETINGS -->

  <!-- BEGIN CONTACT -->
    <div class="col-lg-4">
      <div class="panel">
        <div class="panel-heading">
          <h3 class="panel-title">Contact</h3>
        </div>
        <div class="panel-body panel-icons">
          <p><i class="icon-external-link" title="Website"></i> {WEB_ADDRESS}</p>
          <!-- BEGIN CONTACT_EMAIL -->
            <p><i class="icon-user"></i> <strong>{ROLE}:</strong> {NAME}</p>
          <!-- END CONTACT_EMAIL -->
        </div>
      </div>
    </div>
  <!-- END CONTACT -->

  <!-- BEGIN ACTIONS -->
    <div class="col-lg-4">
      <div class="panel panel-primary">
        <div class="panel-heading">
          <h3 class="panel-title">Actions</h3>
        </div>
          <ul class="nav nav-stacked nav-pills">
            {OPTIONS}
          </ul>
      </div>
    </div>
  <!-- END ACTIONS -->
</div>
