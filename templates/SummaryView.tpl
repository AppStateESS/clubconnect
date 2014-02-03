<div class="col-lg-6 col-lg-push-6">
  <div class="panel panel-default">
    <div class="panel-heading">
      <h4 class="panel-title">Notifications</h4>
    </div>
<!-- BEGIN NO_NOTIFICATIONS -->
    <div class="panel-body">
      <p class="text-muted">{NO_NOTIFICATIONS}</p>
    </div>
<!-- END NO_NOTIFICATIONS -->
    <div class="list-group">
<!-- BEGIN NOTIFICATIONS -->
      <a href="{URL}" class="list-group-item">
        <h4 class="list-group-item-title"><i class="icon-chevron-right pull-right"></i> {TITLE}</h4>
        <p class="list-group-item-text">{TEXT}</p>
      </a>
<!-- END NOTIFICATIONS -->
    </div>
  </div>
</div>
<div class="col-lg-6 col-lg-pull-6">
  <div class="panel panel-default">
    <div class="panel-heading">
      <h4 class="panel-title">Memberships</h4>
    </div>
<!-- BEGIN NO_MEMBERSHIPS -->
    <div class="panel-body">
      <p>You are not yet a member of any student organizations for the 2013-14 academic year.</p>
      <ul class="nav nav-pills nav-stacked">
        <li><a href="{CLUBDIR_LINK}">Browse the Club Directory</a></li>
        <li><a href="{TRANSCRIPT_LINK}">Manage your Co-Curricular Transcript</a></li>
      </ul>
    </div>
<!-- END NO_MEMBERSHIPS -->
    <div class="list-group">
<!-- BEGIN MEMBERSHIPS -->
      <a href="{URL}" class="list-group-item">
        <i class="icon-group"></i>
        {NAME}
      </a>
<!-- END MEMBERSHIPS -->
    </div>
  </div>
<!-- BEGIN PENDING -->
  <div class="panel panel-default">
    <div class="panel-heading">
      <h4 class="panel-title">Pending Membership Requests</h4>
    </div>
    <p class="text-muted">You have requested membership in the following organizations.  These memberships will not show up on your co-curricular transcript until approved by the organization.</p>
    <ul>
<!-- BEGIN OUTSTANDING -->
      <li>{NAME}</li>
<!-- END OUTSTANDING -->
    </ul>
  </div>
<!-- END PENDING -->
</div>
